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
        const pedido = await api("comprobante", {
            method: "GET",
            query: { id: String(id) }
        });

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
