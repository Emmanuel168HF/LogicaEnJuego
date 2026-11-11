'use strict';

// Mantiene sincronizados los ejercicios interactivos y emite un evento común cuando cambia una respuesta.
document.querySelectorAll('[data-exercise]').forEach((exercise) => {
    exercise.addEventListener('change', () => {
        exercise.dispatchEvent(new CustomEvent('exercise:updated', { bubbles: true }));
    });
});

// Inicializa cada actividad de clasificación con sus elementos, zonas de respuesta y controles asociados.
document.querySelectorAll('[data-classify-exercise]').forEach((activity) => {
    const form = activity.closest('form');
    const bank = activity.querySelector('[data-item-bank]');
    const items = Array.from(activity.querySelectorAll('[data-exercise-item]'));
    const zones = Array.from(activity.querySelectorAll('[data-answer-zone]'));
    const error = activity.querySelector('[data-exercise-error]');
    let selectedItemId = null;

    // Actualiza contadores, estados vacíos y el indicador de elementos todavía sin clasificar.
    const updateZones = () => {
        zones.forEach((zone) => {
            const placedItems = zone.querySelectorAll('[data-exercise-item]');
            const emptyState = zone.querySelector('[data-zone-empty]');
            const counter = zone.querySelector('[data-zone-counter]');

            if (emptyState) {
                emptyState.hidden = placedItems.length > 0;
            }
            if (counter) {
                counter.textContent = String(placedItems.length);
            }
            zone.classList.toggle('ring-2', zone.contains(document.activeElement));
        });

        const pending = items.filter((item) => {
            const input = form.querySelector(`[data-answer-input][name="answers[${item.dataset.itemId}]"]`);
            return !input || input.value === '';
        }).length;
        const counter = activity.querySelector('[data-pending-counter]');
        if (counter) {
            counter.textContent = `${pending} elementos disponibles`;
        }
    };

    // Coloca un elemento en la zona elegida y actualiza su campo oculto para que el formulario lo envíe.
    const assignItem = (itemId, zone) => {
        const item = items.find((candidate) => candidate.dataset.itemId === itemId);
        const input = itemId ? form.querySelector(`[data-answer-input][name="answers[${itemId}]"]`) : null;
        const destination = zone && zone.querySelector('[data-zone-items]');
        if (!item || !input || !destination) {
            return;
        }

        destination.appendChild(item);
        input.value = zone.dataset.answerValue;
        item.setAttribute('aria-pressed', 'false');
        item.classList.remove('ring-2', 'ring-secondary');
        selectedItemId = null;
        if (error) {
            error.hidden = true;
            error.textContent = '';
        }
        updateZones();
    };

    // Permite seleccionar elementos con clic y empezar un arrastre con ratón o pantalla táctil compatible.
    items.forEach((item) => {
        item.addEventListener('click', (event) => {
            event.stopPropagation();
            selectedItemId = item.dataset.itemId;
            items.forEach((candidate) => {
                const selected = candidate === item;
                candidate.setAttribute('aria-pressed', String(selected));
                candidate.classList.toggle('ring-2', selected);
                candidate.classList.toggle('ring-secondary', selected);
            });
            const counter = activity.querySelector('[data-pending-counter]');
            if (counter) {
                counter.textContent = 'Ahora selecciona una categoría';
            }
        });
        item.addEventListener('dragstart', (event) => {
            selectedItemId = item.dataset.itemId;
            if (event.dataTransfer) {
                event.dataTransfer.setData('text/plain', selectedItemId);
                event.dataTransfer.effectAllowed = 'move';
            }
        });
    });

    // Acepta elementos mediante clic, teclado o arrastrar y soltar en cada categoría.
    zones.forEach((zone) => {
        zone.addEventListener('click', () => {
            if (selectedItemId) {
                assignItem(selectedItemId, zone);
            }
        });
        zone.addEventListener('keydown', (event) => {
            if ((event.key === 'Enter' || event.key === ' ') && selectedItemId) {
                event.preventDefault();
                assignItem(selectedItemId, zone);
            }
        });
        zone.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'move';
            }
        });
        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            const itemId = event.dataTransfer ? event.dataTransfer.getData('text/plain') : '';
            assignItem(itemId || selectedItemId, zone);
        });
    });

    // Devuelve todos los elementos al banco y limpia las respuestas para reiniciar la actividad.
    const resetButton = activity.querySelector('[data-reset-exercise]');
    if (resetButton) {
        resetButton.addEventListener('click', () => {
            items.forEach((item) => {
                bank.appendChild(item);
                item.setAttribute('aria-pressed', 'false');
                item.classList.remove('ring-2', 'ring-secondary');
            });
            form.querySelectorAll('[data-answer-input]').forEach((input) => {
                input.value = '';
            });
            selectedItemId = null;
            updateZones();
        });
    }

    // Impide comprobar la actividad si quedan elementos pendientes y anuncia el motivo al estudiante.
    if (form) {
        form.addEventListener('submit', (event) => {
            const unanswered = items.find((item) => {
                const input = form.querySelector(`[data-answer-input][name="answers[${item.dataset.itemId}]"]`);
                return !input || input.value === '';
            });
            if (unanswered) {
                event.preventDefault();
                if (error) {
                    error.textContent = 'Coloca todos los elementos en una categoría antes de comprobar tus respuestas.';
                    error.hidden = false;
                }
                unanswered.focus();
            }
        });
    }

    // Deja contadores y zonas listos al inicializar la actividad.
    updateZones();
});
