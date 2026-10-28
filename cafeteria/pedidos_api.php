```php
<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

/*
|--------------------------------------------------------------------------
| Respuesta JSON
|--------------------------------------------------------------------------
*/
function responder(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode(
        $datos,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

/*
|--------------------------------------------------------------------------
| Leer datos JSON
|--------------------------------------------------------------------------
*/
function leerJson(): array
{
    $contenido = file_get_contents('php://input');
    $datos = json_decode($contenido ?: '{}', true);

    if (!is_array($datos)) {
        responder(['error' => 'Los datos enviados no son válidos.'], 400);
    }

    return $datos;
}

/*
|--------------------------------------------------------------------------
| Validar token de seguridad
|--------------------------------------------------------------------------
*/
function validarToken(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $tokenSesion = $_SESSION['csrf_token'] ?? '';

    if (
        $tokenSesion === '' ||
        $token === '' ||
        !hash_equals($tokenSesion, $token)
    ) {
        responder(['error' => 'Token de seguridad inválido. Recarga la página.'], 403);
    }
}

/*
|--------------------------------------------------------------------------
| Validar identificador
|--------------------------------------------------------------------------
*/
function validarId($id): int
{
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        responder(['error' => 'El identificador no es válido.'], 400);
    }

    return $id;
}

/*
|--------------------------------------------------------------------------
| Obtener pedido y sus productos
|--------------------------------------------------------------------------
*/
function obtenerPedido(PDO $pdo, int $id): array
{
    $consulta = $pdo->prepare(
        'SELECT id, cliente, subtotal, total, estado, fecha
         FROM pedidos
         WHERE id = ?'
    );

    $consulta->execute([$id]);
    $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        responder(['error' => 'El pedido no existe.'], 404);
    }

    $consultaDetalle = $pdo->prepare(
        'SELECT
            dp.id,
            dp.producto_id,
            p.nombre AS producto_nombre,
            dp.cantidad,
            dp.precio,
            dp.subtotal
         FROM detalle_pedidos dp
         INNER JOIN productos p ON p.id = dp.producto_id
         WHERE dp.pedido_id = ?
         ORDER BY dp.id ASC'
    );

    $consultaDetalle->execute([$id]);

    $pedido['items'] = $consultaDetalle->fetchAll(PDO::FETCH_ASSOC);

    return $pedido;
}

/*
|--------------------------------------------------------------------------
| Actualizar subtotal y total de un pedido
|--------------------------------------------------------------------------
*/
function recalcularPedido(PDO $pdo, int $pedidoId): void
{
    $consulta = $pdo->prepare(
        'SELECT COALESCE(SUM(subtotal), 0)
         FROM detalle_pedidos
         WHERE pedido_id = ?'
    );

    $consulta->execute([$pedidoId]);

    $total = (float) $consulta->fetchColumn();

    $actualizar = $pdo->prepare(
        'UPDATE pedidos
         SET subtotal = ?, total = ?
         WHERE id = ?'
    );

    $actualizar->execute([
        round($total, 2),
        round($total, 2),
        $pedidoId
    ]);
}

/*
|--------------------------------------------------------------------------
| Ejecutar acciones
|--------------------------------------------------------------------------
*/
try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException(
            'No se encontró la conexión PDO en conexion.php.'
        );
    }

    $accion = $_GET['accion'] ?? '';
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    /*
    |----------------------------------------------------------------------
    | LISTAR PRODUCTOS
    |----------------------------------------------------------------------
    */
    if ($accion === 'productos' && $metodo === 'GET') {
        $consulta = $pdo->query(
            'SELECT id, nombre, precio
             FROM productos
             WHERE activo = 1
             ORDER BY nombre ASC'
        );

        responder([
            'ok' => true,
            'productos' => $consulta->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    /*
    |----------------------------------------------------------------------
    | LISTAR PEDIDOS
    |----------------------------------------------------------------------
    */
    if ($accion === 'listar' && $metodo === 'GET') {
        $consulta = $pdo->query(
            'SELECT id, cliente, subtotal, total, estado, fecha
             FROM pedidos
             ORDER BY id DESC'
        );

        responder([
            'ok' => true,
            'pedidos' => $consulta->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    /*
    |----------------------------------------------------------------------
    | TODAS LAS DEMÁS ACCIONES REQUIEREN TOKEN
    |----------------------------------------------------------------------
    */
    validarToken();

    /*
    |----------------------------------------------------------------------
    | CREAR PEDIDO
    |----------------------------------------------------------------------
    */
    if ($accion === 'crear' && $metodo === 'POST') {
        $datos = leerJson();

        $cliente = trim((string) ($datos['cliente'] ?? ''));
        $items = $datos['items'] ?? [];

        if ($cliente === '') {
            responder(['error' => 'Ingresa el nombre del cliente.'], 422);
        }

        if (!is_array($items) || count($items) === 0) {
            responder(['error' => 'Agrega al menos un producto al pedido.'], 422);
        }

        $pdo->beginTransaction();

        try {
            $insertarPedido = $pdo->prepare(
                'INSERT INTO pedidos
                    (cliente, subtotal, total, estado, fecha)
                 VALUES (?, 0, 0, ?, NOW())'
            );

            $insertarPedido->execute([$cliente, 'Pendiente']);

            $pedidoId = (int) $pdo->lastInsertId();

            $insertarDetalle = $pdo->prepare(
                'INSERT INTO detalle_pedidos
                    (pedido_id, producto_id, cantidad, precio, subtotal)
                 VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($items as $item) {
                $productoId = filter_var(
                    $item['producto_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                $cantidad = filter_var(
                    $item['cantidad'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (
                    $productoId === false ||
                    $productoId === null ||
                    $productoId < 1 ||
                    $cantidad === false ||
                    $cantidad === null ||
                    $cantidad < 1
                ) {
                    throw new DomainException(
                        'Hay productos con identificadores o cantidades inválidos.'
                    );
                }

                $consultaProducto = $pdo->prepare(
                    'SELECT id, precio
                     FROM productos
                     WHERE id = ? AND activo = 1'
                );

                $consultaProducto->execute([$productoId]);
                $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new DomainException(
                        'Uno de los productos no existe o está inactivo.'
                    );
                }

                $precio = (float) $producto['precio'];
                $subtotal = round($precio * $cantidad, 2);

                $insertarDetalle->execute([
                    $pedidoId,
                    $productoId,
                    $cantidad,
                    $precio,
                    $subtotal
                ]);
            }

            recalcularPedido($pdo, $pedidoId);

            $pdo->commit();

            responder([
                'ok' => true,
                'mensaje' => 'Pedido registrado correctamente.',
                'id' => $pedidoId
            ], 201);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /*
    |----------------------------------------------------------------------
    | MODIFICAR CANTIDAD DE UN PRODUCTO DEL PEDIDO
    |----------------------------------------------------------------------
    */
    if ($accion === 'modificar' && $metodo === 'POST') {
        $datos = leerJson();

        $detalleId = validarId($datos['detalle_id'] ?? null);

        $cantidad = filter_var(
            $datos['cantidad'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($cantidad === false || $cantidad === null || $cantidad < 1) {
            responder(['error' => 'La cantidad debe ser mayor que cero.'], 422);
        }

        $consulta = $pdo->prepare(
            'SELECT dp.id, dp.pedido_id, dp.precio, p.estado
             FROM detalle_pedidos dp
             INNER JOIN pedidos p ON p.id = dp.pedido_id
             WHERE dp.id = ?'
        );

        $consulta->execute([$detalleId]);
        $detalle = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$detalle) {
            responder(['error' => 'El producto del pedido no existe.'], 404);
        }

        if ($detalle['estado'] !== 'Pendiente') {
            responder([
                'error' => 'Solo puedes modificar pedidos pendientes.'
            ], 422);
        }

        $nuevoSubtotal = round(
            (float) $detalle['precio'] * $cantidad,
            2
        );

        $actualizar = $pdo->prepare(
            'UPDATE detalle_pedidos
             SET cantidad = ?, subtotal = ?
             WHERE id = ?'
        );

        $actualizar->execute([
            $cantidad,
            $nuevoSubtotal,
            $detalleId
        ]);

        recalcularPedido($pdo, (int) $detalle['pedido_id']);

        responder([
            'ok' => true,
            'mensaje' => 'Producto del pedido actualizado.'
        ]);
    }

    /*
    |----------------------------------------------------------------------
    | ELIMINAR PRODUCTO DE UN PEDIDO
    |----------------------------------------------------------------------
    */
    if ($accion === 'eliminar_detalle' && $metodo === 'POST') {
        $datos = leerJson();

        $detalleId = validarId($datos['detalle_id'] ?? null);

        $consulta = $pdo->prepare(
            'SELECT dp.pedido_id, p.estado
             FROM detalle_pedidos dp
             INNER JOIN pedidos p ON p.id = dp.pedido_id
             WHERE dp.id = ?'
        );

        $consulta->execute([$detalleId]);
        $detalle = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$detalle) {
            responder(['error' => 'El producto del pedido no existe.'], 404);
        }

        if ($detalle['estado'] !== 'Pendiente') {
            responder([
                'error' => 'Solo puedes modificar pedidos pendientes.'
            ], 422);
        }

        $eliminar = $pdo->prepare(
            'DELETE FROM detalle_pedidos WHERE id = ?'
        );

        $eliminar->execute([$detalleId]);

        recalcularPedido($pdo, (int) $detalle['pedido_id']);

        responder([
            'ok' => true,
            'mensaje' => 'Producto eliminado del pedido.'
        ]);
    }

    /*
    |----------------------------------------------------------------------
    | CAMBIAR ESTADO DEL PEDIDO
    |----------------------------------------------------------------------
    */
    if ($accion === 'estado' && $metodo === 'POST') {
        $datos = leerJson();

        $pedidoId = validarId($datos['id'] ?? null);
        $nuevoEstado = trim((string) ($datos['estado'] ?? ''));

        $estadosPermitidos = [
            'Pendiente',
            'En preparación',
            'Listo',
            'Entregado',
            'Cancelado'
        ];

        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            responder(['error' => 'El estado indicado no es válido.'], 422);
        }

        $consulta = $pdo->prepare(
            'SELECT estado FROM pedidos WHERE id = ?'
        );

        $consulta->execute([$pedidoId]);
        $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            responder(['error' => 'El pedido no existe.'], 404);
        }

        $estadoActual = $pedido['estado'];

        $transiciones = [
            'Pendiente' => ['En preparación', 'Cancelado'],
            'En preparación' => ['Listo', 'Cancelado'],
            'Listo' => ['Entregado'],
            'Entregado' => [],
            'Cancelado' => []
        ];

        if (
            $nuevoEstado !== $estadoActual &&
            !in_array(
                $nuevoEstado,
                $transiciones[$estadoActual] ?? [],
                true
            )
        ) {
            responder([
                'error' => 'No se permite cambiar de ' .
                    $estadoActual . ' a ' . $nuevoEstado . '.'
            ], 422);
        }

        $actualizar = $pdo->prepare(
            'UPDATE pedidos SET estado = ? WHERE id = ?'
        );

        $actualizar->execute([$nuevoEstado, $pedidoId]);

        responder([
            'ok' => true,
            'mensaje' => 'Estado del pedido actualizado.',
            'estado' => $nuevoEstado
        ]);
    }

    /*
    |----------------------------------------------------------------------
    | CONSULTAR DATOS PARA EL COMPROBANTE
    |----------------------------------------------------------------------
    */
    if ($accion === 'comprobante' && $metodo === 'GET') {
        $pedidoId = validarId($_GET['id'] ?? null);

        $pedido = obtenerPedido($pdo, $pedidoId);

        responder($pedido);
    }

    /*
    |----------------------------------------------------------------------
    | ACCIÓN NO RECONOCIDA
    |----------------------------------------------------------------------
    */
    responder(['error' => 'La acción solicitada no existe.'], 404);

} catch (DomainException $e) {
    responder(['error' => $e->getMessage()], 422);

} catch (PDOException $e) {
    error_log('Error de base de datos: ' . $e->getMessage());

    responder([
        'error' => 'Ocurrió un error en la base de datos. Revisa la configuración y las tablas.'
    ], 500);

} catch (Throwable $e) {
    error_log('Error en pedidos_api.php: ' . $e->getMessage());

    responder([
        'error' => 'Ocurrió un error interno al procesar la solicitud.'
    ], 500);
}
```
