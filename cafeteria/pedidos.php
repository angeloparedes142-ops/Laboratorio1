<?php
session_start();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION["csrf_token"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestión de pedidos - Cafetería</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 25px;
            color: #222;
        }

        .contenedor {
            max-width: 1100px;
            margin: auto;
        }

        h1, h2 {
            color: #493021;
        }

        .tarjeta {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #00000012;
        }

        input, select, button {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            cursor: pointer;
            background: #70452d;
            color: white;
            border: none;
        }

        button:hover {
            background: #4b2e1c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .mensaje {
            padding: 10px;
            margin-bottom: 15px;
        }

        .error {
            color: #a00000;
        }

        .correcto {
            color: #176b2c;
        }

        .acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #comprobante, #comprobante * {
                visibility: visible;
            }

            #comprobante {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-imprimir {
                display: none !important;
            }
        }
    </style>
</head>

<body>

<div class="contenedor">

    <h1>☕ Gestión de pedidos</h1>

    <div id="mensaje" class="mensaje" role="status"></div>

    <section class="tarjeta">
        <h2>Registrar pedido</h2>

        <label for="cliente">Nombre del cliente</label><br>
        <input
            id="cliente"
            type="text"
            maxlength="150"
            placeholder="Nombre del cliente"
            required
        >

        <br>

        <label for="producto">Producto</label><br>
        <select id="producto">
            <option value="">Cargando productos...</option>
        </select>

        <br>

        <label for="cantidad">Cantidad</label><br>
        <input
            id="cantidad"
            type="number"
            min="1"
            max="1000"
            value="1"
        >

        <br>

        <button type="button" onclick="agregarProducto()">
            Agregar producto
        </button>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th class="no-imprimir">Acción</th>
                </tr>
            </thead>

            <tbody id="carrito"></tbody>
        </table>

        <h3>Total: S/ <span id="total">0.00</span></h3>

        <button type="button" onclick="registrarPedido()">
            Registrar pedido
        </button>
    </section>

    <section class="tarjeta">
        <h2>Pedidos registrados</h2>

        <button type="button" onclick="cargarPedidos()">
            Actualizar pedidos
        </button>

        <table>
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody id="pedidos"></tbody>
        </table>
    </section>

    <section id="comprobante" class="tarjeta" hidden>
        <h2>Comprobante de pedido</h2>
        <div id="detalleComprobante"></div>

        <button
            type="button"
            class="no-imprimir"
            onclick="window.print()"
        >
            Imprimir comprobante
        </button>
    </section>

</div>

<script>
const CSRF = <?= json_encode($csrf) ?>;
const API = "pedidos_api.php";

let productos = [];
let carrito = [];

const dinero = valor =>
    Number(valor).toLocaleString("es-PE", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

function mostrarMensaje(texto, error = false) {
    const elemento = document.getElementById("mensaje");
    elemento.textContent = texto;
    elemento.className = "mensaje " +
        (error ? "error" : "correcto");
}

async function api(accion, opciones = {}) {
    const respuesta = await fetch(
        `${API}?accion=${encodeURIComponent(accion)}`,
        {
            ...opciones,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": CSRF,
                ...(opciones.headers || {})
            }
        }
    );

    const data = await respuesta.json();

    if (!respuesta.ok) {
        throw new Error(data.error || "Ocurrió un error.");
    }

    return data;
}

