'use strict';

// Expone en la barra de progreso un valor accesible limitado al intervalo válido de cero a cien.
document.querySelectorAll('[data-progress]').forEach((progress) => {
    const value = Number(progress.dataset.progress);
    if (Number.isFinite(value)) {
        progress.setAttribute('aria-valuenow', String(Math.min(100, Math.max(0, value))));
    }
});

// Combina filtros por tipo y texto para mostrar solo las actividades coincidentes.
document.querySelectorAll('[data-activity-filter-group]').forEach((filterGroup) => {
    const section = filterGroup.closest('main');
    const filters = Array.from(filterGroup.querySelectorAll('[data-activity-filter]'));
    const search = section.querySelector('[data-activity-search]');
    const cards = Array.from(section.querySelectorAll('[data-activity-card]'));
    const emptyState = section.querySelector('[data-activity-empty]');
    let activeKind = 'all';

    // Normaliza acentos y mayúsculas para que la búsqueda en español sea más tolerante.
    const normalize = (value) => value.toLocaleLowerCase('es').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    // Aplica ambos criterios de búsqueda y alterna el aviso cuando no quedan tarjetas visibles.
    const updateActivities = () => {
        const query = search ? normalize(search.value.trim()) : '';
        let visibleCount = 0;

        cards.forEach((card) => {
            const matchesKind = activeKind === 'all' || card.dataset.activityKind === activeKind;
            const matchesText = normalize(card.dataset.activityTitle || card.innerText).includes(query);
            const visible = matchesKind && matchesText;
            card.hidden = !visible;
            if (visible) {
                visibleCount++;
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleCount > 0;
        }
    };

    // Actualiza el filtro activo y su estado visual y accesible al seleccionar una categoría.
    filters.forEach((filter) => {
        filter.addEventListener('click', () => {
            activeKind = filter.dataset.activityFilter || 'all';
            filters.forEach((candidate) => {
                const active = candidate === filter;
                candidate.setAttribute('aria-pressed', String(active));
                candidate.classList.toggle('bg-primary', active);
                candidate.classList.toggle('text-on-primary', active);
                candidate.classList.toggle('bg-surface-container', !active);
                candidate.classList.toggle('text-on-surface-variant', !active);
            });
            updateActivities();
        });
    });

    // Recalcula la lista en cada cambio de texto y aplica el estado inicial de filtros al cargar.
    if (search) {
        search.addEventListener('input', updateActivities);
    }

    updateActivities();
});
