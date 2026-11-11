'use strict';

(() => {
    const root = document.documentElement;
    const preferenceKey = 'logica-en-juego-theme';
    let preference = null;

    try {
        preference = localStorage.getItem(preferenceKey);
    } catch (error) {
        console.warn('No se pudo leer la preferencia del tema; se usará la preferencia del dispositivo.', error);
    }

    const theme = preference === 'light' || preference === 'dark'
        ? preference
        : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    const applyTheme = (nextTheme) => {
        const isDark = nextTheme === 'dark';
        root.dataset.theme = nextTheme;
        root.classList.toggle('dark', isDark);
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.setAttribute('aria-pressed', String(isDark));
            button.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
            button.title = isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
            const icon = button.querySelector('.theme-toggle-icon');
            const label = button.querySelector('.theme-toggle-label');
            if (icon) {
                icon.textContent = isDark ? 'light_mode' : 'dark_mode';
            }
            if (label) {
                label.textContent = isDark ? 'Modo claro' : 'Modo oscuro';
            }
        });
    };

    applyTheme(theme);

    document.addEventListener('click', (event) => {
        const button = event.target instanceof Element
            ? event.target.closest('[data-theme-toggle]')
            : null;
        if (!button) {
            return;
        }

        const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
        try {
            localStorage.setItem(preferenceKey, nextTheme);
        } catch (error) {
            console.warn('No se pudo guardar la preferencia del tema; el cambio solo durará en esta página.', error);
        }
    });
})();