async function cargarProductos() {
    try {
        productos = await api("productos", {
            method: "GET"
        });

        const select = document.getElementById("producto");
        select.replaceChildren();

        for (const producto of productos) {
            const opcion = document.createElement("option");
            opcion.value = producto.id;
            opcion.textContent =
                `${producto.nombre} — S/ ${dinero(producto.precio)}`;

            select.appendChild(opcion);
        }
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

function agregarProducto() {
    const id = Number(document.getElementById("producto").value);
    const cantidad = Number(document.getElementById("cantidad").value);

    if (!Number.isInteger(cantidad) || cantidad < 1 || cantidad > 1000) {
        mostrarMensaje("Ingresa una cantidad válida.", true);
        return;
    }

    const producto = productos.find(p => Number(p.id) === id);

    if (!producto) {
        mostrarMensaje("Selecciona un producto válido.", true);
        return;
    }

    const existente = carrito.find(p => p.producto_id === id);

    if (existente) {
        if (existente.cantidad + cantidad > 1000) {
            mostrarMensaje("La cantidad máxima es 1000.", true);
            return;
        }

        existente.cantidad += cantidad;
    } else {
        carrito.push({
            producto_id: id,
            nombre: producto.nombre,
            precio: Number(producto.precio),
            cantidad
        });
    }

    dibujarCarrito();
    mostrarMensaje("Producto agregado.");
}

function dibujarCarrito() {
    const tbody = document.getElementById("carrito");
    tbody.replaceChildren();

    let totalCentimos = 0;

    carrito.forEach((item, indice) => {
        const precioCentimos = Math.round(item.precio * 100);
        const subtotalCentimos = precioCentimos * item.cantidad;

        totalCentimos += subtotalCentimos;

        const fila = document.createElement("tr");

        const valores = [
            item.nombre,
            "S/ " + dinero(precioCentimos / 100),
            String(item.cantidad),
            "S/ " + dinero(subtotalCentimos / 100)
        ];

        valores.forEach(valor => {
            const td = document.createElement("td");
            td.textContent = valor;
            fila.appendChild(td);
        });

        const accion = document.createElement("td");
        accion.className = "no-imprimir";

        const boton = document.createElement("button");
        boton.textContent = "Eliminar";
        boton.type = "button";
        boton.addEventListener("click", () => {
            carrito.splice(indice, 1);
            dibujarCarrito();
        });

        accion.appendChild(boton);
        fila.appendChild(accion);
        tbody.appendChild(fila);
    });

    document.getElementById("total").textContent =
        dinero(totalCentimos / 100);
}

async function registrarPedido() {
    const cliente = document.getElementById("cliente").value.trim();

    if (!cliente || carrito.length === 0) {
        mostrarMensaje(
            "Escribe el nombre del cliente y agrega productos.",
            true
        );
        return;
    }

    try {
        const data = await api("crear", {
            method: "POST",
            body: JSON.stringify({
                cliente,
                items: carrito.map(item => ({
                    producto_id: item.producto_id,
                    cantidad: item.cantidad
                }))
            })
        });

        mostrarMensaje(
            `Pedido #${data.pedido_id} registrado. Total: S/ ${dinero(data.total)}`
        );

        carrito = [];
        dibujarCarrito();

        document.getElementById("cliente").value = "";

        await cargarPedidos();
        await imprimirComprobante(data.pedido_id);

    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

async function cargarPedidos() {
    try {
        const pedidos = await api("listar", {
            method: "GET"
        });

        const tbody = document.getElementById("pedidos");
        tbody.replaceChildren();

        pedidos.forEach(pedido => {
            const fila = document.createElement("tr");

            [
                "#" + pedido.id,
                pedido.cliente,
                pedido.fecha,
                "S/ " + dinero(pedido.total),
                pedido.estado
            ].forEach(valor => {
                const td = document.createElement("td");
                td.textContent = valor;
                fila.appendChild(td);
            });

            const tdAcciones = document.createElement("td");
            tdAcciones.className = "acciones";

            const boton = document.createElement("button");
            boton.textContent = "Comprobante";
            boton.type = "button";
            boton.addEventListener("click", () => {
                imprimirComprobante(pedido.id);
            });

            tdAcciones.appendChild(boton);

            if (pedido.estado !== "Entregado" &&
                pedido.estado !== "Cancelado") {

                const select = document.createElement("select");

                const opciones = {
                    "Pendiente": ["En preparación", "Cancelado"],
                    "En preparación": ["Listo", "Cancelado"],
                    "Listo": ["Entregado"],
                    "Entregado": [],
                    "Cancelado": []
                };

                opciones[pedido.estado].forEach(estado => {
                    const opcion = document.createElement("option");
                    opcion.value = estado;
                    opcion.textContent = estado;
                    select.appendChild(opcion);
                });

                const cambiar = document.createElement("button");
                cambiar.textContent = "Cambiar estado";
                cambiar.type = "button";

                cambiar.addEventListener("click", async () => {
                    try {
                        await api("estado", {
                            method: "POST",
                            body: JSON.stringify({
                                pedido_id: Number(pedido.id),
                                estado: select.value
                            })
                        });

                        mostrarMensaje("Estado actualizado.");
                        await cargarPedidos();
                    } catch (error) {
                        mostrarMensaje(error.message, true);
                    }
                });

                tdAcciones.append(select, cambiar);
            }

            fila.appendChild(tdAcciones);
            tbody.appendChild(fila);
        });
    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

async function imprimirComprobante(id) {
    try {
        const pedido = await api(
            "comprobante&id=" + encodeURIComponent(id),
            { method: "GET" }
        );

        const contenedor = document.getElementById("comprobante");
        const detalle = document.getElementById("detalleComprobante");

        detalle.replaceChildren();

        const titulo = document.createElement("h3");
        titulo.textContent = "Cafetería — Pedido #" + pedido.id;
        detalle.appendChild(titulo);

        const cliente = document.createElement("p");
        cliente.textContent = "Cliente: " + pedido.cliente;
        detalle.appendChild(cliente);

        const fecha = document.createElement("p");
        fecha.textContent = "Fecha: " + pedido.fecha;
        detalle.appendChild(fecha);

        const tabla = document.createElement("table");
        const tbody = document.createElement("tbody");

        pedido.items.forEach(item => {
            const fila = document.createElement("tr");

            [
                item.producto_nombre,
                `${item.cantidad} x S/ ${dinero(item.precio)}`,
                "S/ " + dinero(item.subtotal)
            ].forEach(valor => {
                const td = document.createElement("td");
                td.textContent = valor;
                fila.appendChild(td);
            });

            tbody.appendChild(fila);
        });

        tabla.appendChild(tbody);
        detalle.appendChild(tabla);

        const total = document.createElement("h3");
        total.textContent = "TOTAL: S/ " + dinero(pedido.total);
        detalle.appendChild(total);

        const estado = document.createElement("p");
        estado.textContent = "Estado: " + pedido.estado;
        detalle.appendChild(estado);

        contenedor.hidden = false;
        contenedor.scrollIntoView({ behavior: "smooth" });

    } catch (error) {
        mostrarMensaje(error.message, true);
    }
}

async function iniciar() {
    await cargarProductos();
    await cargarPedidos();
}

iniciar();
</script>

</body>
</html>
