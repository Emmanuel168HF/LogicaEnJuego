'use strict';

// Habilita estilos que dependen de JavaScript y gestiona la lectura en voz alta de las lecciones.
document.documentElement.classList.add('js');

// Registra minutos de aprendizaje solo mientras la pestaña está visible y el usuario interactúa.
const heartbeatUrl = document.body.dataset.sessionHeartbeat;
const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
const avatarPicker = document.querySelector('[data-avatar-picker]');
if (avatarPicker) {
    const avatarName = document.querySelector('[data-avatar-name]');
    avatarPicker.addEventListener('change', (event) => {
        if (!(event.target instanceof HTMLInputElement) || event.target.name !== 'avatar') {
            return;
        }
        if (avatarName) {
            avatarName.textContent = event.target.value;
        }
        const label = event.target.closest('label');
        if (label) {
            avatarPicker.querySelectorAll('.avatar-choice').forEach((choice) => {
                choice.classList.toggle('is-selected', choice === label);
            });
        }
    });
}

if (heartbeatUrl && csrfToken) {
    let ultimaInteraccion = Date.now();
    let enviandoLatido = false;
    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach((evento) => {
        document.addEventListener(evento, () => {
            ultimaInteraccion = Date.now();
        }, { passive: true });
    });

    window.setInterval(async () => {
        if (document.visibilityState !== 'visible' ||
            Date.now() - ultimaInteraccion > 120000 ||
            enviandoLatido) {
            return;
        }

        enviandoLatido = true;
        const datos = new FormData();
        datos.append('csrf_token', csrfToken);
        datos.append('action', 'session_heartbeat');
        datos.append('return_page', 'perfil');
        try {
            const respuesta = await fetch(heartbeatUrl, {
                method: 'POST',
                body: datos,
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!respuesta.ok && respuesta.status !== 401 && respuesta.status !== 419) {
                console.error('No se pudo guardar el tiempo de aprendizaje.');
            }
        } catch (error) {
            console.error('No se pudo conectar para guardar el tiempo de aprendizaje.', error);
        } finally {
            enviandoLatido = false;
        }
    }, 60000);
}

// Conecta cada control de lectura con el contenido indicado en su atributo data-speak.
document.querySelectorAll('[data-speak]').forEach((button) => {
    button.addEventListener('click', () => {
        const content = document.querySelector(button.dataset.speak);
        // Informa si falta el contenido asociado o el navegador no ofrece síntesis de voz.
        if (!content || !('speechSynthesis' in window)) {
            button.setAttribute('aria-label', 'La lectura en voz alta no está disponible en este navegador');
            return;
        }

        // El segundo clic detiene la lectura que ya está en curso.
        if (window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
            button.querySelector('span:last-child').textContent = 'Escuchar lección';
            return;
        }

        // Configura el idioma, inicia la lectura y restaura el texto del botón al finalizar o fallar.
        const utterance = new SpeechSynthesisUtterance(content.innerText);
        utterance.lang = document.documentElement.lang || 'es';
        utterance.onend = () => {
            button.querySelector('span:last-child').textContent = 'Escuchar lección';
        };
        utterance.onerror = () => {
            button.querySelector('span:last-child').textContent = 'Escuchar lección';
        };
        button.querySelector('span:last-child').textContent = 'Detener lectura';
        window.speechSynthesis.speak(utterance);
    });
});
