let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

function agregarCarrito(nombre, precio) {

    let productoExistente = carrito.find(
        producto => producto.nombre === nombre
    );

    if (productoExistente) {
        productoExistente.cantidad++;
    } else {
        carrito.push({
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });
    }

    guardarCarrito();

    alert("🛒 " + nombre + " fue agregado al carrito");
}


function guardarCarrito() {

    localStorage.setItem(
        "carrito",
        JSON.stringify(carrito)
    );

    actualizarContador();
}


function actualizarContador() {

    carrito = JSON.parse(
        localStorage.getItem("carrito")
    ) || [];

    let cantidad = carrito.reduce(
        (total, producto) => total + producto.cantidad,
        0
    );

    let contador = document.getElementById("contador-carrito");

    if (contador) {
        contador.textContent = cantidad;
    }
}


function mostrarCarrito() {

    let lista = document.getElementById("lista-carrito");

    if (!lista) return;

    carrito = JSON.parse(
        localStorage.getItem("carrito")
    ) || [];

    lista.innerHTML = "";

    let total = 0;


    if (carrito.length === 0) {

        lista.innerHTML = `
            <div class="carrito-vacio">
                <h2>🛒 Tu carrito está vacío</h2>
                <p>Agrega productos desde nuestra cafetería.</p>
            </div>
        `;

        document.getElementById("total-carrito").textContent =
            "S/ 0.00";

        return;
    }


    carrito.forEach((producto, index) => {

        let subtotal =
            producto.precio * producto.cantidad;

        total += subtotal;


        lista.innerHTML += `

            <div class="producto-carrito">

                <div>
                    <h3>${producto.nombre}</h3>

                    <p>
                        Precio: S/ ${producto.precio.toFixed(2)}
                    </p>
                </div>


                <div class="controles">

                    <button onclick="cambiarCantidad(${index}, -1)">
                        −
                    </button>

                    <span>
                        ${producto.cantidad}
                    </span>

                    <button onclick="cambiarCantidad(${index}, 1)">
                        +
                    </button>

                </div>


                <div>

                    <strong>
                        S/ ${subtotal.toFixed(2)}
                    </strong>

                    <button
                        class="eliminar"
                        onclick="eliminarProducto(${index})"
                    >
                        🗑 Eliminar
                    </button>

                </div>

            </div>

        `;
    });


    document.getElementById("total-carrito").textContent =
        "S/ " + total.toFixed(2);
}


function cambiarCantidad(index, cambio) {

    carrito[index].cantidad += cambio;

    if (carrito[index].cantidad <= 0) {
        carrito.splice(index, 1);
    }

    guardarCarrito();
    mostrarCarrito();
}


function eliminarProducto(index) {

    carrito.splice(index, 1);

    guardarCarrito();
    mostrarCarrito();
}


function vaciarCarrito() {

    carrito = [];

    guardarCarrito();
    mostrarCarrito();
}


function realizarPedido() {

    if (carrito.length === 0) {

        alert("🛒 Tu carrito está vacío");

        return;
    }

    alert("🎉 ¡Pedido realizado con éxito! Gracias por tu compra ☕");

    carrito = [];

    guardarCarrito();
    mostrarCarrito();
}


document.addEventListener("DOMContentLoaded", function () {

    actualizarContador();
    mostrarCarrito();

});
