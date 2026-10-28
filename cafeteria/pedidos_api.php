```php
<?php

session_start();
require_once "conexion.php";

header("Content-Type: application/json; charset=utf-8");

function responder($data, $status = 200)
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

function exigirMetodo($metodo)
{
    if ($_SERVER["REQUEST_METHOD"] !== $metodo) {
        header("Allow: $metodo");
        responder(["error" => "Método no permitido."], 405);
    }
}

function validarCsrf()
{
    $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

    if (
        empty($_SESSION["csrf_token"]) ||
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        responder(["error" => "Token de seguridad inválido."], 403);
    }
}

function entrada()
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        responder(["error" => "Datos JSON inválidos."], 400);
    }

    return $data;
}

$accion = $_GET["accion"] ?? "";

try {
    switch ($accion) {

        // A: Listar productos disponibles.
        case "productos":

            exigirMetodo("GET");

            $stmt = $pdo->query(
                "SELECT id, nombre, precio
                 FROM productos
                 WHERE activo = 1
                 ORDER BY nombre"
            );

            responder($stmt->fetchAll());

            break;

        // A y E: Listar pedidos.
        case "listar":

            exigirMetodo("GET");

            $stmt = $pdo->query(
                "SELECT
                    p.id,
                    p.cliente,
                    p.fecha,
                    p.estado,
                    p.total,
                    COUNT(d.id) AS lineas
                 FROM pedidos p
                 LEFT JOIN detalle_pedidos d
                    ON d.pedido_id = p.id
                 GROUP BY
                    p.id,
                    p.cliente,
                    p.fecha,
                    p.estado,
                    p.total
                 ORDER BY p.id DESC"
            );

            responder($stmt->fetchAll());

            break;

        // A, B, C y D: Registrar un pedido.
        case "crear":

            exigirMetodo("POST");
            validarCsrf();

            $data = entrada();

            $cliente = trim($data["cliente"] ?? "");
            $items = $data["items"] ?? [];

            if (
                $cliente === "" ||
                mb_strlen($cliente) > 150
            ) {
                responder(
                    ["error" => "Nombre de cliente inválido."],
                    422
                );
            }

            if (
                !is_array($items) ||
                count($items) < 1 ||
                count($items) > 100
            ) {
                responder(
                    ["error" => "El pedido debe tener productos."],
                    422
                );
            }

            $cantidades = [];

            foreach ($items as $item) {

                $id = filter_var(
                    $item["producto_id"] ?? null,
                    FILTER_VALIDATE_INT
                );

                $cantidad = filter_var(
                    $item["cantidad"] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (
                    $id === false || $id === null || $id < 1 ||
                    $cantidad === false || $cantidad === null ||
                    $cantidad < 1 || $cantidad > 1000
                ) {
                    responder(
                        ["error" => "Producto o cantidad inválidos."],
                        422
                    );
                }

                $cantidades[$id] =
                    ($cantidades[$id] ?? 0) + $cantidad;

                if ($cantidades[$id] > 1000) {
                    responder(
                        ["error" => "Cantidad máxima excedida."],
                        422
                    );
                }
            }

            $pdo->beginTransaction();

            $stmtProducto = $pdo->prepare(
                "SELECT id, nombre, precio
                 FROM productos
                 WHERE id = ? AND activo = 1"
            );

            $productosPedido = [];
            $totalCentimos = 0;

            foreach ($cantidades as $id => $cantidad) {

                $stmtProducto->execute([$id]);
                $producto = $stmtProducto->fetch();

                if (!$producto) {
                    throw new DomainException(
                        "Uno de los productos no existe o está inactivo."
                    );
                }

                $precioCentimos = (int) round(
                    (float) $producto["precio"] * 100
                );

                $subtotalCentimos =
                    $precioCentimos * $cantidad;

                $totalCentimos += $subtotalCentimos;

                $productosPedido[] = [
                    "id" => (int) $producto["id"],
                    "nombre" => $producto["nombre"],
                    "precio" => $precioCentimos,
                    "cantidad" => $cantidad,
                    "subtotal" => $subtotalCentimos
                ];
            }

            if ($totalCentimos > 999999999) {
                throw new DomainException(
                    "El total del pedido es demasiado alto."
                );
            }

            $stmt = $pdo->prepare(
                "INSERT INTO pedidos (cliente, total)
                 VALUES (?, ?)"
            );

            $stmt->execute([
                $cliente,
                number_format(
                    $totalCentimos / 100,
                    2,
                    ".",
                    ""
                )
            ]);

            $pedidoId = (int) $pdo->lastInsertId();

            $stmtDetalle = $pdo->prepare(
                "INSERT INTO detalle_pedidos
                    (
                        pedido_id,
                        producto_id,
                        producto_nombre,
                        cantidad,
                        precio,
                        subtotal
                    )
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($productosPedido as $item) {

                $stmtDetalle->execute([
                    $pedidoId,
                    $item["id"],
                    $item["nombre"],
                    $item["cantidad"],
                    number_format(
                        $item["precio"] / 100,
                        2,
                        ".",
                        ""
                    ),
                    number_format(
                        $item["subtotal"] / 100,
                        2,
                        ".",
                        ""
                    )
                ]);
            }

            $pdo->commit();

            responder([
                "mensaje" => "Pedido registrado correctamente.",
                "pedido_id" => $pedidoId,
                "total" => number_format(
                    $totalCentimos / 100,
                    2,
                    ".",
                    ""
                )
            ], 201);

            break;

        // B: Modificar la cantidad de un producto.
        case "modificar":

            exigirMetodo("POST");
            validarCsrf();

            $data = entrada();

            $pedidoId = filter_var(
                $data["pedido_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            $detalleId = filter_var(
                $data["detalle_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            $cantidad = filter_var(
                $data["cantidad"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (
                !$pedidoId ||
                !$detalleId ||
                $cantidad === false ||
                $cantidad === null ||
                $cantidad < 1 ||
                $cantidad > 1000
            ) {
                responder(
                    ["error" => "Datos inválidos."],
                    422
                );
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT estado
                 FROM pedidos
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->execute([$pedidoId]);
            $pedido = $stmt->fetch();

            if (
                !$pedido ||
                $pedido["estado"] !== "Pendiente"
            ) {
                throw new DomainException(
                    "Solo se pueden modificar pedidos pendientes."
                );
            }

            $stmt = $pdo->prepare(
                "SELECT precio
                 FROM detalle_pedidos
                 WHERE id = ? AND pedido_id = ?
                 FOR UPDATE"
            );

            $stmt->execute([$detalleId, $pedidoId]);
            $detalle = $stmt->fetch();

            if (!$detalle) {
                throw new DomainException(
                    "Detalle de pedido no encontrado."
                );
            }

            $precioCentimos = (int) round(
                (float) $detalle["precio"] * 100
            );

            $subtotalCentimos =
                $precioCentimos * $cantidad;

            if ($subtotalCentimos > 999999999) {
                throw new DomainException(
                    "Subtotal demasiado alto."
                );
            }

            $stmt = $pdo->prepare(
                "UPDATE detalle_pedidos
                 SET cantidad = ?, subtotal = ?
                 WHERE id = ? AND pedido_id = ?"
            );

            $stmt->execute([
                $cantidad,
                number_format(
                    $subtotalCentimos / 100,
                    2,
                    ".",
                    ""
                ),
                $detalleId,
                $pedidoId
            ]);

            // Recalcular el total del pedido.
            $stmt = $pdo->prepare(
                "UPDATE pedidos
                 SET total = (
                    SELECT COALESCE(SUM(subtotal), 0)
                    FROM detalle_pedidos
                    WHERE pedido_id = ?
                 )
                 WHERE id = ?"
            );

            $stmt->execute([$pedidoId, $pedidoId]);

            $pdo->commit();

            responder([
                "mensaje" => "Cantidad actualizada correctamente."
            ]);

            break;

        // B: Eliminar un producto de un pedido.
        case "eliminar_detalle":

            exigirMetodo("POST");
            validarCsrf();

            $data = entrada();

            $pedidoId = filter_var(
                $data["pedido_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            $detalleId = filter_var(
                $data["detalle_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$pedidoId || !$detalleId) {
                responder(
                    ["error" => "Identificadores inválidos."],
                    422
                );
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT estado
                 FROM pedidos
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->execute([$pedidoId]);
            $pedido = $stmt->fetch();

            if (
                !$pedido ||
                $pedido["estado"] !== "Pendiente"
            ) {
                throw new DomainException(
                    "Solo se pueden editar pedidos pendientes."
                );
            }

            $stmt = $pdo->prepare(
                "DELETE FROM detalle_pedidos
                 WHERE id = ? AND pedido_id = ?"
            );

            $stmt->execute([$detalleId, $pedidoId]);

            if ($stmt->rowCount() === 0) {
                throw new DomainException(
                    "Detalle no encontrado."
                );
            }

            $stmt = $pdo->prepare(
                "UPDATE pedidos
                 SET total = (
                    SELECT COALESCE(SUM(subtotal), 0)
                    FROM detalle_pedidos
                    WHERE pedido_id = ?
                 )
                 WHERE id = ?"
            );

            $stmt->execute([$pedidoId, $pedidoId]);

            $pdo->commit();

            responder([
                "mensaje" => "Producto eliminado del pedido."
            ]);

            break;

        // E: Cambiar el estado de un pedido.
        case "estado":

            exigirMetodo("POST");
            validarCsrf();

            $data = entrada();

            $id = filter_var(
                $data["pedido_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            $estado = $data["estado"] ?? "";

            $permitidos = [
                "Pendiente" => [
                    "En preparación",
                    "Cancelado"
                ],
                "En preparación" => [
                    "Listo",
                    "Cancelado"
                ],
                "Listo" => [
                    "Entregado"
                ],
                "Entregado" => [],
                "Cancelado" => []
            ];

            if (
                !$id ||
                !is_string($estado) ||
                !in_array(
                    $estado,
                    [
                        "Pendiente",
                        "En preparación",
                        "Listo",
                        "Entregado",
                        "Cancelado"
                    ],
                    true
                )
            ) {
                responder(
                    ["error" => "Pedido o estado inválido."],
                    422
                );
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT estado
                 FROM pedidos
                 WHERE id = ?
                 FOR UPDATE"
            );

            $stmt->execute([$id]);
            $pedido = $stmt->fetch();

            if (!$pedido) {
                throw new DomainException(
                    "Pedido no encontrado."
                );
            }

            if (
                !in_array(
                    $estado,
                    $permitidos[$pedido["estado"]],
                    true
                )
            ) {
                throw new DomainException(
                    "No se permite ese cambio de estado."
                );
            }

            $stmt = $pdo->prepare(
                "UPDATE pedidos
                 SET estado = ?
                 WHERE id = ?"
            );

            $stmt->execute([$estado, $id]);

            $pdo->commit();

            responder([
                "mensaje" => "Estado actualizado correctamente."
            ]);

            break;

        // F: Consultar los datos de un comprobante.
        case "comprobante":

            exigirMetodo("GET");

            $id = filter_var(
                $_GET["id"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id) {
                responder(
                    ["error" => "Número de pedido inválido."],
                    422
                );
            }

            $stmt = $pdo->prepare(
                "SELECT id, cliente, fecha, estado, total
                 FROM pedidos
                 WHERE id = ?"
            );

            $stmt->execute([$id]);
            $pedido = $stmt->fetch();

            if (!$pedido) {
                responder(
                    ["error" => "Pedido no encontrado."],
                    404
                );
            }

            $stmt = $pdo->prepare(
                "SELECT
                    producto_nombre,
                    cantidad,
                    precio,
                    subtotal
                 FROM detalle_pedidos
                 WHERE pedido_id = ?
                 ORDER BY id"
            );

            $stmt->execute([$id]);

            $pedido["items"] = $stmt->fetchAll();

            responder($pedido);

            break;

        default:

            responder([
                "error" => "Acción desconocida."
            ], 404);
    }

} catch (DomainException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    responder([
        "error" => $e->getMessage()
    ], 422);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());

    responder([
        "error" => "No se pudo completar la operación."
    ], 500);
}
```
