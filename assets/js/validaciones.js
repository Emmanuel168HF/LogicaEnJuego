'use strict';

// Refuerza la validación nativa del navegador en formularios antes de permitir su envío.
document.addEventListener('submit', (event) => {
    const form = event.target;
    // Cancela el envío cuando algún campo requerido o con restricciones no supera la validación.
    if (form instanceof HTMLFormElement && !form.reportValidity()) {
        event.preventDefault();
    }
});
