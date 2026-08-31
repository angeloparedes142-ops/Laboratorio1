// ==========================================
// CARRITO DE COMPRAS - COFFEE
// ==========================================

const CLAVE_CARRITO = "carrito_cafeteria";


// ==========================================
// OBTENER CARRITO
// ==========================================

function obtenerCarrito() {

    const datos =
        localStorage.getItem(CLAVE_CARRITO);

    if (datos) {
        return JSON.parse(datos);
    }

    return [];
}


// ==========================================
// GUARDAR CARRITO
// ==========================================

function guardarCarrito(carrito) {

    localStorage.setItem(
        CLAVE_CARRITO,
        JSON.stringify(carrito)
    );

    actualizarContador();

}


// ==========================================
// AGREGAR PRODUCTO
// ==========================================

function agregarProducto(
    nombre,
    precio,
    imagen
) {

    let carrito =
        obtenerCarrito();


    // Buscar producto existente

    const productoExistente =
        carrito.find(
            producto =>
                producto.nombre === nombre
        );


    if (productoExistente) {

        productoExistente.cantidad++;

    } else {

        carrito.push({

            id:
                Date.now() +
                Math.floor(
                    Math.random() * 1000
                ),

            nombre:
                nombre,

            precio:
                Number(precio),

            imagen:
                imagen,

            cantidad:
                1

        });

    }


    guardarCarrito(carrito);


    // Mensaje

    alert(
        "☕ " +
        nombre +
        " fue agregado al carrito."
    );
}


// ==========================================
// ACTUALIZAR CONTADOR
// ==========================================

function actualizarContador() {

    const elementos =
        document.querySelectorAll(
            "#contador-carrito"
        );


    const carrito =
        obtenerCarrito();


    const cantidad =
        carrito.reduce(

            function(total, producto) {

                return total +
                    producto.cantidad;
            },
            0
        );


    elementos.forEach(
        function(elemento) {

            elemento.textContent =
                cantidad;
        }
    );
}


// ==========================================
// MOSTRAR CARRITO
// ==========================================

function mostrarCarrito() {

    const lista =
        document.getElementById(
            "lista-carrito"
        );


    const totalElemento =
        document.getElementById(
            "total-carrito"
        );


    // Si no estamos en carrito.html

    if (!lista) {
        return;
    }


    const carrito =
        obtenerCarrito();


    // ======================================
    // CARRITO VACÍO
    // ======================================

    if (carrito.length === 0) {

        lista.innerHTML = `

            <div class="carrito-vacio">

                <h2>
                    🛒 Tu carrito está vacío
                </h2>

                <p>
                    Agrega productos desde
                    nuestro menú.
                </p>

                <a
                    href="index.html"
                    class="btn-carrito">

                    Ver productos

                </a>

            </div>

        `;


        if (totalElemento) {

            totalElemento.textContent =
                "S/ 0.00";

        }

        return;
    }


    // Limpiar

    lista.innerHTML = "";


    let total = 0;


    // ======================================
    // PRODUCTOS
    // ======================================

    carrito.forEach(
        function(producto) {


            const subtotal =
                producto.precio *
                producto.cantidad;


            total += subtotal;


            const item =
                document.createElement(
                    "div"
                );


            item.className =
                "producto-carrito";


            item.innerHTML = `

                <div class="producto-datos">

                    <img
                        src="${producto.imagen}"
                        alt="${producto.nombre}"
                    >

                    <div>

                        <h3>
                            ${producto.nombre}
                        </h3>

                        <p>
                            S/
                            ${producto.precio.toFixed(2)}
                        </p>

                    </div>

                </div>


                <div class="cantidad">

                    <button
                        type="button"

                        onclick="cambiarCantidad(
                            ${producto.id},
                            ${producto.cantidad - 1}
                        )">

                        −

                    </button>


                    <span>
                        ${producto.cantidad}
                    </span>


                    <button
                        type="button"

                        onclick="cambiarCantidad(
                            ${producto.id},
                            ${producto.cantidad + 1}
                        )">
                        +
                    </button>

                </div>


                <div class="subtotal">

                    <strong>

                        S/
                        ${subtotal.toFixed(2)}

                    </strong>


                    <button
                        type="button"
                        class="eliminar"

                        onclick="eliminarProducto(
                            ${producto.id}
                        )">

                        🗑️

                    </button>

                </div>

            `;


            lista.appendChild(item);

        }
    );


    // ======================================
    // TOTAL
    // ======================================

    if (totalElemento) {

        totalElemento.textContent =
            "S/ " +
            total.toFixed(2);

    }

}


