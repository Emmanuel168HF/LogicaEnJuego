<?php
declare(strict_types=1);
$usuarioLipal = function_exists('usuarioActual') ? usuarioActual() : null;
$esEjercicio = ($paginaActual ?? ($_GET['page'] ?? '')) === 'ejercicio';

if ($usuarioLipal && !$esEjercicio):
    $tieneEdad = isset($usuarioLipal['age']) && $usuarioLipal['age'] !== null;
    $edadLipal = $tieneEdad ? (int) $usuarioLipal['age'] : 15;
    $perfilLipal = ServiceIA::obtenerPerfilPorEdad($edadLipal);

    // Estilos dinámicos según rango de edad
    $clasesHeader = match ($perfilLipal['tema_css']) {
        'tema-infantil' => 'bg-gradient-to-r from-amber-400 to-orange-500 text-white',
        'tema-gamer'    => 'bg-gradient-to-r from-violet-600 to-indigo-700 text-white',
        default         => 'bg-gradient-to-r from-[#0051d5] to-[#000f3f] text-white',
    };

    $clasesBordeBurbuja = match ($perfilLipal['tema_css']) {
        'tema-infantil' => 'ring-4 ring-orange-400',
        'tema-gamer'    => 'ring-4 ring-violet-500',
        default         => 'ring-4 ring-[#0051d5]',
    };

    $clasesTextoChat = $perfilLipal['tema_css'] === 'tema-infantil' ? 'text-base leading-relaxed' : 'text-sm';
?>

<!-- Botón flotante de LIPAL (mascota libre sin círculo, más grande) -->
<button id="btn-lipal" type="button" onclick="toggleLipal()"
    aria-label="Abrir chat con <?= escapar($perfilLipal['nombre']) ?>"
    title="Habla con <?= escapar($perfilLipal['nombre']) ?>"
    class="fixed bottom-4 right-4 z-40 flex h-36 w-36 items-center justify-center bg-transparent p-0 transition-transform duration-200 hover:scale-110 focus:outline-none">
    <img src="<?= escapar($perfilLipal['avatar']) ?>"
         alt="<?= escapar($perfilLipal['nombre']) ?>"
         class="h-full w-full object-contain drop-shadow-[0_10px_14px_rgba(0,0,0,0.45)]">
</button>

<!-- Ventana flotante del chat -->
<div id="ventana-lipal" data-voz="<?= $perfilLipal['activar_voz'] ? 'true' : 'false' ?>"
    class="fixed bottom-24 right-6 z-50 hidden w-[380px] max-w-[92vw] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
    
    <!-- Cabecera -->
    <div class="flex items-center justify-between p-4 <?= $clasesHeader ?>">
        <div class="flex items-center gap-3">
            <img src="<?= escapar($perfilLipal['avatar']) ?>" alt="LIPAL" class="h-11 w-11 rounded-full bg-white p-1 object-contain shadow-sm">
            <div>
                <h3 class="font-extrabold leading-tight"><?= escapar($perfilLipal['nombre']) ?></h3>
                <p class="text-xs opacity-90">
                    <?= escapar($perfilLipal['subtitulo']) ?><?= $tieneEdad ? " · {$edadLipal} años" : '' ?>
                </p>
            </div>
        </div>
        <button type="button" onclick="toggleLipal()" aria-label="Cerrar tutor"
            class="flex h-8 w-8 items-center justify-center rounded-full text-white/90 transition hover:bg-white/20">✕</button>
    </div>

    <!-- Aviso si aún no registra su edad -->
    <?php if (!$tieneEdad): ?>
    <div class="border-b border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-900">
        💡 Agrega tu edad en <a href="<?= escapar(rutaPagina('perfil')) ?>" class="font-bold underline">Mi perfil</a> para adaptar a LIPAL a tu nivel.
    </div>
    <?php endif; ?>

    <!-- Cuerpo del chat -->
    <div id="lipal-mensajes" class="flex h-80 flex-col gap-3 overflow-y-auto bg-[#f8f9ff] p-4 <?= $clasesTextoChat ?>">
        <div class="self-start rounded-2xl rounded-tl-none border border-slate-100 bg-white p-3 text-slate-800 shadow-sm">
            ¡Hola <strong><?= escapar($usuarioLipal['name']) ?></strong>! 👋 Soy <strong><?= escapar($perfilLipal['nombre']) ?></strong>, tu tutor de aprendizaje. ¿En qué te ayudo hoy?
        </div>

        <!-- Sugerencias rápidas -->
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="enviarRapidaLipal('Dame un consejo de estudio para esta sección')"
                class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                💡 Dame un consejo
            </button>
            <button type="button" onclick="enviarRapidaLipal('Explícame este tema paso a paso')"
                class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                Explícame un tema
            </button>
            <button type="button" onclick="enviarRapidaLipal('Dame un ejercicio de práctica')"
                class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">
                Dame ejercicios
            </button>
        </div>
    </div>

    <!-- Pie de envío -->
    <div class="flex items-center gap-2 border-t border-slate-200 bg-white p-3">
        <?= campoCsrf() ?>
        <input id="input-lipal" type="text" maxlength="500" placeholder="Escribe tu duda, tema o módulo..."
            onkeypress="if(event.key==='Enter') enviarMensajeLipal()"
            class="flex-1 rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-800 outline-none focus:border-[#0051d5]">
        <button id="btn-enviar-lipal" type="button" onclick="enviarMensajeLipal()" aria-label="Enviar"
            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0051d5] font-bold text-white transition hover:bg-blue-700 disabled:opacity-50">
            ➤
        </button>
    </div>
