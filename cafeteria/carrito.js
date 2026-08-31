// Obtener los productos guardados
let carrito = JSON.parse(localStorage.getItem("carrito")) || [];


// AGREGAR PRODUCTOS AL CARRITO
function agregarCarrito(nombre, precio) {

    // Buscar si el producto ya existe
    let productoExistente = carrito.find(
        producto => producto.nombre === nombre
    );
    if (productoExistente) {
        productoExistente.cantidad++;
    } else {
        carrito.push({
            nombre: nombre,
            precio: Number(precio),
            cantidad: 1
        });
    }
    guardarCarrito();

    alert("🛒 " + nombre + " fue agregado al carrito");
}


// GUARDAR EL CARRITO
function guardarCarrito() {
    localStorage.setItem(
        "carrito",
        JSON.stringify(carrito)
    );
    actualizarContador();
}


// ACTUALIZAR EL NÚMERO DEL CARRITO
function actualizarContador() {

    carrito = JSON.parse(
        localStorage.getItem("carrito")
    ) || [];
    let cantidadTotal = carrito.reduce(
        (total, producto) => total + producto.cantidad,
        0
    );
    let contador = document.getElementById(
        "contador-carrito"
    );
    if (contador) {
        contador.textContent = cantidadTotal;
    }
}


// MOSTRAR LOS PRODUCTOS EN pedidos.html
function mostrarCarrito() {
    let lista = document.getElementById(
        "lista-carrito"
    );
    if (!lista) return;
    carrito = JSON.parse(
        localStorage.getItem("carrito")
    ) || [];
    lista.innerHTML = "";
    let total = 0;


    // SI EL CARRITO ESTÁ VACÍO
    if (carrito.length === 0) {
        lista.innerHTML = `
            <div class="carrito-vacio">
                <h2>🛒 Tu carrito está vacío</h2>
                <p>Agrega productos desde nuestra cafetería.</p>
            </div>
        `;

        document.getElementById(
            "total-carrito"
        ).textContent = "S/ 0.00";

        return;

    }


    // MOSTRAR CADA PRODUCTO
    carrito.forEach((producto, index) => {
        let subtotal =
            producto.precio * producto.cantidad;
        total += subtotal;
        lista.innerHTML += `
            <div class="producto-carrito">
               <div class="info-producto">
                   <h3>${producto.nombre}</h3>
                    <p>
                        Precio: S/ ${producto.precio.toFixed(2)}
                    </p>
                </div>
                <div class="controles-cantidad">

                    <button
                        onclick="cambiarCantidad(${index}, -1)"
                    >
                        −
                    </button>
                    <span>
                        ${producto.cantidad}
                    </span>


                    <button
                        onclick="cambiarCantidad(${index}, 1)"
                    >
                        +
                    </button>

                </div>
                <div class="subtotal-producto">
                    <strong>
                        S/ ${subtotal.toFixed(2)}
                    </strong>
                    <button
                        class="btn-eliminar"
                        onclick="eliminarProducto(${index})"
                    >
                        🗑 Eliminar
                    </button>
                </div>
            </div>
        `;
    });


    document.getElementById(
        "total-carrito"
    ).textContent = "S/ " + total.toFixed(2);

}


// CAMBIAR LA CANTIDAD
function cambiarCantidad(index, cambio) {

    carrito[index].cantidad += cambio;


    // Si llega a 0, eliminar producto
    if (carrito[index].cantidad <= 0) {

        carrito.splice(index, 1);

    }


    guardarCarrito();

    mostrarCarrito();

}


// ELIMINAR UN PRODUCTO
function eliminarProducto(index) {

    carrito.splice(index, 1);

    guardarCarrito();

    mostrarCarrito();

}


// VACIAR TODO EL CARRITO
function vaciarCarrito() {

    if (carrito.length === 0) {

        alert("El carrito ya está vacío.");
        return;

    }
    let confirmar = confirm(
        "¿Deseas vaciar todo el carrito?"
    );
    if (confirmar) {
        carrito = [];
        guardarCarrito();
        mostrarCarrito();
    }
}


// REALIZAR EL PEDIDO
function realizarPedido() {
    if (carrito.length === 0) {
        alert("🛒 Tu carrito está vacío.");
        return;

    }


    alert(
        "🎉 ¡Pedido realizado con éxito!\n\n" +
        "Gracias por comprar en nuestra cafetería ☕"
    );
    carrito = [];
    guardarCarrito();
    mostrarCarrito();

}


// INICIAR EL CARRITO
document.addEventListener(
    "DOMContentLoaded",
    function () {
        actualizarContador();
  mostrarCarrito();

    }
);