// ==========================================
// CAMBIAR CANTIDAD
// ==========================================

function cambiarCantidad(
    id,
    cantidad
) {

    let carrito =
        obtenerCarrito();


    const producto =
        carrito.find(
            producto =>
                producto.id === id
        );


    if (!producto) {
        return;
    }


    // Eliminar si llega a cero

    if (cantidad <= 0) {

        carrito =
            carrito.filter(
                producto =>
                    producto.id !== id
            );

    } else {

        producto.cantidad =
            cantidad;

    }


    guardarCarrito(carrito);

    mostrarCarrito();

}


// ==========================================
// ELIMINAR PRODUCTO
// ==========================================

function eliminarProducto(id) {

    let carrito =
        obtenerCarrito();


    carrito =
        carrito.filter(
            producto =>
                producto.id !== id
        );


    guardarCarrito(carrito);

    mostrarCarrito();

}


// ==========================================
// VACIAR CARRITO
// ==========================================

function vaciarCarrito() {

    const carrito =
        obtenerCarrito();


    if (carrito.length === 0) {

        alert(
            "El carrito ya está vacío."
        );

        return;
    }


    const confirmar =
        confirm(
            "¿Deseas eliminar todos los productos?"
        );


    if (!confirmar) {
        return;
    }


    localStorage.removeItem(
        CLAVE_CARRITO
    );


    actualizarContador();

    mostrarCarrito();

}


// ==========================================
// CALCULAR TOTAL
// ==========================================

function calcularTotal() {

    const carrito =
        obtenerCarrito();


    return carrito.reduce(

        function(total, producto) {

            return total +
                (
                    producto.precio *
                    producto.cantidad
                );

        },

        0
    );

}


// ==========================================
// CONFIRMAR PEDIDO
// ==========================================

function confirmarPedido() {

    const carrito =
        obtenerCarrito();


    // Carrito vacío

    if (carrito.length === 0) {

        alert(
            "🛒 Tu carrito está vacío."
        );

        return;
    }


    // Campos

    const nombreElemento =
        document.getElementById(
            "nombre-cliente"
        );


    const telefonoElemento =
        document.getElementById(
            "telefono-cliente"
        );


    const nombre =
        nombreElemento
            ? nombreElemento.value.trim()
            : "";


    const telefono =
        telefonoElemento
            ? telefonoElemento.value.trim()
            : "";


    // Validar nombre

    if (nombre === "") {

        alert(
            "Ingresa tu nombre."
        );

        nombreElemento.focus();

        return;
    }


    // Validar teléfono

    if (telefono === "") {

        alert(
            "Ingresa tu teléfono."
        );

        telefonoElemento.focus();

        return;
    }


    // Total

    const total =
        calcularTotal();


    // Número de pedido

    const numeroPedido =
        Math.floor(
            10000 +
            Math.random() * 90000
        );


    // Confirmación

    alert(

        "✅ PEDIDO CONFIRMADO\n\n" +

        "Pedido: #" +
        numeroPedido +

        "\nCliente: " +
        nombre +

        "\nTeléfono: " +
        telefono +

        "\n\nTotal: S/ " +
        total.toFixed(2) +

        "\n\n" +

        "¡Gracias por comprar en Coffee! ☕"

    );


    // Limpiar carrito

    localStorage.removeItem(
        CLAVE_CARRITO
    );


    actualizarContador();


    // Volver al inicio

    window.location.href =
        "index.html";

}


// ==========================================
// INICIAR
// ==========================================

document.addEventListener(
    "DOMContentLoaded",
    function() {

        actualizarContador();

        mostrarCarrito();

    }
);

