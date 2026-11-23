
// Controla la duración y posición de las notificaciones de recompensas.
document.addEventListener('DOMContentLoaded', () => {
    const notificaciones = document.querySelectorAll(
        '[data-notificacion-recompensa]'
    );

    notificaciones.forEach((notificacion, indice) => {
        // Separa las notificaciones cuando se reciben varias recompensas.
        notificacion.style.bottom = `${30 + indice * 118}px`;

        // Espera 4 segundos antes de iniciar la salida.
        window.setTimeout(() => {
            notificacion.classList.add('is-hiding');

            // Elimina la notificación después de la animación.
            window.setTimeout(() => {
                notificacion.remove();
            }, 500);
        }, 4000);
    });
});