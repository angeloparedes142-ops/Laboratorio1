"use strict";

// 1. Actualizar el año en el footer (RF-10)
const footerYear = document.querySelector("#year");
if (footerYear) {
    footerYear.textContent = new Date().getFullYear();
}

// 2. Transferir datos de la tarjeta al modal (RF-06)
const modalRegistro = document.getElementById("modalRegistro");
const inputCurso = document.getElementById("curso");

if (modalRegistro && inputCurso) {
    modalRegistro.addEventListener("show.bs.modal", (event) => {
        const botonActivador = event.relatedTarget;
        const nombreCurso = botonActivador.dataset.curso; // Lee data-curso
        inputCurso.value = nombreCurso;
    });

    // Limpiar el formulario al cerrar el modal (RF-09)
    modalRegistro.addEventListener("hidden.bs.modal", () => {
        const form = document.querySelector("#formRegistro");
        if (form) {
            form.reset();
            form.classList.remove("was-validated");
            const estado = document.querySelector("#estadoFormulario");
            if (estado) {
                estado.classList.add("d-none");
                estado.textContent = "";
            }
        }
    });
}

// 3. Algoritmo de validación del formulario (5.7 y RF-07/08)
const form = document.querySelector("#formRegistro");
const estado = document.querySelector("#estadoFormulario");

if (form && estado) {
  form.addEventListener("submit", (event) => {
    event.preventDefault(); // Evita recargar
    form.classList.add("was-validated");
    
    if (!form.checkValidity()) {
      form.querySelector(":invalid")?.focus(); // Enfocar el primer error
      return;
    }
    
    // Éxito simulado
    estado.textContent = "Registro de demostración completado.";
    estado.classList.remove("d-none");
  });
}