</div>

<script>
function toggleLipal() {
    const ventana = document.getElementById('ventana-lipal');
    ventana.classList.toggle('hidden');
    ventana.classList.toggle('flex');
    if (!ventana.classList.contains('hidden')) {
        document.getElementById('input-lipal').focus();
    }
}

function enviarRapidaLipal(texto) {
    document.getElementById('input-lipal').value = texto;
    enviarMensajeLipal();
}

function leerVozLipal(texto) {
    const ventana = document.getElementById('ventana-lipal');
    if (ventana.dataset.voz === 'true' && 'speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const textoLimpio = texto.replace(/[*#_`~]/g, '');
        const locucion = new SpeechSynthesisUtterance(textoLimpio);
        locucion.lang = 'es-MX';
        locucion.rate = 0.95;
        window.speechSynthesis.speak(locucion);
    }
}

// Memoria breve de la conversación en la pestaña actual
let historialLipal = JSON.parse(sessionStorage.getItem('historial_lipal') || '[]');

function guardarHistorialLipal(rol, texto) {
    historialLipal.push({ rol, texto });
    if (historialLipal.length > 8) historialLipal = historialLipal.slice(-8);
    sessionStorage.setItem('historial_lipal', JSON.stringify(historialLipal));
}

function limpiarChatLipal() {
    historialLipal = [];
    sessionStorage.removeItem('historial_lipal');
    const caja = document.getElementById('lipal-mensajes');
    caja.innerHTML = `
        <div class="self-start rounded-2xl rounded-tl-none border border-slate-100 bg-white p-3 text-slate-800 shadow-sm">
            Conversación reiniciada. 🔄 ¿Sobre qué parte de esta lección quieres platicar?
        </div>`;
}

async function enviarMensajeLipal() {
    const input = document.getElementById('input-lipal');
    const btnEnviar = document.getElementById('btn-enviar-lipal');
    const caja = document.getElementById('lipal-mensajes');
    const mensaje = input.value.trim();
    if (!mensaje) return;

    const burbujaUser = document.createElement('div');
    burbujaUser.className = 'self-end rounded-2xl rounded-tr-none bg-[#0051d5] p-3 text-white shadow-sm max-w-[85%]';
    burbujaUser.textContent = mensaje;
    caja.appendChild(burbujaUser);
    guardarHistorialLipal('estudiante', mensaje);

    input.value = '';
    input.disabled = true;
    btnEnviar.disabled = true;

    const burbujaCargando = document.createElement('div');
    burbujaCargando.className = 'self-start rounded-2xl rounded-tl-none border border-slate-100 bg-white p-3 text-slate-400 italic shadow-sm';
    burbujaCargando.textContent = 'Pensando...';
    caja.appendChild(burbujaCargando);
    caja.scrollTop = caja.scrollHeight;

    // Extrae el título y los primeros párrafos visibles de la lección/pantalla
    const tituloPantalla = document.querySelector('h1')?.innerText || document.title;
    const parrafos = Array.from(document.querySelectorAll('main p'))
        .map(p => p.innerText.trim())
        .filter(t => t.length > 25)
        .slice(0, 4)
        .join('\n');

    const csrfInput = document.querySelector('#ventana-lipal input[name="csrf_token"]');

    const datos = new FormData();
    datos.append('mensaje', mensaje);
    datos.append('contexto', tituloPantalla);
    datos.append('contenido', parrafos);
    datos.append('historial', JSON.stringify(historialLipal));
    datos.append('modo', mensaje.toLowerCase().includes('consejo') ? 'consejo' : 'chat');
    if (csrfInput) {
        datos.append('csrf_token', csrfInput.value);
    }

    try {
        const res = await fetch('index.php?page=ia', {
            method: 'POST',
            body: datos
        });
        const data = await res.json();
        burbujaCargando.remove();

        const burbujaIA = document.createElement('div');
        burbujaIA.className = 'self-start rounded-2xl rounded-tl-none border border-slate-100 bg-white p-3 text-slate-800 shadow-sm whitespace-pre-line max-w-[88%]';
        const respuestaFinal = data.respuesta || data.error || 'No pude responder en este momento.';
        burbujaIA.textContent = respuestaFinal;
        caja.appendChild(burbujaIA);
        guardarHistorialLipal('lipal', respuestaFinal);

        // Si la IA sugiere preguntas de seguimiento, las muestra como botones clicables
        if (Array.isArray(data.sugerencias) && data.sugerencias.length > 0) {
            const filaSug = document.createElement('div');
            filaSug.className = 'flex flex-wrap gap-1.5 mt-1';
            data.sugerencias.forEach(sug => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100';
                btn.textContent = sug;
                btn.onclick = () => enviarRapidaLipal(sug);
                filaSug.appendChild(btn);
            });
            caja.appendChild(filaSug);
        }

        leerVozLipal(respuestaFinal);
    } catch (error) {
        burbujaCargando.remove();
        const burbujaError = document.createElement('div');
        burbujaError.className = 'self-start rounded-2xl bg-red-50 p-3 text-red-600';
        burbujaError.textContent = 'Error de conexión al intentar hablar con LIPAL.';
        caja.appendChild(burbujaError);
    } finally {
        input.disabled = false;
        btnEnviar.disabled = false;
        input.focus();
        caja.scrollTop = caja.scrollHeight;
    }
}
</script>
<?php endif; ?>
<!-- Cierra la estructura HTML abierta por la plantilla de encabezado. -->
</body>
</html>