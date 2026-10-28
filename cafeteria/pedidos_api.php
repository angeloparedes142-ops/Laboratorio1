<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

function responder($datos, int $codigo = 200): void {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function leerJson(): array {
    $raw = file_get_contents('php://input');
    $datos = json_decode($raw ?: '{}', true);
    if (!is_array($datos)) responder(['error' => 'Los datos enviados no son válidos.'], 400);
    return $datos;
}
function validarToken(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sesion = $_SESSION['csrf_token'] ?? '';
    if ($token === '' || $sesion === '' || !hash_equals($sesion, $token)) {
        responder(['error' => 'Token de seguridad inválido. Recarga la página.'], 403);
    }
}
function validarId($valor): int {
    $id = filter_var($valor, FILTER_VALIDATE_INT);
    if ($id === false || $id === null || $id < 1) responder(['error' => 'El identificador no es válido.'], 400);
    return (int)$id;
}
function obtenerPedido(PDO $pdo, int $id): array {
    $q = $pdo->prepare('SELECT id, cliente, fecha, estado, total FROM pedidos WHERE id = ?');
    $q->execute([$id]);
    $pedido = $q->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) responder(['error' => 'El pedido no existe.'], 404);
    $q = $pdo->prepare('SELECT id, producto_id, producto_nombre, cantidad, precio, subtotal FROM detalle_pedidos WHERE pedido_id = ? ORDER BY id');
    $q->execute([$id]);
    $pedido['items'] = $q->fetchAll(PDO::FETCH_ASSOC);
    return $pedido;
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) throw new RuntimeException('La conexión PDO no está configurada en conexion.php.');
    $accion = (string)($_GET['accion'] ?? '');
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'GET' && $accion === 'productos') {
        $q = $pdo->query('SELECT id, nombre, precio FROM productos WHERE activo = 1 ORDER BY nombre');
        responder($q->fetchAll(PDO::FETCH_ASSOC));
    }
    if ($metodo === 'GET' && $accion === 'listar') {
        $q = $pdo->query('SELECT id, cliente, fecha, estado, total FROM pedidos ORDER BY id DESC');
        responder($q->fetchAll(PDO::FETCH_ASSOC));
    }
    if ($metodo === 'GET' && $accion === 'comprobante') {
        responder(obtenerPedido($pdo, validarId($_GET['id'] ?? null)));
    }
    if ($metodo !== 'POST') responder(['error' => 'Método no permitido.'], 405);

    validarToken();
    $datos = leerJson();

    if ($accion === 'crear') {
        $cliente = trim((string)($datos['cliente'] ?? ''));
        $items = $datos['items'] ?? [];
        if ($cliente === '') responder(['error' => 'Escribe el nombre del cliente.'], 422);
        if (!is_array($items) || count($items) === 0) responder(['error' => 'Agrega al menos un producto al pedido.'], 422);
        if (count($items) > 100) responder(['error' => 'El pedido supera el máximo de 100 líneas de productos.'], 422);

        $pdo->beginTransaction();
        try {
            $q = $pdo->prepare("INSERT INTO pedidos (cliente, estado, total) VALUES (?, 'Pendiente', 0)");
            $q->execute([$cliente]);
            $pedidoId = (int)$pdo->lastInsertId();
            $productoQ = $pdo->prepare('SELECT id, nombre, precio FROM productos WHERE id = ? AND activo = 1');
            $detalleQ = $pdo->prepare('INSERT INTO detalle_pedidos (pedido_id, producto_id, producto_nombre, cantidad, precio, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
            $totalCentimos = 0;
            foreach ($items as $item) {
                if (!is_array($item)) throw new DomainException('Uno de los productos enviados no es válido.');
                $productoId = filter_var($item['producto_id'] ?? null, FILTER_VALIDATE_INT);
                $cantidad = filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT);
                if ($productoId === false || $productoId === null || $productoId < 1 || $cantidad === false || $cantidad === null || $cantidad < 1 || $cantidad > 1000) {
                    throw new DomainException('Revisa los identificadores y cantidades de los productos.');
                }
                $productoQ->execute([(int)$productoId]);
                $producto = $productoQ->fetch(PDO::FETCH_ASSOC);
                if (!$producto) throw new DomainException('Uno de los productos no existe o está inactivo.');
                $precioCentimos = (int)round((float)$producto['precio'] * 100);
                $subtotalCentimos = $precioCentimos * (int)$cantidad;
                $totalCentimos += $subtotalCentimos;
                $detalleQ->execute([$pedidoId, (int)$producto['id'], $producto['nombre'], (int)$cantidad,
                    number_format($precioCentimos / 100, 2, '.', ''), number_format($subtotalCentimos / 100, 2, '.', '')]);
            }
            $total = number_format($totalCentimos / 100, 2, '.', '');
            $q = $pdo->prepare('UPDATE pedidos SET total = ? WHERE id = ?');
            $q->execute([$total, $pedidoId]);
            $pdo->commit();
            responder(['ok' => true, 'mensaje' => 'Pedido registrado correctamente.', 'id' => $pedidoId, 'pedido_id' => $pedidoId, 'total' => (float)$total], 201);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    if ($accion === 'estado') {
        $pedidoId = validarId($datos['pedido_id'] ?? $datos['id'] ?? null);
        $nuevoEstado = trim((string)($datos['estado'] ?? ''));
        $transiciones = [
            'Pendiente' => ['En preparación', 'Cancelado'],
            'En preparación' => ['Listo', 'Cancelado'],
            'Listo' => ['Entregado'],
            'Entregado' => [], 'Cancelado' => []
        ];
        $q = $pdo->prepare('SELECT estado FROM pedidos WHERE id = ?');
        $q->execute([$pedidoId]);
        $pedido = $q->fetch(PDO::FETCH_ASSOC);
        if (!$pedido) responder(['error' => 'El pedido no existe.'], 404);
        $actual = $pedido['estado'];
        if (!in_array($nuevoEstado, $transiciones[$actual] ?? [], true) && $nuevoEstado !== $actual) {
            responder(['error' => "No se permite cambiar de {$actual} a {$nuevoEstado}."], 422);
        }
        $q = $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
        $q->execute([$nuevoEstado, $pedidoId]);
        responder(['ok' => true, 'mensaje' => 'Estado actualizado.', 'estado' => $nuevoEstado]);
    }
    responder(['error' => 'La acción solicitada no existe.'], 404);
} catch (DomainException $e) {
    responder(['error' => $e->getMessage()], 422);
