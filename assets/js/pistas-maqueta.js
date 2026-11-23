document.addEventListener("DOMContentLoaded", function () {

    const botonAbrir = document.querySelector("[data-hint-open]");
    const panel = document.querySelector("[data-hint-overlay]");

    // Comprobar que existan los elementos
    if (!botonAbrir || !panel) {
        console.error("No se encontró el botón o el panel de pistas.");
        return;
    }

    const botonesCerrar = panel.querySelectorAll("[data-hint-close]");
    const btnUsar = panel.querySelector("[data-hint-use]");
    const textoPista = panel.querySelector("[data-hint-text]");
    const errorPista = panel.querySelector("[data-hint-error]");
    const saldos = document.querySelectorAll("[data-hint-balance], .hint-demo-balance");

    // Abrir menú lateral
    function abrirPanel() {
        panel.hidden = false;
        document.body.style.overflow = "hidden";
    }

    // Cerrar menú lateral
    function cerrarPanel() {
        panel.hidden = true;
        document.body.style.overflow = "";
        botonAbrir.focus();
    }

    // Evento del botón Ver pista
    botonAbrir.addEventListener("click", abrirPanel);

    // Eventos de los botones Cancelar y cerrar
    botonesCerrar.forEach(function (boton) {
        boton.addEventListener("click", cerrarPanel);
    });

    // Cerrar al hacer clic fuera del menú
    panel.addEventListener("click", function (evento) {
        if (evento.target === panel) {
            cerrarPanel();
        }
    });

    // Cerrar al presionar Escape
    document.addEventListener("keydown", function (evento) {
        if (evento.key === "Escape" && !panel.hidden) {
            cerrarPanel();
        }
    });

    // Descontar tokens y mostrar pista
    if (btnUsar) {
        btnUsar.addEventListener("click", async function () {
            btnUsar.disabled = true;
            const textoOriginal = btnUsar.textContent;
            btnUsar.textContent = "Procesando...";
            if (errorPista) errorPista.hidden = true;

            const datos = new FormData();
            datos.append("action", "use_hint");
            datos.append("activity_id", btnUsar.dataset.activityId || "0");

            const csrf = document.querySelector('input[name="csrf_token"]');
            if (csrf) {
                datos.append("csrf_token", csrf.value);
            }

            try {
                const respuesta = await fetch(window.location.href, {
                    method: "POST",
                    body: datos,
                });
                const resultado = await respuesta.json();

                if (!respuesta.ok || !resultado.ok) {
                    throw new Error(resultado.error || "No se pudo canjear la pista.");
                }

                // Actualiza el saldo en el panel y en el botón exterior
                saldos.forEach(function (el) {
                    if (el.hasAttribute("data-hint-balance")) {
                        el.textContent = resultado.saldo;
                    } else {
                        el.innerHTML = `<span class="material-symbols-outlined">monetization_on</span> ${resultado.saldo} tokens`;
                    }
                });

                if (textoPista) {
                    textoPista.textContent = resultado.pista;
                }

                btnUsar.textContent = "Pista usada (−12 tokens)";
            } catch (error) {
                if (errorPista) {
                    errorPista.textContent = error.message;
                    errorPista.hidden = false;
                }
                btnUsar.disabled = false;
                btnUsar.textContent = textoOriginal;
            }
        });
    }

});