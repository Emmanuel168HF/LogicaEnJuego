'use strict';

// Valida que cada pregunta obligatoria del cuestionario tenga una opción seleccionada antes de enviarlo.
document.querySelectorAll('[data-questionnaire]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        // Si falta una respuesta, evita el envío y lleva el foco a la primera pregunta pendiente.
        const questions = Array.from(form.querySelectorAll('[data-required-question]'));
        const unanswered = questions.find((question) => !question.querySelector('input:checked'));

        if (unanswered) {
            event.preventDefault();
            const firstInput = unanswered.querySelector('input');
            if (firstInput) {
                firstInput.focus();
            }
        }
    });
});
