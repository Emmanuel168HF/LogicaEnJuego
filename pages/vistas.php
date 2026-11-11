<?php
declare(strict_types=1);

// Plantilla compartida para las pantallas públicas y de aprendizaje de la plataforma.
$paginaActual = $paginaActual ?? 'inicio';
require_once __DIR__ . '/../includes/bootstrap.php';
// Las vistas con datos personales o intentos requieren una sesión autenticada.
if (in_array($paginaActual, ['perfil', 'progreso', 'repetir_actividad', 'evaluacion_final'], true)) {
    requerirUsuario();
}
$usuarioActual = usuarioActual();
$db = null;

// Abre la conexión solo cuando la vista necesita consultar información.
$obtenerDb = static function () use (&$db): PDO {
    if (!$db instanceof PDO) {
        $db = obtenerConexion();
    }
    return $db;
};

// Construye enlaces internos con sus parámetros y escapa el resultado para HTML.
function urlVista(string $pagina, array $parametros = []): string
{
    return escapar(rutaPagina($pagina, $parametros));
}

// Inicia formularios POST con token CSRF, acción y página de retorno.
function formularioVista(string $accion, string $pagina, array $extra = []): void
{
    echo '<form method="post" action="' . urlVista($pagina) . '" class="contents">';
    echo campoCsrf();
    echo '<input type="hidden" name="action" value="' . escapar($accion) . '">';
    echo '<input type="hidden" name="return_page" value="' . escapar($pagina) . '">';
    foreach ($extra as $clave => $valor) {
        echo '<input type="hidden" name="' . escapar((string) $clave) . '" value="' . escapar((string) $valor) . '">';
    }
}

// Renderiza controles de respuesta según el tipo definido para una pregunta evaluativa.
function opcionesEvaluacion(array $pregunta): void
{
    if ($pregunta['input_type'] === 'text') {
        echo '<textarea name="answers[' . escapar($pregunta['field_key']) . ']" rows="3" maxlength="2000" ' .
            (!empty($pregunta['is_required']) ? 'required' : '') .
            ' class="mt-space-sm w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md font-body-md focus:border-secondary"></textarea>';
        return;
    }
    echo '<div class="grid gap-space-sm sm:grid-cols-2">';
    foreach ($pregunta['choices'] as $opcion) {
        $value = is_array($opcion) ? (string) ($opcion['value'] ?? '') : (string) $opcion;
        $label = is_array($opcion) ? (string) ($opcion['label'] ?? $value) : (string) $opcion;
        echo '<label class="flex cursor-pointer items-start gap-space-sm rounded-DEFAULT bg-surface-container-low p-space-md transition hover:bg-surface-container">';
        echo '<input class="mt-1 h-5 w-5 accent-secondary" type="radio" name="answers[' . escapar($pregunta['field_key']) . ']" value="' . escapar($value) . '" ' .
            (!empty($pregunta['is_required']) ? 'required' : '') . '>';
        echo '<span class="font-body-md">' . escapar($label) . '</span></label>';
    }
    echo '</div>';
}

// Genera los componentes visuales reutilizados por distintas pantallas.
function tarjeta(string $titulo, string $contenido, string $clase = ''): void
{
    echo '<article class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm ' . escapar($clase) . '">';
    echo '<h2 class="mb-space-sm font-headline-sm font-bold text-primary">' . escapar($titulo) . '</h2>' . $contenido . '</article>';
}

function listaVacia(string $mensaje): void
{
    echo '<div class="rounded-lg border border-dashed border-outline-variant bg-surface-container-lowest p-space-xl text-center font-body-md text-on-surface-variant">' .
        escapar($mensaje) . '</div>';
}

// Relaciona cada identificador de vista con el título que se muestra en el documento.
$titulos = [
    'inicio' => 'Lógica en juego',
    'menu' => 'Menú principal',
    'modulos' => 'Catálogo de módulos',
    'detalle_modulo' => 'Detalle del módulo',
    'leccion' => 'Lección educativa',
    'cuestionario' => 'Cuestionario',
    'ejercicio' => 'Ejercicio interactivo',
    'retroalimentacion' => 'Retroalimentación',
    'resultados' => 'Resultados',
    'perfil' => 'Mi perfil',
    'progreso' => 'Mi progreso',
    'repetir_actividad' => 'Mis actividades',
    'diagnostico' => 'Cuestionario diagnóstico',
    'evaluacion_final' => 'Cuestionario posterior',
];
$titulo = $titulos[$paginaActual] ?? 'Lógica en juego';
?>
<!doctype html>
<!-- Estructura común del documento y recursos visuales compartidos por las vistas. -->
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#000f3f">
    <title><?= escapar($titulo) ?> | Lógica en juego</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config={darkMode:'class',theme:{extend:{
            fontFamily:{sans:['Plus Jakarta Sans','sans-serif'],display:['Plus Jakarta Sans','sans-serif'],'body-md':['Plus Jakarta Sans','sans-serif'],'body-lg':['Plus Jakarta Sans','sans-serif'],'body-sm':['Plus Jakarta Sans','sans-serif'],'headline-sm':['Plus Jakarta Sans','sans-serif'],'headline-md':['Plus Jakarta Sans','sans-serif'],'headline-lg':['Plus Jakarta Sans','sans-serif'],'label-sm':['Plus Jakarta Sans','sans-serif'],'label-md':['Plus Jakarta Sans','sans-serif'],'label-lg':['Plus Jakarta Sans','sans-serif']},
            colors:{brand:'rgb(var(--app-primary) / <alpha-value>)',action:'rgb(var(--app-secondary) / <alpha-value>)',canvas:'rgb(var(--app-background) / <alpha-value>)',violet:'rgb(var(--app-tertiary) / <alpha-value>)',background:'rgb(var(--app-background) / <alpha-value>)',surface:'rgb(var(--app-surface) / <alpha-value>)','surface-container-lowest':'rgb(var(--app-surface-container-lowest) / <alpha-value>)','surface-container-low':'rgb(var(--app-surface-container-low) / <alpha-value>)','surface-container':'rgb(var(--app-surface-container) / <alpha-value>)','surface-container-high':'rgb(var(--app-surface-container-high) / <alpha-value>)','surface-container-highest':'rgb(var(--app-surface-container-highest) / <alpha-value>)',primary:'rgb(var(--app-primary) / <alpha-value>)',secondary:'rgb(var(--app-secondary) / <alpha-value>)',tertiary:'rgb(var(--app-tertiary) / <alpha-value>)','primary-container':'rgb(var(--app-primary-container) / <alpha-value>)','on-primary':'rgb(var(--app-on-primary) / <alpha-value>)','on-surface':'rgb(var(--app-on-surface) / <alpha-value>)','on-surface-variant':'rgb(var(--app-on-surface-variant) / <alpha-value>)','on-primary-fixed':'rgb(var(--app-primary-fixed) / <alpha-value>)','on-secondary':'rgb(var(--app-on-secondary) / <alpha-value>)','on-secondary-container':'rgb(var(--app-on-primary) / <alpha-value>)','outline-variant':'rgb(var(--app-outline-variant) / <alpha-value>)',outline:'rgb(var(--app-outline) / <alpha-value>)','surface-tint':'rgb(var(--app-surface-tint) / <alpha-value>)','primary-fixed':'rgb(var(--app-primary-fixed) / <alpha-value>)','primary-fixed-dim':'rgb(var(--app-primary-fixed-dim) / <alpha-value>)','tertiary-fixed-dim':'rgb(var(--app-tertiary-fixed-dim) / <alpha-value>)',error:'rgb(var(--app-error) / <alpha-value>)','error-container':'rgb(var(--app-error-container) / <alpha-value>)','on-error-container':'rgb(var(--app-on-error-container) / <alpha-value>)'},
            spacing:{margin:'2.5rem','space-xs':'0.25rem','space-sm':'0.5rem','space-md':'1rem','space-lg':'1.5rem','space-xl':'2rem',gutter:'1.5rem'},
            fontSize:{display:['40px',{lineHeight:'48px',letterSpacing:'-0.02em',fontWeight:'800'}],'headline-lg':['32px',{lineHeight:'40px',letterSpacing:'-0.015em',fontWeight:'700'}],'headline-md':['24px',{lineHeight:'32px',letterSpacing:'-0.01em',fontWeight:'700'}],'headline-sm':['20px',{lineHeight:'28px',fontWeight:'600'}],'body-lg':['18px',{lineHeight:'28px'}],'body-md':['16px',{lineHeight:'24px'}],'body-sm':['14px',{lineHeight:'20px'}],'label-lg':['16px',{lineHeight:'24px',fontWeight:'600'}],'label-md':['14px',{lineHeight:'20px',fontWeight:'600'}],'label-sm':['12px',{lineHeight:'16px',letterSpacing:'0.02em',fontWeight:'600'}]},
            borderRadius:{DEFAULT:'1rem',lg:'2rem',xl:'3rem'}
        }}};
    </script>
    <link rel="stylesheet" href="assets/css/estilos.css?v=20261110-2">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/tema.css?v=20261110">
    <script src="assets/js/tema.js?v=20261110"></script>
</head>
<body class="min-h-screen bg-background font-body-md text-on-surface antialiased" data-session-heartbeat="<?= $usuarioActual ? urlVista('perfil') : '' ?>">
<!-- Cabecera global: navegación, acceso, cuenta y cierre de sesión según el usuario. -->
<header class="fixed left-0 right-0 top-0 z-50 border-b border-outline-variant bg-surface-container-lowest shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
    <div class="mx-auto flex h-16 max-w-[1440px] items-center justify-between gap-4 px-4 lg:px-margin">
        <a href="<?= urlVista('inicio') ?>" class="flex shrink-0 items-center gap-space-md font-headline-sm text-headline-sm font-semibold text-primary">
            <img src="assets/images/logo.png" alt="" class="h-11 w-11 object-contain">
            <span>Lógica en juego</span>
        </a>
        <nav class="flex items-center gap-1 overflow-x-auto text-sm md:gap-space-sm" aria-label="Navegación principal">
            <?php
            $enlacesNavegacion = ['inicio' => 'Inicio', 'modulos' => 'Módulos', 'progreso' => 'Progreso', 'menu' => 'Menú'];
            if ($usuarioActual) {
                $enlacesNavegacion = ['perfil' => 'Mi perfil'] + $enlacesNavegacion;
            }
            foreach ($enlacesNavegacion as $destino => $etiqueta):
            ?>
                <?php if ($destino === 'progreso' && !$usuarioActual) { continue; } ?>
                <a class="<?= $paginaActual === $destino ? 'bg-surface-container-low text-secondary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' ?> whitespace-nowrap rounded-DEFAULT px-3 py-2 font-label-md transition-colors md:px-space-md"
                   href="<?= urlVista($destino) ?>"><?= escapar($etiqueta) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="flex shrink-0 items-center gap-2 md:gap-space-md">
            <button type="button" data-theme-toggle aria-pressed="false" class="theme-toggle">
                <span class="material-symbols-outlined theme-toggle-icon" aria-hidden="true">dark_mode</span>
                <span class="theme-toggle-label">Modo oscuro</span>
            </button>
            <?php if ($usuarioActual && ($usuarioActual['role'] ?? '') === 'admin'): ?>
                <a href="<?= urlVista('admin') ?>" class="hidden rounded-full bg-surface-container px-space-md py-space-xs font-label-sm text-secondary transition-colors hover:bg-surface-container-high sm:inline-flex">Admin</a>
            <?php elseif (!$usuarioActual): ?>
                <a href="<?= urlVista('login') ?>" class="hidden font-label-sm text-secondary hover:underline sm:inline-flex">Ingresar</a>
            <?php endif; ?>
            <?php if ($usuarioActual): ?>
                <a href="<?= urlVista('perfil') ?>" aria-label="Abrir mi perfil" title="Mi perfil" class="flex h-10 w-10 items-center justify-center rounded-full bg-tertiary-fixed-dim/40 text-2xl transition-transform hover:scale-110"><?= escapar($usuarioActual['avatar'] ?? '🧠') ?></a>
                <span class="hidden max-w-32 truncate text-sm font-semibold text-on-surface-variant sm:inline"><?= escapar($usuarioActual['username'] ?? $usuarioActual['name'] ?? '') ?></span>
                <?php formularioVista('logout', $paginaActual); ?>
                    <button aria-label="Cerrar sesión" title="Cerrar sesión" class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-on-primary transition-transform hover:scale-105">
                        <span class="material-symbols-outlined text-xl">logout</span>
                    </button>
                </form>
            <?php else: ?>
                <a aria-label="Crear cuenta" href="<?= urlVista('registro') ?>" class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-on-primary transition-transform hover:scale-105">
                    <span class="material-symbols-outlined text-xl">person</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
<!-- Contenedor principal de la pantalla y de los mensajes temporales. -->
<main class="min-h-screen w-full bg-background pt-16">
<div class="mx-auto flex w-full max-w-[1440px] flex-col px-4 py-space-xl lg:px-margin">
    <?php mostrarMensajesFlash(); ?>
    <?php if (!in_array($paginaActual, ['inicio', 'modulos', 'menu'], true)): ?>
        <!-- Ruta de navegación para identificar la pantalla actual. -->
        <div class="mb-space-xl flex items-center gap-space-xs font-label-sm text-label-sm text-on-surface-variant">
            <span class="material-symbols-outlined text-base text-secondary">explore</span>
            <span>Plataforma</span><span class="text-outline-variant">/</span>
            <h1 class="font-label-md text-label-md font-bold text-primary"><?= escapar($titulo) ?></h1>
        </div>
    <?php endif; ?>
    <?php
    switch ($paginaActual) {
        case 'inicio':
            ?>
            <!-- Portada pública con descripción, accesos al aprendizaje y pasos de la ruta. -->
            <section class="relative w-full overflow-hidden py-space-xl lg:py-space-xl">
                <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-secondary/5 blur-3xl"></div>
                <div class="pointer-events-none absolute -right-20 top-1/2 h-80 w-80 rounded-full bg-tertiary-fixed-dim/20 blur-3xl"></div>
                <div class="relative grid grid-cols-1 items-center gap-gutter lg:grid-cols-12">
                    <div class="flex flex-col justify-center lg:col-span-6 lg:pr-space-lg">
                        <div class="mb-space-md inline-flex w-fit items-center gap-space-xs rounded-full bg-surface-container-high px-space-md py-space-xs shadow-sm">
                            <span class="material-symbols-outlined text-lg text-secondary">auto_awesome</span>
                            <span class="font-label-sm uppercase tracking-wide text-secondary">Plataforma educativa interactiva</span>
                        </div>
                        <h1 class="mb-space-sm font-display text-display font-extrabold leading-tight tracking-tight text-primary">Lógica en juego</h1>
                        <p class="mb-space-md font-headline-sm font-semibold text-secondary">Aprende informática paso a paso</p>
                        <p class="mb-space-xl max-w-xl font-body-lg leading-relaxed text-on-surface-variant">
                            Descubre el fascinante mundo de la tecnología, los algoritmos y la programación con lecciones interactivas diseñadas a tu ritmo. Convierte la curiosidad en habilidades reales mediante retos visuales y dinámicos.
                        </p>
                        <div class="flex flex-wrap items-center gap-space-md">
                            <a class="group inline-flex items-center justify-center gap-space-sm rounded-DEFAULT bg-secondary px-space-xl py-space-md font-label-lg text-on-secondary shadow-md transition-all hover:bg-secondary-container hover:shadow-lg active:scale-95" href="<?= urlVista('menu') ?>">
                                Comenzar <span class="material-symbols-outlined text-lg transition-transform group-hover:translate-x-1">arrow_forward</span>
                            </a>
                            <a class="inline-flex items-center justify-center gap-space-sm rounded-DEFAULT bg-surface-container-lowest px-space-xl py-space-md font-label-lg text-primary shadow-sm transition-all hover:bg-surface-container-low" href="<?= urlVista('modulos') ?>">
                                <span class="material-symbols-outlined text-secondary">explore</span> Explorar módulos
                            </a>
                        </div>
                        <div class="mt-space-xl grid grid-cols-3 gap-space-md">
                            <?php foreach ([
                                ['school', '8+', 'Módulos lúdicos'],
                                ['military_tech', '100%', 'Práctico y guiado'],
                                ['sentiment_very_satisfied', '0 a 100', 'Sin requisitos'],
                            ] as [$icon, $value, $label]): ?>
                                <div class="rounded-DEFAULT bg-surface-container-low p-space-md shadow-sm">
                                    <div class="mb-1 flex items-center gap-space-xs text-secondary">
                                        <span class="material-symbols-outlined text-xl"><?= escapar($icon) ?></span>
                                        <span class="font-headline-sm font-bold text-primary"><?= escapar($value) ?></span>
                                    </div>
                                    <p class="font-label-sm text-on-surface-variant"><?= escapar($label) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="relative mt-space-lg lg:col-span-6 lg:mt-0">
                        <div class="relative w-full overflow-hidden rounded-lg bg-surface-container-lowest p-space-md shadow-xl">
                            <div class="relative h-[360px] w-full overflow-hidden rounded-DEFAULT bg-surface-container sm:h-[460px]">
                                <img class="h-full w-full rounded-DEFAULT object-cover" src="assets/images/hero-aprendizaje.png" alt="Estudiantes colaborando en retos de programación y pensamiento lógico">
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-primary/60 via-transparent to-transparent"></div>
                                <div class="absolute left-space-md top-space-md flex items-center gap-space-xs rounded-full bg-surface-container-lowest/90 px-space-md py-space-xs shadow-md backdrop-blur-md">
                                    <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-secondary"></span>
                                    <span class="font-label-sm font-bold text-primary">Nivel 1: Introducción a la Computación</span>
                                </div>
                                <div class="absolute bottom-space-md left-space-md right-space-md flex items-center justify-between gap-space-sm rounded-DEFAULT bg-surface-container-lowest/95 p-space-md shadow-lg backdrop-blur-md">
                                    <div class="flex min-w-0 items-center gap-space-md">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-container-high text-secondary">
                                            <span class="material-symbols-outlined">code_blocks</span>
                                        </div>
                                        <div class="min-w-0">
                                            <h2 class="truncate font-label-md font-bold text-primary">Pensamiento computacional</h2>
                                            <p class="truncate font-body-sm text-on-surface-variant">Descompón retos en soluciones lógicas</p>
                                        </div>
                                    </div>
                                    <span class="hidden items-center gap-1 rounded-full bg-surface-container px-space-sm py-space-xs font-label-sm text-secondary sm:flex">
                                        <span class="material-symbols-outlined text-base">bolt</span> Interactivo
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-2 right-6 hidden -rotate-2 items-center gap-space-sm rounded-full bg-surface-container-highest/90 px-space-md py-space-xs font-label-sm text-primary shadow-md backdrop-blur md:flex">
                            <span class="material-symbols-outlined text-secondary">psychology</span> Aprende jugando cada día
                        </div>
                    </div>
                </div>
            </section>
            <section class="mt-space-lg w-full py-space-xl">
                <div class="mx-auto mb-space-xl max-w-2xl text-center">
                    <span class="font-label-sm font-bold uppercase tracking-wider text-secondary">Ruta de aprendizaje</span>
                    <h2 class="mt-1 font-headline-lg text-primary">¿Cómo funciona la experiencia?</h2>
                    <p class="mt-2 font-body-md text-on-surface-variant">Un recorrido intuitivo, diseñado paso a paso para asegurar un aprendizaje sólido y divertido.</p>
                </div>
                <div class="grid grid-cols-1 gap-gutter md:grid-cols-3">
                    <?php foreach ([
                        ['lightbulb', 'Paso 01', 'Fundamentos claros', 'Aprende los conceptos clave de hardware, software y lógica sin tecnicismos innecesarios.'],
                        ['drag_indicator', 'Paso 02', 'Práctica dinámica', 'Clasifica elementos y experimenta con ejercicios interactivos.'],
                        ['insights', 'Paso 03', 'Retroalimentación instantánea', 'Evalúa tu progreso con explicaciones que ayudan a afianzar tu conocimiento.'],
                    ] as [$icon, $step, $heading, $description]): ?>
                        <article class="rounded-DEFAULT bg-surface-container-lowest p-space-lg shadow-sm transition-shadow hover:shadow-md">
                            <div class="mb-space-md flex h-12 w-12 items-center justify-center rounded-DEFAULT bg-surface-container-high text-secondary">
                                <span class="material-symbols-outlined text-[26px]"><?= escapar($icon) ?></span>
                            </div>
                            <span class="font-label-sm font-bold text-secondary"><?= escapar($step) ?></span>
                            <h3 class="mb-space-xs mt-1 font-headline-sm font-bold text-primary"><?= escapar($heading) ?></h3>
                            <p class="font-body-sm text-on-surface-variant"><?= escapar($description) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php
            break;

        case 'menu':
        case 'modulos':
            ?>
            <!-- Menú del estudiante y catálogo con avance, filtros y enlaces a los módulos. -->
            <?php
            // Prepara el listado, valida el filtro y calcula qué módulos están completos.
            $usuarioId = $usuarioActual ? (int) $usuarioActual['id'] : null;
            $modulos = (new Modulo($obtenerDb()))->listarPublicados($usuarioId);
            $totalModulos = count($modulos);
            $modulosCompletados = array_values(array_filter(
                $modulos,
                static fn (array $modulo): bool => (int) $modulo['lesson_count'] > 0 &&
                    (int) $modulo['completed_lessons'] >= (int) $modulo['lesson_count']
            ));
            $filtroCatalogo = $_GET['filter'] ?? 'all';
            if (!is_string($filtroCatalogo) || !in_array($filtroCatalogo, ['all', 'completed'], true)) {
                $filtroCatalogo = 'all';
            }
            if ($paginaActual === 'modulos' && $filtroCatalogo === 'completed' && $usuarioActual) {
                $modulos = $modulosCompletados;
            }
            if ($paginaActual === 'menu' && $usuarioActual) {
                // Habilita la evaluación posterior únicamente cuando se completó toda la ruta.
                $resumen = (new Progreso($obtenerDb()))->resumen((int) $usuarioActual['id']);
                $evaluacionDisponible = count($modulos) > 0 &&
                    (int) $resumen['completed_modules'] >= count($modulos);
            }
            if ($paginaActual === 'menu') {
                // Construye navegación lateral y saludo contextual para el usuario.
                echo '<div class="grid items-start gap-space-xl lg:grid-cols-12">';
                echo '<aside class="lg:col-span-3"><div class="rounded-lg bg-surface-container-lowest p-space-md shadow-sm">';
                if ($usuarioActual) {
                    $nombre = trim((string) ($usuarioActual['name'] ?? 'Estudiante'));
                    echo '<div class="mb-space-md flex items-center gap-space-md rounded-DEFAULT bg-surface-container-low p-space-md">';
                    echo '<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary font-headline-sm font-bold text-on-primary">' .
                        escapar(mb_substr($nombre, 0, 1, 'UTF-8')) . '</div><div class="min-w-0"><span class="block truncate font-label-md font-bold text-primary">' .
                        escapar($nombre) . '</span><span class="font-label-sm text-on-surface-variant">Nivel 1 · Aprendiz</span></div></div>';
                } else {
                    echo '<div class="mb-space-md rounded-DEFAULT bg-surface-container-low p-space-md"><p class="font-label-md font-bold text-primary">Tu espacio de aprendizaje</p><p class="mt-1 text-sm text-on-surface-variant">Inicia sesión para guardar tu avance.</p></div>';
                }
                echo '<nav class="flex flex-col gap-space-xs" aria-label="Menú del estudiante">';
                foreach ([['menu', 'home', 'Inicio'], ['perfil', 'face', 'Mi perfil'], ['modulos', 'view_in_ar', 'Módulos'], ['progreso', 'trending_up', 'Progreso'], ['diagnostico', 'quiz', 'Diagnóstico']] as [$destino, $icono, $etiqueta]) {
                    if ($destino === 'progreso' && !$usuarioActual) {
                        continue;
                    }
                    echo '<a class="flex items-center gap-space-sm rounded-DEFAULT px-space-md py-space-sm font-label-md transition-colors ' .
                        ($destino === $paginaActual ? 'bg-surface-container-low text-secondary' : 'text-on-surface-variant hover:bg-surface-container-low') .
                        '" href="' . urlVista($destino) . '"><span class="material-symbols-outlined text-xl">' . escapar($icono) . '</span>' . escapar($etiqueta) . '</a>';
                }
                if ($usuarioActual && $evaluacionDisponible) {
                    echo '<a class="flex items-center gap-space-sm rounded-DEFAULT px-space-md py-space-sm font-label-md text-on-surface-variant transition-colors hover:bg-surface-container-low" href="' .
                        urlVista('evaluacion_final') . '"><span class="material-symbols-outlined text-xl">fact_check</span>Evaluación final</a>';
                } elseif ($usuarioActual) {
                    echo '<span aria-disabled="true" class="flex items-center gap-space-sm rounded-DEFAULT px-space-md py-space-sm font-label-md text-outline"><span class="material-symbols-outlined text-xl">lock</span>Evaluación final · bloqueada</span>';
                }
                if ($usuarioActual && ($usuarioActual['role'] ?? '') === 'admin') {
                    echo '<a class="flex items-center gap-space-sm rounded-DEFAULT px-space-md py-space-sm font-label-md text-on-surface-variant hover:bg-surface-container-low" href="' .
                        urlVista('admin') . '"><span class="material-symbols-outlined text-xl">settings</span>Administración</a>';
                }
                echo '</nav></div></aside><section class="min-w-0 lg:col-span-9">';
                echo '<div class="mb-space-xl"><h1 class="font-headline-lg text-primary">¿Qué quieres aprender hoy?</h1><p class="mt-2 font-body-md text-on-surface-variant">' .
                    ($usuarioActual ? 'Continúa tu ruta y explora nuevos retos.' : 'Explora las lecciones y crea una cuenta para conservar tu progreso.') .
                    '</p></div>';
            }
            if ($paginaActual === 'menu' && $usuarioActual) {
                echo '<section class="mb-space-lg grid gap-space-md sm:grid-cols-3">';
                tarjeta('Avance', escapar($resumen['completion_percentage']) . '%');
                tarjeta('Puntos', escapar($resumen['points']));
                tarjeta('Insignias', escapar($resumen['badges']));
                echo '</section>';
            }
            if ($paginaActual === 'menu') {
                echo '<div class="mb-space-lg flex flex-wrap gap-space-sm">';
                echo '<a class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-sm font-label-md text-on-secondary" href="' . urlVista('modulos') . '">Ver todos los módulos <span class="material-symbols-outlined text-lg">arrow_forward</span></a>';
                if ($usuarioActual) {
                    if ($evaluacionDisponible) {
                        echo '<a class="inline-flex items-center gap-space-xs rounded-full bg-surface-container px-space-lg py-space-sm font-label-md text-primary" href="' . urlVista('evaluacion_final') . '">Evaluación final <span class="material-symbols-outlined text-lg">arrow_forward</span></a>';
                    }
                }
                echo '</div>';
            }
            if ($paginaActual === 'modulos') {
                // Encabezado del catálogo y opciones para filtrar el contenido.
                echo '<div class="mb-space-xl flex flex-col justify-between gap-space-lg md:flex-row md:items-end">';
                echo '<div class="flex flex-col gap-space-xs"><div class="flex items-center gap-space-xs font-label-sm text-secondary"><span class="material-symbols-outlined text-base">school</span><span>Ruta de aprendizaje gamificada</span></div>';
                echo '<h1 class="font-headline-lg text-primary">Catálogo de módulos</h1>';
                echo '<p class="max-w-2xl font-body-md text-on-surface-variant">Explora trece estaciones de aprendizaje, desde los componentes básicos hasta la programación, la inteligencia artificial y sus usos responsables.</p></div>';
                echo '<nav class="flex items-center gap-space-xs self-start rounded-full bg-surface-container p-space-xs md:self-auto" aria-label="Filtrar módulos">';
                echo '<a class="flex items-center gap-space-xs rounded-full px-space-md py-space-xs font-label-sm ' .
                    ($filtroCatalogo === 'all' ? 'bg-surface-container-lowest text-primary shadow-sm' : 'text-on-surface-variant') .
                    '" href="' . urlVista('modulos', ['filter' => 'all']) . '"><span class="h-2 w-2 rounded-full bg-secondary"></span>Todos (' . escapar($totalModulos) . ')</a>';
                if ($usuarioActual) {
                    echo '<a class="flex items-center gap-space-xs rounded-full px-space-md py-space-xs font-label-sm ' .
                        ($filtroCatalogo === 'completed' ? 'bg-surface-container-lowest text-primary shadow-sm' : 'text-on-surface-variant') .
                        '" href="' . urlVista('modulos', ['filter' => 'completed']) . '"><span class="h-2 w-2 rounded-full bg-surface-tint"></span>Completados (' .
                        escapar(count($modulosCompletados)) . ')</a>';
                } else {
                    echo '<a class="flex items-center gap-space-xs rounded-full px-space-md py-space-xs font-label-sm text-on-surface-variant" href="' .
                        urlVista('login') . '"><span class="h-2 w-2 rounded-full bg-surface-tint"></span>Completados</a>';
                }
                echo '</nav></div>';
                if ($filtroCatalogo === 'completed' && !$usuarioActual) {
                    listaVacia('Inicia sesión para consultar tus módulos completados.');
                    break;
                }
            }
            if (!$modulos) {
                // Mensaje alternativo cuando no hay módulos para mostrar.
                listaVacia($filtroCatalogo === 'completed'
                    ? 'Aún no has completado ningún módulo. Termina todas sus lecciones para verlo aquí.'
                    : 'Todavía no hay módulos disponibles.');
            } else {
                // Tarjetas dinámicas con imagen, dificultad, estado y enlace de cada módulo.
                $iconosModulo = [
                    'conoce-tu-computadora' => 'desktop_windows',
                    'hardware-y-software' => 'memory',
                    'fundamentos-digitales' => 'pin',
                    'seguridad-digital' => 'verified_user',
                    'pensamiento-logico' => 'psychology',
                    'algoritmos' => 'alt_route',
                    'diagramas-y-pseudocodigo' => 'account_tree',
                    'fundamentos-de-programacion' => 'terminal',
                ];
                $nivelModulo = ['inicial' => 1, 'intermedio' => 2, 'avanzado' => 3];
                echo '<section class="mb-space-xl grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-4">';
                foreach ($modulos as $modulo) {
                    $total = (int) $modulo['lesson_count'];
                    $completadas = (int) $modulo['completed_lessons'];
                    $porcentaje = $total > 0 ? (int) round($completadas * 100 / $total) : 0;
                    $slug = (string) $modulo['slug'];
                    $icono = $iconosModulo[$slug] ?? 'code';
                    $nivel = $nivelModulo[$modulo['difficulty'] ?? 'inicial'] ?? 1;
                    $imagen = 'assets/images/' . rawurlencode($slug) . '.png';
                    if (!is_file(__DIR__ . '/../' . $imagen)) {
                        $imagen = 'assets/images/' . rawurlencode($slug) . '.svg';
                        if (!is_file(__DIR__ . '/../' . $imagen)) {
                            $imagen = 'assets/images/hero-aprendizaje.png';
                        }
                    }
                    $moduloUrl = urlVista('detalle_modulo', ['module' => $modulo['id']]);
                    echo '<article class="group relative flex flex-col rounded-DEFAULT bg-surface-container-lowest p-space-lg shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">';
                    echo '<a class="relative mb-space-md flex h-36 w-full items-center justify-center overflow-hidden rounded-DEFAULT bg-surface-container-low" href="' . $moduloUrl . '" aria-label="Abrir módulo ' . escapar($modulo['sort_order']) . '">';
                    echo '<img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" src="' . escapar($imagen) . '" alt="Ilustración: ' . escapar($modulo['title']) . '">';
                    echo '<span class="absolute bottom-space-sm right-space-sm flex h-7 w-7 items-center justify-center rounded-full bg-surface-container-lowest text-secondary shadow-sm"><span class="material-symbols-outlined text-base">' . escapar($icono) . '</span></span></a>';
                    echo '<div class="flex flex-grow flex-col"><h2 class="mb-space-xs line-clamp-1 font-headline-sm text-primary transition-colors group-hover:text-secondary">' . escapar((int) $modulo['sort_order']) . '. ' . escapar($modulo['title']) . '</h2>';
                    echo '<p class="mb-space-md line-clamp-2 min-h-10 font-body-sm text-on-surface-variant">' . escapar($modulo['description'] ?: 'Descubre conceptos y practica con retos interactivos.') . '</p>';
                    echo '<div class="mt-auto flex items-center justify-between pt-space-sm"><div class="flex items-center gap-space-xs"><span class="mr-1 font-label-sm text-on-surface-variant">Nivel:</span>';
                    for ($punto = 1; $punto <= 3; $punto++) {
                        echo '<span class="h-2.5 w-2.5 rounded-full ' . ($punto <= $nivel ? 'bg-secondary' : 'bg-outline-variant') . '"></span>';
                    }
                    echo '</div><a aria-label="Abrir módulo ' . escapar($modulo['sort_order']) . '" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-secondary transition-colors group-hover:bg-secondary group-hover:text-on-secondary" href="' . $moduloUrl . '"><span class="material-symbols-outlined text-lg">arrow_forward</span></a></div>';
                    if ($total > 0 && $porcentaje > 0) {
                        echo '<div class="mt-3 h-1 overflow-hidden rounded-full bg-surface-container"><div class="h-full bg-secondary" style="width:' . $porcentaje . '%"></div></div>';
                    }
                    echo '</div></article>';
                }
                echo '</section>';
            }
            if ($paginaActual === 'modulos' && $filtroCatalogo !== 'completed') {
                echo '<section class="relative mb-space-xl flex flex-col items-center justify-between gap-space-xl overflow-hidden rounded-lg bg-primary-container p-space-xl text-on-primary shadow-md md:flex-row">';
                echo '<div class="z-10 flex max-w-xl flex-col gap-space-xs"><div class="inline-flex items-center gap-space-xs font-label-sm text-primary-fixed"><span class="material-symbols-outlined text-lg">verified</span>Ruta secuencial recomendada</div>';
                echo '<h2 class="font-headline-md text-surface-container-lowest">¿Comenzando por primera vez?</h2><p class="font-body-sm text-surface-container-high">Sigue los módulos en orden para construir bases sólidas de informática y programación.</p></div>';
                echo '<div class="z-10 flex flex-wrap items-center gap-space-md"><a class="flex items-center gap-space-xs rounded-full bg-secondary px-space-xl py-space-md font-label-md text-on-secondary shadow-md transition hover:bg-secondary-container" href="' .
                    urlVista('detalle_modulo', ['module' => $totalModulos > 0 ? $modulos[0]['id'] : 1]) .
                    '">Iniciar módulo 1 <span class="material-symbols-outlined text-lg">play_arrow</span></a>';
                if ($usuarioActual) {
                    echo '<a class="flex items-center gap-space-xs rounded-full bg-white/10 px-space-lg py-space-md font-label-md text-on-primary transition hover:bg-white/20" href="' .
                        urlVista('progreso') . '"><span class="material-symbols-outlined text-lg">analytics</span>Ver mi avance</a>';
                }
                echo '</div><div class="pointer-events-none absolute -bottom-16 -right-12 h-64 w-64 rounded-full bg-secondary/20 blur-3xl"></div></section>';
            }
            if ($paginaActual === 'menu') {
                echo '<div class="mt-space-lg rounded-DEFAULT bg-surface-container-low p-space-md text-sm text-on-surface-variant"><span class="font-label-md font-bold text-primary">Consejo de estudio:</span> avanza a tu ritmo, lee la lección y prueba el reto antes de continuar.</div>';
                echo '</section></div>';
            }
            break;

        case 'detalle_modulo':
            ?>
            <!-- Ficha del módulo, objetivos, avance del alumno y lista de unidades y actividades. -->
            <?php
            // Resuelve el módulo solicitado o selecciona uno publicado como opción inicial.
            $modeloModulo = new Modulo($obtenerDb());
            $moduleId = filter_var($_GET['module'] ?? null, FILTER_VALIDATE_INT);
            if (!$moduleId) {
                $publicados = $modeloModulo->listarPublicados();
                $moduleId = $publicados ? (int) $publicados[0]['id'] : 0;
            }
            $modulo = $moduleId ? $modeloModulo->buscarPorId((int) $moduleId) : null;
            if (!$modulo) {
                listaVacia('Selecciona un módulo publicado del catálogo para ver sus lecciones.');
                echo '<a class="mt-4 inline-block font-bold text-action" href="' . urlVista('modulos') . '">Volver al catálogo</a>';
                break;
            }
            $lecciones = (new Leccion($obtenerDb()))->listarPorModulo((int) $modulo['id'], $usuarioActual ? (int) $usuarioActual['id'] : null);
            $siguienteLeccion = null;
            $completadas = 0;
            foreach ($lecciones as $leccion) {
                if (!empty($leccion['is_completed'])) {
                    $completadas++;
                }
                if (empty($leccion['is_completed'])) {
                    $siguienteLeccion = $leccion;
                }
            }
            $actividades = $modeloModulo->actividades((int) $modulo['id']);
            $imagenModulo = 'assets/images/' . rawurlencode((string) $modulo['slug']) . '.png';
            if (!is_file(__DIR__ . '/../' . $imagenModulo)) {
                $imagenModulo = 'assets/images/' . rawurlencode((string) $modulo['slug']) . '.svg';
                if (!is_file(__DIR__ . '/../' . $imagenModulo)) {
                    $imagenModulo = 'assets/images/hero-aprendizaje.png';
                }
            }
            $avanceModulo = count($lecciones) > 0 ? (int) round($completadas * 100 / count($lecciones)) : 0;
            // Resumen visual del módulo con progreso y acceso para continuar.
            echo '<nav class="mb-space-lg flex items-center gap-space-xs font-label-md text-on-surface-variant" aria-label="Migas de pan">';
            echo '<a class="flex items-center gap-space-xs transition-colors hover:text-secondary" href="' . urlVista('modulos') . '"><span class="material-symbols-outlined text-lg">folder_open</span>Módulos</a>';
            echo '<span class="text-outline-variant">/</span><span class="font-semibold text-secondary">' . escapar((int) $modulo['sort_order']) . '. ' . escapar($modulo['title']) . '</span></nav>';
            echo '<div class="grid items-start gap-space-xl lg:grid-cols-12">';
            echo '<section class="flex flex-col gap-space-lg lg:col-span-5">';
            echo '<article class="overflow-hidden rounded-lg bg-surface-container-lowest shadow-sm"><div class="relative h-56 overflow-hidden bg-surface-container-high sm:h-64">';
            echo '<img class="h-full w-full object-cover" src="' . escapar($imagenModulo) . '" alt="Ilustración: ' . escapar($modulo['title']) . '">';
            echo '<span class="absolute bottom-space-md left-space-md rounded-full bg-primary/90 px-space-md py-space-xs font-label-sm text-on-primary">Módulo ' . escapar((int) $modulo['sort_order']) . '</span></div>';
            echo '<div class="p-space-lg"><div class="mb-space-sm flex flex-wrap items-center gap-space-sm font-label-sm text-on-surface-variant">';
            echo '<span class="inline-flex items-center gap-space-xs"><span class="material-symbols-outlined text-lg text-secondary">schedule</span>' . escapar($modulo['estimated_minutes'] ?? 20) . ' min</span>';
            echo '<span class="rounded-full bg-surface-container-low px-space-sm py-1">' . escapar(ucfirst((string) ($modulo['difficulty'] ?? 'inicial'))) . '</span></div>';
            echo '<h1 class="mb-space-sm font-headline-lg text-primary">' . escapar($modulo['title']) . '</h1>';
            echo '<p class="mb-space-lg font-body-md text-on-surface-variant">' . escapar($modulo['description'] ?? '') . '</p>';
            echo '<div class="mb-space-xs flex items-center justify-between font-label-sm text-on-surface-variant"><span>Progreso del módulo</span><span class="font-bold text-secondary">' .
                escapar($completadas) . '/' . escapar(count($lecciones)) . ' lecciones · ' . escapar($avanceModulo) . '%</span></div>';
            echo '<div class="h-2.5 overflow-hidden rounded-full bg-surface-container"><div class="h-full rounded-full bg-secondary transition-all" style="width:' . $avanceModulo . '%"></div></div>';
            if ($siguienteLeccion !== null) {
                echo '<a class="mt-space-lg inline-flex w-full items-center justify-center gap-space-sm rounded-full bg-secondary px-space-lg py-space-md font-label-md text-on-secondary shadow-md transition hover:bg-secondary-container" href="' .
                    urlVista('leccion', ['lesson' => $siguienteLeccion['id']]) . '">Continuar módulo <span class="material-symbols-outlined">arrow_forward</span></a>';
            }
            echo '</div></article>';
            echo '<article class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><h2 class="mb-space-sm font-headline-sm font-bold text-primary">Lo que aprenderás</h2><p class="whitespace-pre-line font-body-sm leading-6 text-on-surface-variant">' .
                escapar($modulo['objectives'] ?: 'Los objetivos de aprendizaje se están preparando.') . '</p></article>';
            echo '</section>';
            echo '<section class="flex flex-col gap-space-md lg:col-span-7"><div class="flex items-end justify-between gap-space-sm"><div><p class="font-label-sm uppercase tracking-wide text-secondary">Tu ruta de aprendizaje</p><h2 class="font-headline-lg text-primary">Lecciones del módulo</h2></div><span class="font-label-sm text-on-surface-variant">' . escapar(count($lecciones)) . ' unidades</span></div>';
            if (!$lecciones) {
                listaVacia('Este módulo todavía no tiene lecciones publicadas.');
            } else {
                echo '<div class="flex flex-col gap-space-sm">';
                foreach ($lecciones as $indice => $leccion) {
                    $terminada = !empty($leccion['is_completed']);
                    echo '<a class="group flex items-center gap-space-md rounded-DEFAULT bg-surface-container-lowest p-space-md shadow-sm transition hover:bg-surface-container-low" href="' .
                        urlVista('leccion', ['lesson' => $leccion['id']]) . '">';
                    echo '<span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full ' . ($terminada ? 'bg-secondary text-on-secondary' : 'bg-surface-container-high text-secondary') . '"><span class="material-symbols-outlined">' .
                        ($terminada ? 'check_circle' : ($indice === 0 ? 'play_arrow' : 'check_box_outline_blank')) . '</span></span>';
                    echo '<span class="min-w-0 flex-grow"><span class="block font-label-md font-bold text-primary">' . escapar($leccion['title']) . '</span>';
                    echo '<span class="mt-1 block font-body-sm text-on-surface-variant">' . escapar($leccion['estimated_minutes']) . ' min de lectura · ' .
                        ($terminada ? 'Finalizada' : ($indice === 0 ? 'Siguiente lección' : 'Pendiente')) . '</span></span>';
                    echo '<span class="material-symbols-outlined text-secondary transition-transform group-hover:translate-x-1">arrow_forward</span></a>';
                }
                echo '</div>';
            }
            if ($actividades) {
                echo '<div class="mt-space-md"><h2 class="mb-space-sm font-headline-sm font-bold text-primary">Practica lo aprendido</h2><div class="grid gap-space-sm sm:grid-cols-2">';
            }
            foreach ($actividades as $actividad) {
                $destino = $actividad['type'] === 'quiz' ? 'cuestionario' : 'ejercicio';
                $iconoActividad = $actividad['type'] === 'quiz' ? 'quiz' : 'sports_esports';
                echo '<a class="flex items-center gap-space-sm rounded-DEFAULT bg-surface-container-lowest p-space-md font-label-md text-primary shadow-sm transition hover:bg-surface-container-low" href="' .
                    urlVista($destino, ['activity' => $actividad['id']]) . '"><span class="material-symbols-outlined text-2xl text-secondary">' .
                    $iconoActividad . '</span><span class="min-w-0 flex-grow">' . escapar($actividad['title']) . '</span><span class="material-symbols-outlined text-secondary">arrow_forward</span></a>';
            }
            if ($actividades) {
                echo '</div></div>';
            }
            echo '<a class="mt-space-md inline-flex items-center gap-space-xs font-label-md text-secondary hover:underline" href="' . urlVista('modulos') . '"><span class="material-symbols-outlined">arrow_back</span>Volver al catálogo</a></section></div>';
            break;

        case 'leccion':
            ?>
            <!-- Contenido de estudio, avance de la ruta, idea clave y navegación entre lecciones. -->
            <?php
            // Divide el texto en párrafos para separar introducción, lectura y concepto destacado.
            $leccionId = filter_var($_GET['lesson'] ?? null, FILTER_VALIDATE_INT);
            $leccion = $leccionId ? (new Leccion($obtenerDb()))->buscar((int) $leccionId) : null;
            if (!$leccion) {
                listaVacia('Selecciona una lección publicada desde el detalle de un módulo.');
                echo '<a class="mt-4 inline-block font-bold text-action" href="' . urlVista('modulos') . '">Ver módulos</a>';
                break;
            }
            $leccionesModulo = (new Leccion($obtenerDb()))->listarPorModulo((int) $leccion['module_id'], $usuarioActual ? (int) $usuarioActual['id'] : null);
            $indiceLeccion = array_search((int) $leccion['id'], array_map(static fn ($row): int => (int) $row['id'], $leccionesModulo), true);
            $indiceLeccion = $indiceLeccion === false ? 0 : $indiceLeccion;
            $leccionesCompletadas = count(array_filter($leccionesModulo, static fn ($row): bool => !empty($row['is_completed'])));
            $avanceLeccion = count($leccionesModulo) > 0 ? (int) round($leccionesCompletadas * 100 / count($leccionesModulo)) : 0;
            $parrafosLeccion = preg_split('/\R\s*\R/', trim((string) $leccion['content'])) ?: [];
            $introLeccion = $parrafosLeccion[0] ?? '';
            $contenidoLeccion = implode("\n\n", array_slice($parrafosLeccion, 1));
            $ideaClaveLeccion = $parrafosLeccion[1] ?? $introLeccion;
            // Presenta la lectura y los controles para guardar el avance del estudiante.
            echo '<div class="mb-space-lg flex flex-wrap items-center justify-between gap-space-md rounded-DEFAULT bg-surface-container-lowest p-space-md shadow-sm">';
            echo '<a class="inline-flex items-center gap-space-xs font-label-md text-secondary hover:underline" href="' . urlVista('detalle_modulo', ['module' => $leccion['module_id']]) . '"><span class="material-symbols-outlined text-lg">folder_open</span>' . escapar($leccion['module_title']) . '</a>';
            echo '<div class="flex items-center gap-space-sm"><span class="font-label-sm text-on-surface-variant">Lección ' . escapar($indiceLeccion + 1) . ' de ' . escapar(count($leccionesModulo)) . ' · ' . escapar($avanceLeccion) . '%</span><div class="h-2 w-24 overflow-hidden rounded-full bg-surface-container sm:w-36"><div class="h-full rounded-full bg-secondary" style="width:' . $avanceLeccion . '%"></div></div></div></div>';
            echo '<header class="mb-space-xl flex flex-col justify-between gap-space-md md:flex-row md:items-end"><div class="max-w-3xl">';
            echo '<div class="mb-space-sm flex flex-wrap items-center gap-space-sm"><span class="rounded-full bg-surface-container-high px-space-sm py-1 font-label-sm text-secondary">' . escapar($leccion['module_title']) . '</span>';
            echo '<span class="inline-flex items-center gap-space-xs rounded-full bg-surface-container px-space-sm py-1 font-label-sm text-on-surface-variant"><span class="material-symbols-outlined text-base">schedule</span>' . escapar($leccion['estimated_minutes']) . ' min de lectura</span></div>';
            echo '<h1 class="font-display text-display text-primary">' . escapar($leccion['title']) . '</h1>';
            echo '<p class="mt-space-sm font-body-lg text-on-surface-variant">' . escapar($introLeccion) . '</p></div>';
            echo '<button type="button" data-speak="#lesson-reading" class="inline-flex shrink-0 items-center gap-space-xs rounded-full bg-surface-container-low px-space-md py-space-sm font-label-md text-secondary transition hover:bg-surface-container-high"><span class="material-symbols-outlined">volume_up</span><span>Escuchar lección</span></button></header>';
            echo '<div class="grid items-start gap-space-xl lg:grid-cols-12"><section id="lesson-content" class="flex flex-col gap-space-lg lg:col-span-8">';
            echo '<article id="lesson-reading" class="rounded-DEFAULT bg-surface-container-lowest p-space-xl shadow-sm"><div class="prose max-w-none whitespace-pre-line font-body-md leading-7 text-on-surface">' . nl2br(escapar($contenidoLeccion !== '' ? $contenidoLeccion : $introLeccion)) . '</div></article>';
            if ($usuarioActual) {
                echo '<div class="rounded-DEFAULT bg-surface-container-lowest p-space-lg shadow-sm">';
                formularioVista('complete_lesson', $paginaActual, ['lesson_id' => $leccion['id']]);
                    echo '<button class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-md font-label-md text-on-secondary shadow-md transition hover:bg-secondary-container"><span class="material-symbols-outlined">task_alt</span>Marcar lección como completada</button></form>';
                echo '</div>';
            } else {
                echo '<p class="rounded-DEFAULT bg-surface-container-low p-space-md">Inicia sesión para guardar tu progreso: <a class="font-bold text-secondary" href="' . urlVista('login') . '">Iniciar sesión</a></p>';
            }
            if ($indiceLeccion !== false) {
                echo '<nav class="flex flex-wrap justify-between gap-3" aria-label="Navegación entre lecciones">';
                if ($indiceLeccion > 0) {
                    echo '<a class="inline-flex items-center gap-space-xs rounded-full bg-surface-container-lowest px-space-lg py-space-md font-label-md text-primary shadow-sm" href="' . urlVista('leccion', ['lesson' => $leccionesModulo[$indiceLeccion - 1]['id']]) . '"><span class="material-symbols-outlined">arrow_back</span>Lección anterior</a>';
                } else {
                    echo '<span></span>';
                }
                if (isset($leccionesModulo[$indiceLeccion + 1])) {
                    echo '<a class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-md font-label-md text-on-secondary shadow-md" href="' . urlVista('leccion', ['lesson' => $leccionesModulo[$indiceLeccion + 1]['id']]) . '">Siguiente lección<span class="material-symbols-outlined">arrow_forward</span></a>';
                } else {
                    echo '<a class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-md font-label-md text-on-secondary shadow-md" href="' . urlVista('detalle_modulo', ['module' => $leccion['module_id']]) . '">Volver al módulo<span class="material-symbols-outlined">arrow_forward</span></a>';
                }
                echo '</nav>';
            }
            echo '</section><aside class="flex flex-col gap-space-lg lg:sticky lg:top-24 lg:col-span-4">';
            echo '<article class="rounded-DEFAULT bg-surface-container-lowest p-space-lg shadow-sm"><div class="mb-space-md flex items-center gap-space-sm text-secondary"><span class="material-symbols-outlined text-2xl">lightbulb</span><h2 class="font-headline-sm font-bold text-primary">Idea clave</h2></div>';
            echo '<p class="font-body-sm leading-6 text-on-surface-variant">' . escapar($ideaClaveLeccion) . '</p></article>';
            echo '<article class="rounded-DEFAULT bg-surface-container-lowest p-space-lg shadow-sm"><h2 class="mb-space-md font-label-md font-bold text-primary">Tu ruta</h2><ol class="flex flex-col gap-space-sm">';
            foreach ($leccionesModulo as $indice => $paso) {
                $actual = (int) $paso['id'] === (int) $leccion['id'];
                echo '<li><a class="flex items-center gap-space-sm rounded-DEFAULT p-space-sm ' . ($actual ? 'bg-surface-container-high text-primary' : 'text-on-surface-variant hover:bg-surface-container-low') . '" href="' . urlVista('leccion', ['lesson' => $paso['id']]) . '">';
                echo '<span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full ' . (!empty($paso['is_completed']) ? 'bg-secondary text-on-secondary' : 'bg-surface-container text-secondary') . '"><span class="material-symbols-outlined text-lg">' .
                    (!empty($paso['is_completed']) ? 'check' : ($actual ? 'play_arrow' : 'lock_open')) . '</span></span>';
                echo '<span class="font-label-sm">' . escapar($indice + 1) . '. ' . escapar($paso['title']) . '</span></a></li>';
            }
            echo '</ol></article></aside></div>';
            $actividades = (new Modulo($obtenerDb()))->actividades((int) $leccion['module_id']);
            if ($actividades) {
                echo '<section class="mt-space-xl rounded-lg bg-surface-container-low p-space-lg"><h2 class="mb-space-md font-headline-sm font-bold text-primary">Practica lo aprendido</h2><div class="flex flex-wrap gap-space-sm">';
                foreach ($actividades as $actividad) {
                    $destino = $actividad['type'] === 'quiz' ? 'cuestionario' : 'ejercicio';
                    echo '<a class="rounded-full bg-secondary px-space-lg py-space-md font-label-md text-on-secondary shadow-sm" href="' .
                        urlVista($destino, ['activity' => $actividad['id']]) . '">' . escapar($actividad['title']) . ' <span aria-hidden="true">→</span></a>';
                }
                echo '</div></section>';
            }
            break;

        case 'cuestionario':
            if (!$usuarioActual) {
                listaVacia('Inicia sesión para responder el cuestionario y guardar tu resultado.');
                echo '<a class="mt-4 inline-block rounded-full bg-action px-5 py-3 font-bold text-white" href="' .
                    urlVista('login') . '">Iniciar sesión</a>';
                break;
            }
            $modeloPregunta = new Pregunta($obtenerDb());
            ?>
            <!-- Formulario de preguntas de opción múltiple para una actividad publicada. -->
            <?php
            // Valida la actividad y recupera las preguntas que se mostrarán al estudiante.
            $actividadId = filter_var($_GET['activity'] ?? null, FILTER_VALIDATE_INT);
            if (!$actividadId) {
                $lista = $obtenerDb()->query("SELECT id FROM activities WHERE type='quiz' AND is_published=1 ORDER BY id LIMIT 1")->fetchColumn();
                $actividadId = $lista ? (int) $lista : 0;
            }
            $actividad = $actividadId ? $modeloPregunta->actividad((int) $actividadId) : null;
            $preguntas = $actividad ? $modeloPregunta->cuestionario((int) $actividad['id']) : [];
            if (!$actividad || !$preguntas) {
                listaVacia('No hay cuestionarios publicados con preguntas completas. Consulta al administrador del curso.');
                break;
            }
            echo '<header class="mb-space-lg"><div class="mb-space-xs flex items-center gap-space-xs font-label-sm uppercase tracking-wide text-secondary"><span class="material-symbols-outlined text-lg">quiz</span><span>Cuestionario del módulo</span></div>';
            echo '<h1 class="font-display text-display text-primary">' . escapar($actividad['title']) . '</h1>';
            echo '<p class="mt-space-xs max-w-3xl font-body-md text-on-surface-variant">Lee cada pregunta y selecciona la respuesta que consideres correcta. Al terminar recibirás retroalimentación inmediata.</p></header>';
            echo '<form method="post" action="' . urlVista($paginaActual) . '" data-questionnaire class="space-y-space-lg">';
            echo campoCsrf() . '<input type="hidden" name="action" value="submit_quiz"><input type="hidden" name="return_page" value="cuestionario">';
            echo '<input type="hidden" name="activity_id" value="' . escapar($actividad['id']) . '">';
            echo '<div class="rounded-DEFAULT bg-surface-container-lowest p-space-md shadow-sm"><div class="mb-space-xs flex items-center justify-between font-label-sm text-on-surface-variant"><span>Avance del cuestionario</span><span>' . escapar(count($preguntas)) . ' preguntas</span></div><div class="h-2.5 overflow-hidden rounded-full bg-surface-container"><div class="h-full rounded-full bg-secondary" style="width:100%"></div></div></div>';
            foreach ($preguntas as $indice => $pregunta) {
                echo '<fieldset data-required-question class="relative overflow-hidden rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><div class="absolute left-0 top-0 h-full w-1.5 bg-secondary"></div>';
                echo '<div class="mb-space-sm flex flex-wrap items-center justify-between gap-space-xs"><span class="font-label-sm uppercase tracking-wide text-on-surface-variant">Pregunta ' .
                    escapar($indice + 1) . ' de ' . escapar(count($preguntas)) . '</span><span class="rounded-full bg-surface-container px-space-sm py-1 font-label-sm text-on-surface-variant">Opción múltiple</span></div>';
                echo '<legend class="mb-space-md block font-headline-md text-primary">' . escapar($pregunta['prompt']) . '</legend><div class="grid gap-space-sm">';
                foreach ($pregunta['options'] as $opcion) {
                    echo '<label class="group flex cursor-pointer items-start gap-space-md rounded-DEFAULT bg-surface-container-lowest p-space-md shadow-sm transition hover:bg-surface-container-low has-[:checked]:bg-surface-container-low">';
                    echo '<input class="mt-1 h-5 w-5 accent-secondary" type="radio" name="answers[' . escapar($pregunta['id']) . ']" value="' . escapar($opcion['id']) . '" required>';
                    echo '<span class="font-body-md text-on-surface">' . escapar($opcion['label']) . '</span></label>';
                }
                echo '</div></fieldset>';
            }
            echo '<div class="flex flex-col items-center justify-between gap-space-md rounded-lg bg-surface-container-lowest p-space-lg shadow-sm sm:flex-row"><p class="font-body-sm text-on-surface-variant">Tus respuestas se revisarán al enviar el cuestionario.</p><button class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-xl py-space-md font-label-md text-on-secondary shadow-md transition hover:bg-secondary-container">Enviar respuestas<span class="material-symbols-outlined">arrow_forward</span></button></div></form>';
            break;

        case 'ejercicio':
            ?>
            <!-- Reto práctico con consignas de clasificación o controles de respuesta alternativos. -->
            <?php
            // Exige sesión para guardar el intento y comprueba que la actividad tenga consignas.
            if (!$usuarioActual) {
                listaVacia('Inicia sesión para completar el ejercicio y guardar tu progreso.');
                echo '<a class="mt-4 inline-block rounded-full bg-action px-5 py-3 font-bold text-white" href="' .
                    urlVista('login') . '">Iniciar sesión</a>';
                break;
            }
            $actividadId = filter_var($_GET['activity'] ?? null, FILTER_VALIDATE_INT);
            $actividad = $actividadId ? (new Ejercicio($obtenerDb()))->buscar((int) $actividadId) : null;
            if (!$actividad || !$actividad['items']) {
                listaVacia('No hay ejercicios publicados con consignas configuradas.');
                break;
            }
            echo '<header class="mb-space-lg"><div class="mb-space-xs flex items-center gap-space-xs font-label-sm uppercase tracking-wide text-secondary"><span class="material-symbols-outlined text-lg">touch_app</span><span>Ejercicio interactivo</span></div>';
            echo '<h1 class="font-display text-display text-primary">' . escapar($actividad['title']) . '</h1>';
            echo '<p class="mt-space-xs max-w-3xl font-body-md text-on-surface-variant">' . escapar($actividad['description'] ?? 'Resuelve el reto y comprueba cuánto has aprendido.') . '</p></header>';
            $opcionesClasificacion = $actividad['type'] === 'classify' ? $actividad['items'][0]['choices'] : [];
            $clasificacionCompatible = $actividad['type'] === 'classify';
            foreach ($actividad['items'] as $item) {
                if ($item['choices'] !== $opcionesClasificacion) {
                    $clasificacionCompatible = false;
                    break;
                }
            }
            // La clasificación visual solo aplica cuando todas las consignas comparten opciones.
            echo '<form method="post" action="' . urlVista($paginaActual) . '" data-exercise class="space-y-space-lg">';
            echo campoCsrf() . '<input type="hidden" name="action" value="submit_exercise"><input type="hidden" name="return_page" value="ejercicio">';
            echo '<input type="hidden" name="activity_id" value="' . escapar($actividad['id']) . '">';
            if ($clasificacionCompatible) {
                echo '<section data-classify-exercise class="flex flex-col gap-space-lg">';
                echo '<div class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><div class="mb-space-md flex items-center justify-between gap-space-sm"><h2 class="flex items-center gap-space-xs font-label-md font-bold text-primary"><span class="material-symbols-outlined text-secondary">view_timeline</span>Elementos por clasificar</h2><span data-pending-counter class="rounded-full bg-surface-container px-space-sm py-1 font-label-sm text-on-surface-variant">' . count($actividad['items']) . ' elementos disponibles</span></div>';
                echo '<div data-item-bank class="flex min-h-20 flex-wrap items-center gap-space-sm rounded-DEFAULT bg-surface-container-low p-space-md">';
                foreach ($actividad['items'] as $indice => $item) {
                    echo '<button type="button" draggable="true" data-exercise-item data-item-id="' . escapar($item['id']) . '" aria-pressed="false" class="inline-flex cursor-grab items-center gap-space-sm rounded-full bg-surface-container-lowest px-space-md py-space-sm font-label-md text-primary shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:cursor-grabbing">';
                    echo '<span class="material-symbols-outlined text-outline">drag_indicator</span><span class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-container text-secondary"><span class="material-symbols-outlined">' .
                        ['keyboard', 'web', 'memory', 'terminal'][$indice % 4] . '</span></span><span>' . escapar($item['prompt']) . '</span></button>';
                    echo '<input type="hidden" data-answer-input name="answers[' . escapar($item['id']) . ']" value="">';
                }
                echo '</div></div><p data-exercise-error class="hidden rounded-DEFAULT border border-error bg-error-container p-space-md font-label-md text-on-error-container" role="alert"></p>';
                echo '<div class="grid gap-space-md md:grid-cols-2">';
                foreach ($opcionesClasificacion as $opcion) {
                    echo '<div data-answer-zone data-answer-value="' . escapar($opcion) . '" role="group" tabindex="0" aria-label="Clasificar elementos como ' . escapar($opcion) . '" class="min-h-64 rounded-lg bg-surface-container-lowest p-space-lg shadow-sm transition">';
                    echo '<div class="mb-space-md flex items-center justify-between"><div class="flex items-center gap-space-sm"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-high text-secondary"><span class="material-symbols-outlined">' .
                        (stripos($opcion, 'hardware') !== false ? 'developer_board' : (stripos($opcion, 'software') !== false ? 'terminal' : 'category')) .
                        '</span></span><div><h2 class="font-headline-sm text-primary">' . escapar($opcion) . '</h2><p class="font-body-sm text-on-surface-variant">Arrastra o selecciona aquí los elementos</p></div></div>';
                    echo '<span data-zone-counter class="rounded-full bg-surface-container px-space-sm py-1 font-label-sm text-on-surface-variant">0</span></div>';
                    echo '<div data-zone-items class="flex min-h-36 flex-col gap-space-xs rounded-DEFAULT bg-surface p-space-md"><p data-zone-empty class="m-auto text-center font-body-sm text-on-surface-variant">Suelta aquí los elementos de esta categoría</p></div></div>';
                }
                echo '</div><div class="flex flex-wrap items-center justify-between gap-space-md rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><button type="button" data-reset-exercise class="inline-flex items-center gap-space-xs rounded-full px-space-md py-space-sm font-label-md text-on-surface-variant hover:bg-surface-container"><span class="material-symbols-outlined">restart_alt</span>Reiniciar</button><button class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-xl py-space-md font-label-md text-on-secondary shadow-md hover:bg-secondary-container">Comprobar respuestas<span class="material-symbols-outlined">check_circle</span></button></div></section>';
            } else {
            foreach ($actividad['items'] as $indice => $item) {
                echo '<fieldset class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><legend class="font-headline-sm text-primary">' .
                    escapar($indice + 1) . '. ' . escapar($item['prompt']) . '</legend>';
                if ($actividad['type'] === 'order') {
                    if ($indice === 0) {
                        echo '<p class="mb-space-sm font-body-sm text-on-surface-variant">Elige cada elemento una sola vez y ordénalos para construir la secuencia.</p>';
                    }
                    echo '<label class="mt-3 block">Posición ' . escapar($indice + 1) .
                        '<select name="answers[order][]" required class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md"><option value="">Selecciona un paso</option>';
                    foreach ($actividad['items'] as $choice) {
                        echo '<option value="' . escapar($choice['id']) . '">' . escapar($choice['prompt']) . '</option>';
                    }
                    echo '</select></label>';
                } else {
                    echo '<div class="mt-space-md grid gap-space-sm sm:grid-cols-2">';
                    foreach ($item['choices'] as $choice) {
                        echo '<label class="flex cursor-pointer items-center gap-space-sm rounded-DEFAULT bg-surface-container-low p-space-md transition hover:bg-surface-container">';
                        echo '<input class="h-5 w-5 accent-secondary" type="radio" name="answers[' . escapar($item['id']) . ']" value="' . escapar($choice) . '" required>';
                        echo '<span class="font-body-md">' . escapar($choice) . '</span></label>';
                    }
                    echo '</div>';
                }
                echo '</fieldset>';
            }
            echo '<button class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-xl py-space-md font-label-md text-on-secondary shadow-md">Comprobar actividad<span class="material-symbols-outlined">check_circle</span></button>';
            }
            echo '</form>';
            break;

        case 'retroalimentacion':
        case 'resultados':
            ?>
            <!-- Resumen de puntuación y revisión individual de respuestas y explicaciones. -->
            <?php
            // Normaliza los datos del último intento para poder mostrar su resultado.
            $resultado = $_SESSION['ultimo_resultado'] ?? null;
            if (!is_array($resultado)) {
                listaVacia('Aún no hay resultados recientes. Completa un cuestionario o ejercicio para ver la retroalimentación.');
                break;
            }
            $porcentajeResultado = max(0, min(100, (int) ($resultado['percentage'] ?? 0)));
            $respuestasResultado = is_array($resultado['answers'] ?? null) ? $resultado['answers'] : [];
            $aciertosResultado = count(array_filter($respuestasResultado, static fn ($respuesta): bool => is_array($respuesta) && !empty($respuesta['correct'])));
            echo '<header class="mb-space-lg"><nav class="mb-space-sm flex flex-wrap items-center gap-space-xs font-label-md text-on-surface-variant" aria-label="Migas de pan"><a class="hover:text-secondary" href="' . urlVista('modulos') . '">Módulos</a><span class="material-symbols-outlined text-base">chevron_right</span><span class="text-secondary">Retroalimentación inmediata</span></nav>';
            echo '<div class="flex flex-wrap items-center justify-between gap-space-md"><div><p class="mb-space-xs font-label-sm uppercase tracking-wide text-secondary">Evaluación formativa</p><h1 class="font-display text-display text-primary">' .
                ($paginaActual === 'resultados' ? 'Resultados de la actividad' : 'Revisa tus respuestas') . '</h1></div><span class="inline-flex items-center gap-space-xs rounded-full bg-surface-container px-space-md py-space-xs font-label-sm text-secondary"><span class="material-symbols-outlined text-lg">verified</span>Intento completado</span></div></header>';
            echo '<div class="grid items-start gap-gutter lg:grid-cols-12"><aside class="flex flex-col gap-space-md lg:col-span-5">';
            echo '<section class="relative overflow-hidden rounded-lg bg-surface-container-lowest p-space-xl text-center shadow-sm"><div class="absolute left-0 right-0 top-0 h-1.5 bg-gradient-to-r from-secondary via-secondary-container to-tertiary"></div>';
            echo '<div class="mx-auto mb-space-md flex h-24 w-24 items-center justify-center rounded-full bg-surface-container-high text-secondary shadow-inner" style="background:conic-gradient(#0051d5 ' . $porcentajeResultado . '%, #e5eeff ' . $porcentajeResultado . '%)"><div class="flex h-[76px] w-[76px] items-center justify-center rounded-full bg-surface-container-lowest"><span class="material-symbols-outlined text-4xl">' .
                ($porcentajeResultado >= 70 ? 'check_circle' : 'auto_stories') . '</span></div></div>';
            echo '<h2 class="font-headline-lg text-primary">' . ($porcentajeResultado >= 70 ? '¡Buen trabajo!' : '¡Sigue practicando!') . '</h2><p class="mt-space-xs font-body-sm text-on-surface-variant">' .
                ($porcentajeResultado >= 70 ? 'Has avanzado con éxito. Revisa tus respuestas para afianzar lo aprendido.' : 'Cada intento es una oportunidad para aprender; revisa las explicaciones y vuelve a intentarlo.') . '</p>';
            echo '<div class="my-space-lg rounded-DEFAULT bg-surface-container-low p-space-lg"><p class="font-label-sm uppercase tracking-wide text-on-surface-variant">Puntuación obtenida</p><p class="mt-space-xs font-display text-display text-primary">' .
                escapar($resultado['score'] ?? 0) . '<span class="font-headline-lg font-normal text-on-surface-variant"> / ' . escapar($resultado['max_score'] ?? 0) . '</span></p><p class="font-label-md text-secondary">' . escapar($porcentajeResultado) . '% de aciertos</p></div>';
            echo '<div class="grid grid-cols-3 gap-space-sm"><div class="rounded-DEFAULT bg-surface-container-lowest p-space-sm"><span class="material-symbols-outlined text-on-surface-variant">quiz</span><p class="font-headline-sm font-bold text-primary">' . escapar(count($respuestasResultado)) . '</p><span class="font-label-sm text-on-surface-variant">Preguntas</span></div>';
            echo '<div class="rounded-DEFAULT bg-surface-container-lowest p-space-sm"><span class="material-symbols-outlined text-secondary">check_circle</span><p class="font-headline-sm font-bold text-primary">' . escapar($aciertosResultado) . '</p><span class="font-label-sm text-on-surface-variant">Correctas</span></div>';
            echo '<div class="rounded-DEFAULT bg-surface-container-lowest p-space-sm"><span class="material-symbols-outlined text-tertiary">bolt</span><p class="font-headline-sm font-bold text-primary">+' . escapar($resultado['score'] ?? 0) . '</p><span class="font-label-sm text-on-surface-variant">Puntos</span></div></div>';
            echo '<div class="mt-space-lg flex flex-col gap-space-sm"><a class="inline-flex items-center justify-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-md font-label-md text-on-secondary shadow-sm" href="' . urlVista('repetir_actividad') . '"><span class="material-symbols-outlined">replay</span>Ver actividades y repetir</a><a class="inline-flex items-center justify-center gap-space-xs rounded-full bg-surface-container-low px-space-lg py-space-md font-label-md text-primary" href="' . urlVista('progreso') . '"><span class="material-symbols-outlined">insights</span>Ver mi progreso</a></div></section></aside>';
            echo '<section class="flex flex-col gap-space-md lg:col-span-7"><div class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><h2 class="font-headline-md text-primary">Resumen por pregunta</h2><p class="mt-1 font-body-sm text-on-surface-variant">Consulta qué respondiste y revisa la explicación.</p></div>';
            if ($respuestasResultado) {
                foreach ($respuestasResultado as $indice => $respuesta) {
                    if (!is_array($respuesta)) {
                        continue;
                    }
                    $esCorrecta = !empty($respuesta['correct']);
                    $textoRespuesta = $respuesta['selected_answer'] ?? $respuesta['respuesta'] ?? '';
                    echo '<article class="rounded-lg bg-surface-container-lowest p-space-md shadow-sm"><div class="flex items-start gap-space-md"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full ' .
                        ($esCorrecta ? 'bg-surface-container text-secondary' : 'bg-error-container text-error') . ' font-label-md">P' . escapar($indice + 1) . '</span><div class="min-w-0 flex-grow">';
                    echo '<div class="flex flex-wrap items-start justify-between gap-space-xs"><h3 class="font-label-md font-bold text-primary">' . escapar($respuesta['prompt'] ?? ('Consigna ' . ($respuesta['item_id'] ?? $indice + 1))) . '</h3><span class="rounded-full px-space-sm py-1 font-label-sm ' .
                        ($esCorrecta ? 'bg-surface-container text-secondary' : 'bg-error-container text-on-error-container') . '">' . ($esCorrecta ? 'Correcta' : 'Revisar') . '</span></div>';
                    if ($textoRespuesta !== '') {
                        echo '<p class="mt-space-sm rounded-DEFAULT bg-surface-container-low p-space-sm font-body-sm text-on-surface-variant">Tu respuesta: <strong class="text-primary">' . escapar($textoRespuesta) . '</strong></p>';
                    }
                    if (!$esCorrecta && isset($respuesta['correct_answer'])) {
                        echo '<p class="mt-space-xs font-body-sm text-secondary">Respuesta correcta: <strong>' . escapar($respuesta['correct_answer']) . '</strong></p>';
                    }
                    if (!empty($respuesta['explanation'])) {
                        echo '<p class="mt-space-sm border-t border-outline-variant pt-space-sm font-body-sm leading-6 text-on-surface-variant"><span class="font-bold text-primary">Explicación: </span>' . escapar($respuesta['explanation']) . '</p>';
                    }
                    echo '</div></div></article>';
                }
            }
            echo '</section></div>';
            break;

        case 'perfil':
            $usuario = requerirUsuario();
            $modeloUsuario = new Usuario($obtenerDb());
            $perfil = $modeloUsuario->perfil((int) $usuario['id']);
            $estadisticas = $modeloUsuario->estadisticasPerfil((int) $usuario['id']);
            $insignias = (new Progreso($obtenerDb()))->insignias((int) $usuario['id']);
            $avatares = $modeloUsuario->avatares();
            $segundosActivos = (int) $estadisticas['active_seconds'];
            $tiempoActivo = $segundosActivos > 0 && $segundosActivos < 60
                ? '< 1 min'
                : ($segundosActivos >= 3600
                    ? intdiv($segundosActivos, 3600) . ' h ' . intdiv($segundosActivos % 3600, 60) . ' min'
                    : intdiv($segundosActivos, 60) . ' min');
            $fechaRegistro = new DateTimeImmutable($perfil['created_at']);
            $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            $fechaAlta = $fechaRegistro->format('j') . ' de ' . $meses[(int) $fechaRegistro->format('n')] . ' de ' . $fechaRegistro->format('Y');
            ?>
            <section class="profile-page">
                <header class="mb-space-lg overflow-hidden rounded-lg bg-gradient-to-br from-primary-fixed via-surface-container-low to-tertiary-fixed-dim/50 p-space-xl shadow-sm">
                    <div class="flex flex-col items-start gap-space-lg sm:flex-row sm:items-center">
                        <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-surface-container-lowest text-6xl shadow-md" aria-hidden="true"><?= escapar($perfil['avatar']) ?></div>
                        <div>
                            <p class="font-label-sm uppercase tracking-wide text-secondary">¡Este es tu espacio!</p>
                            <h1 class="mt-space-xs font-display text-headline-lg text-primary"><?= escapar($perfil['first_name']) ?> <?= escapar($perfil['last_name']) ?></h1>
                            <p class="font-body-md text-on-surface-variant">@<?= escapar($perfil['username']) ?></p>
                            <?php if ($perfil['description'] !== ''): ?>
                                <p class="mt-space-sm max-w-2xl font-body-sm text-on-surface-variant"><?= escapar($perfil['description']) ?></p>
                            <?php endif; ?>
                            <p class="mt-space-xs font-label-sm text-on-surface-variant">
                                <?= $perfil['age'] !== null ? escapar($perfil['age']) . ' años · ' : '' ?><?= escapar($perfil['state'] !== '' ? $perfil['state'] . ', ' : '') ?><?= escapar($perfil['country']) ?>
                            </p>
                        </div>
                        <a class="sm:ml-auto inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-md py-space-sm font-label-md text-on-secondary shadow-sm" href="<?= urlVista('modulos') ?>"><span class="material-symbols-outlined">rocket_launch</span>¡A aprender!</a>
                    </div>
                </header>

                <div class="grid items-start gap-gutter lg:grid-cols-12">
                    <div class="flex flex-col gap-space-lg lg:col-span-7">
                    <section class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm">
                        <h2 class="mb-space-xs font-headline-md text-primary">Personaliza tu perfil</h2>
                        <p class="mb-space-md font-body-sm text-on-surface-variant">Elige cómo te verán tus compañeros en esta aventura.</p>
                        <form method="post" action="<?= urlVista('perfil') ?>" class="space-y-space-md">
                            <?= campoCsrf() ?>
                            <input type="hidden" name="action" value="save_profile">
                            <input type="hidden" name="return_page" value="perfil">
                            <label class="block font-label-md text-primary">Nombre de usuario
                                <input class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" type="text" name="username" value="<?= escapar($perfil['username']) ?>" minlength="3" maxlength="24" pattern="[A-Za-z0-9_]{3,24}" autocomplete="username" aria-describedby="username-help" required>
                            </label>
                            <p id="username-help" class="font-body-sm text-on-surface-variant">Usa de 3 a 24 letras, números o guion bajo.</p>
                            <div class="grid gap-space-md sm:grid-cols-2">
                                <label class="block font-label-md text-primary">Nombre(s)
                                    <input class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" type="text" name="first_name" value="<?= escapar($perfil['first_name']) ?>" maxlength="60" autocomplete="given-name" required>
                                </label>
                                <label class="block font-label-md text-primary">Apellidos
                                    <input class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" type="text" name="last_name" value="<?= escapar($perfil['last_name']) ?>" maxlength="60" autocomplete="family-name" required>
                                </label>
                            </div>
                            <div class="grid gap-space-md sm:grid-cols-2">
                                <label class="block font-label-md text-primary">País
                                    <input class="mt-space-xs w-full cursor-not-allowed rounded-DEFAULT border border-outline-variant bg-surface-container-low p-space-md" type="text" value="México" readonly aria-describedby="country-help">
                                </label>
                                <label class="block font-label-md text-primary">Estado
                                    <select class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" name="state">
                                        <option value="">Elige tu estado (opcional)</option>
                                        <?php foreach ($modeloUsuario->estadosMexico() as $estado): ?>
                                            <option value="<?= escapar($estado) ?>" <?= $perfil['state'] === $estado ? 'selected' : '' ?>><?= escapar($estado) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                            <p id="country-help" class="font-body-sm text-on-surface-variant">El país de la cuenta está establecido como México y no se puede cambiar.</p>
                            <label class="block font-label-md text-primary">Edad (opcional)
                                <input class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" type="number" name="age" min="5" max="120" step="1" value="<?= escapar($perfil['age']) ?>" inputmode="numeric">
                            </label>
                            <label class="block font-label-md text-primary">Descripción (opcional)
                                <textarea class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" name="description" rows="3" maxlength="280" placeholder="¿Qué te gusta aprender?"><?= escapar($perfil['description']) ?></textarea>
                                <span class="font-body-sm text-on-surface-variant">Hasta 280 caracteres. No incluyas información privada como tu dirección o teléfono.</span>
                            </label>
                            <p class="font-body-sm text-on-surface-variant">Correo de la cuenta: <strong><?= escapar($perfil['email']) ?></strong></p>
                            <button class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-sm font-label-md text-on-secondary shadow-sm" type="submit"><span class="material-symbols-outlined">save</span>Guardar cambios</button>
                        </form>
                    </section>
                    <section class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm">
                        <h2 class="mb-space-xs font-headline-md text-primary">Elige tu avatar</h2>
                        <p class="mb-space-md font-body-sm text-on-surface-variant">Selecciona uno y guarda el cambio para actualizar tu perfil.</p>
                        <form method="post" action="<?= urlVista('perfil') ?>" class="space-y-space-md" data-avatar-picker>
                            <?= campoCsrf() ?>
                            <input type="hidden" name="action" value="save_avatar">
                            <input type="hidden" name="return_page" value="perfil">
                            <fieldset>
                                <legend class="sr-only">Avatares disponibles</legend>
                                <div class="grid grid-cols-5 gap-space-sm sm:grid-cols-8">
                                    <?php foreach ($avatares as $avatar): ?>
                                        <label class="avatar-choice cursor-pointer <?= $perfil['avatar'] === $avatar ? 'is-selected' : '' ?>">
                                            <input class="sr-only" type="radio" name="avatar" value="<?= escapar($avatar) ?>" aria-label="Avatar <?= escapar($avatar) ?>" <?= $perfil['avatar'] === $avatar ? 'checked' : '' ?> required>
                                            <span aria-hidden="true"><?= escapar($avatar) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <p class="mt-space-sm font-body-sm text-on-surface-variant" aria-live="polite">Avatar elegido: <span data-avatar-name><?= escapar($perfil['avatar']) ?></span></p>
                            </fieldset>
                            <button class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-sm font-label-md text-on-secondary shadow-sm" type="submit"><span class="material-symbols-outlined">face</span>Guardar avatar</button>
                        </form>
                    </section>
                    </div>

                    <aside class="flex flex-col gap-space-lg lg:col-span-5">
                        <section class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm">
                            <h2 class="mb-space-md font-headline-md text-primary">Tu aventura</h2>
                            <div class="grid grid-cols-2 gap-space-sm">
                                <article class="rounded-DEFAULT bg-amber-50 p-space-md">
                                    <span class="text-2xl" aria-hidden="true">🔥</span>
                                    <p class="mt-space-xs font-headline-sm font-bold text-primary"><?= escapar($estadisticas['streak_days']) ?> <?= $estadisticas['streak_days'] === 1 ? 'día' : 'días' ?></p>
                                    <p class="font-label-sm text-on-surface-variant">Racha actual</p>
                                </article>
                                <article class="rounded-DEFAULT bg-sky-50 p-space-md">
                                    <span class="text-2xl" aria-hidden="true">⏱️</span>
                                    <p class="mt-space-xs font-headline-sm font-bold text-primary"><?= escapar($tiempoActivo) ?></p>
                                    <p class="font-label-sm text-on-surface-variant">Tiempo aprendiendo</p>
                                </article>
                                <article class="rounded-DEFAULT bg-violet-50 p-space-md">
                                    <span class="text-2xl" aria-hidden="true">📅</span>
                                    <p class="mt-space-xs font-headline-sm font-bold text-primary"><?= escapar($fechaAlta) ?></p>
                                    <p class="font-label-sm text-on-surface-variant">Día de tu primera aventura</p>
                                </article>
                                <article class="rounded-DEFAULT bg-emerald-50 p-space-md">
                                    <span class="text-2xl" aria-hidden="true">✨</span>
                                    <p class="mt-space-xs font-headline-sm font-bold text-primary"><?= escapar($estadisticas['login_days']) ?></p>
                                    <p class="font-label-sm text-on-surface-variant">Días que has iniciado sesión</p>
                                </article>
                            </div>
                            <p class="mt-space-md rounded-DEFAULT bg-surface-container-low p-space-md font-body-sm text-on-surface-variant">
                                <?= $estadisticas['streak_days'] > 0
                                    ? '¡Vas genial! Vuelve mañana para mantener tu racha. Si pasa un día completo sin entrar, la racha vuelve a empezar.'
                                    : '¡Hoy es un gran día para empezar una nueva racha! Entra cada día para mantenerla.' ?>
                            </p>
                        </section>

                        <section class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm">
                            <div class="mb-space-md flex items-center justify-between gap-space-sm">
                                <h2 class="font-headline-md text-primary">Mis insignias</h2>
                                <span class="rounded-full bg-tertiary-fixed-dim/40 px-space-sm py-1 font-label-sm text-primary"><?= escapar(count($insignias)) ?> ganadas</span>
                            </div>
                            <?php if (!$insignias): ?>
                                <?php listaVacia('Completa lecciones y retos para ganar tus primeras insignias.'); ?>
                            <?php else: ?>
                                <ul class="grid gap-space-sm">
                                    <?php foreach ($insignias as $insignia): ?>
                                        <li class="flex items-center gap-space-sm rounded-DEFAULT bg-amber-50 p-space-md">
                                            <span class="text-3xl" aria-hidden="true">🏅</span>
                                            <span><strong class="block font-label-md text-primary"><?= escapar($insignia['name']) ?></strong><span class="font-body-sm text-on-surface-variant"><?= escapar($insignia['description']) ?></span></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </section>
                        <section class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm">
                            <h2 class="mb-space-xs font-headline-md text-primary">Cambiar contraseña</h2>
                            <p class="mb-space-md font-body-sm text-on-surface-variant">Confirma tu contraseña actual antes de elegir una nueva.</p>
                            <form method="post" action="<?= urlVista('perfil') ?>" class="space-y-space-md">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="action" value="change_password">
                                <input type="hidden" name="return_page" value="perfil">
                                <label class="block font-label-md text-primary">Contraseña actual
                                    <input class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" type="password" name="current_password" maxlength="4096" autocomplete="current-password" required>
                                </label>
                                <label class="block font-label-md text-primary">Nueva contraseña
                                    <input class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" type="password" name="new_password" minlength="10" maxlength="4096" autocomplete="new-password" required>
                                </label>
                                <label class="block font-label-md text-primary">Repite la nueva contraseña
                                    <input class="mt-space-xs w-full rounded-DEFAULT border border-outline-variant bg-surface-container-lowest p-space-md focus:border-secondary" type="password" name="new_password_confirmation" minlength="10" maxlength="4096" autocomplete="new-password" required>
                                </label>
                                <button class="inline-flex items-center gap-space-xs rounded-full bg-primary px-space-lg py-space-sm font-label-md text-on-primary shadow-sm" type="submit"><span class="material-symbols-outlined">key</span>Actualizar contraseña</button>
                            </form>
                        </section>
                    </aside>
                </div>
            </section>
            <?php
            break;

        case 'progreso':
            ?>
            <!-- Panel personal con indicadores, avance por módulo, insignias e historial. -->
            <?php
            // Consulta métricas agregadas y progreso de cada módulo para el usuario autenticado.
            $usuario = requerirUsuario();
            $progreso = new Progreso($obtenerDb());
            $resumen = $progreso->resumen((int) $usuario['id']);
            $insignias = $progreso->insignias((int) $usuario['id']);
            $consultaModulos = $obtenerDb()->prepare(
                'SELECT m.id,m.title,m.sort_order,COUNT(l.id) AS total_lessons,
                        SUM(CASE WHEN lp.completed_at IS NOT NULL THEN 1 ELSE 0 END) AS completed_lessons
                 FROM modules m
                 LEFT JOIN lessons l ON l.module_id=m.id AND l.is_published=1
                 LEFT JOIN lesson_progress lp ON lp.lesson_id=l.id AND lp.user_id=:user_id
                 WHERE m.is_published=1 AND m.archived_at IS NULL
                 GROUP BY m.id,m.title,m.sort_order
                 ORDER BY m.sort_order,m.id'
            );
            $consultaModulos->execute(['user_id' => (int) $usuario['id']]);
            $avanceModulos = $consultaModulos->fetchAll();
            $avanceGeneral = max(0, min(100, (int) $resumen['completion_percentage']));
            echo '<header class="mb-space-lg"><p class="mb-space-xs font-label-sm text-secondary">Panel de aprendizaje</p><h1 class="font-display text-display text-primary">Mi progreso</h1><p class="mt-space-xs max-w-3xl font-body-md text-on-surface-variant">Revisa tus lecciones completadas, los retos resueltos y tus próximos objetivos.</p></header>';
            echo '<section class="mb-space-lg rounded-lg bg-surface-container-lowest p-space-xl shadow-sm"><div class="flex flex-col justify-between gap-space-lg lg:flex-row lg:items-center"><div><p class="font-label-sm uppercase tracking-wide text-secondary">Avance general</p><p class="mt-space-xs font-headline-lg text-primary">' . escapar($avanceGeneral) . '% completado</p><p class="font-body-sm text-on-surface-variant">' . escapar($resumen['completed_lessons']) . ' de ' . escapar($resumen['total_lessons']) . ' lecciones terminadas</p></div><div class="w-full lg:max-w-xl"><div class="mb-space-xs flex justify-between font-label-sm text-on-surface-variant"><span>Tu ruta de aprendizaje</span><span>' . escapar($avanceGeneral) . '%</span></div><div class="h-4 overflow-hidden rounded-full bg-surface-container"><div class="h-full rounded-full bg-gradient-to-r from-secondary to-secondary-container transition-all" style="width:' . $avanceGeneral . '%"></div></div></div></div>';
            echo '<div class="mt-space-lg grid grid-cols-2 gap-space-sm lg:grid-cols-4">';
            foreach ([
                ['verified', (string) $resumen['completed_modules'], 'Módulos listos'],
                ['menu_book', (string) $resumen['completed_lessons'], 'Lecciones aprobadas'],
                ['bolt', (string) $resumen['points'], 'Puntos de lógica'],
                ['military_tech', (string) count($insignias), 'Insignias ganadas'],
            ] as [$icon, $value, $label]) {
                echo '<article class="flex items-center gap-space-sm rounded-DEFAULT bg-surface-container-low p-space-md"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-secondary text-on-secondary"><span class="material-symbols-outlined">' . escapar($icon) . '</span></span><div><p class="font-headline-sm font-bold text-primary">' . escapar($value) . '</p><p class="font-label-sm text-on-surface-variant">' . escapar($label) . '</p></div></article>';
            }
            echo '</div></section>';
            echo '<div class="grid items-start gap-gutter lg:grid-cols-12"><section class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm lg:col-span-7"><div class="mb-space-md flex items-center justify-between gap-space-sm"><h2 class="font-headline-md text-primary">Avance por módulo</h2><span class="rounded-full bg-surface-container px-space-sm py-1 font-label-sm text-on-surface-variant">' . escapar(count($avanceModulos)) . ' módulos</span></div><div class="flex flex-col gap-space-sm">';
            if (!$avanceModulos) {
                listaVacia('Todavía no hay módulos publicados para mostrar.');
            }
            foreach ($avanceModulos as $moduloAvance) {
                $totalLecciones = (int) $moduloAvance['total_lessons'];
                $leccionesHechas = (int) $moduloAvance['completed_lessons'];
                $porcentajeModulo = $totalLecciones > 0 ? (int) round($leccionesHechas * 100 / $totalLecciones) : 0;
                echo '<a class="block rounded-DEFAULT bg-surface-container-low p-space-md transition hover:bg-surface-container" href="' . urlVista('detalle_modulo', ['module' => $moduloAvance['id']]) . '"><div class="mb-space-xs flex flex-wrap items-center justify-between gap-space-xs"><span class="font-label-md font-bold text-primary">' . escapar($moduloAvance['sort_order']) . '. ' . escapar($moduloAvance['title']) . '</span><span class="font-label-md font-bold text-secondary">' . escapar($porcentajeModulo) . '%</span></div><div class="mb-space-xs h-2.5 overflow-hidden rounded-full bg-surface-container-high"><div class="h-full rounded-full bg-secondary" style="width:' . $porcentajeModulo . '%"></div></div><span class="font-label-sm text-on-surface-variant">' . escapar($leccionesHechas) . ' de ' . escapar($totalLecciones) . ' lecciones' . ($porcentajeModulo === 100 ? ' · Completado' : ($porcentajeModulo > 0 ? ' · En curso' : ' · Pendiente')) . '</span></a>';
            }
            echo '</div></section><aside class="flex flex-col gap-space-lg lg:col-span-5"><section class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><h2 class="mb-space-md font-headline-md text-primary">Insignias</h2>';
            if (!$insignias) {
                listaVacia('Todavía no se han obtenido insignias.');
            } else {
                echo '<ul class="grid gap-space-sm">';
                foreach ($insignias as $insignia) {
                    echo '<li class="flex items-center gap-space-sm rounded-DEFAULT bg-surface-container-low p-space-md"><span class="material-symbols-outlined text-3xl text-secondary">military_tech</span><span><strong class="block font-label-md text-primary">' . escapar($insignia['name']) . '</strong><span class="font-body-sm text-on-surface-variant">' .
                        escapar($insignia['description']) . '</span></span></li>';
                }
                echo '</ul>';
            }
            echo '</section><section class="rounded-lg bg-primary p-space-lg text-on-primary shadow-sm"><h2 class="mb-space-xs font-headline-sm font-bold">Siguiente paso</h2><p class="mb-space-md font-body-sm text-primary-fixed">Sigue tu ruta de aprendizaje y completa una actividad para ganar más puntos.</p><a class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-md py-space-sm font-label-md text-on-secondary" href="' . urlVista('modulos') . '">Explorar módulos<span class="material-symbols-outlined">arrow_forward</span></a></section></aside></div>';
            echo '<section class="mt-space-xl"><h2 class="mb-space-md font-headline-md text-primary">Historial de actividades</h2>';
            $historial = $progreso->historial((int) $usuario['id']);
            if (!$historial) {
                listaVacia('Tus actividades completadas aparecerán aquí.');
            } else {
                echo '<div class="overflow-x-auto rounded-lg bg-surface-container-lowest shadow-sm"><table class="w-full text-left"><thead class="bg-surface-container-low font-label-sm text-on-surface-variant"><tr><th class="p-space-md">Actividad</th><th class="p-space-md">Puntuación</th><th class="p-space-md">Fecha</th></tr></thead><tbody class="font-body-sm">';
                foreach ($historial as $fila) {
                    echo '<tr class="border-t border-outline-variant"><td class="p-space-md font-label-md text-primary">' . escapar($fila['activity_title']) . '</td><td class="p-space-md">' .
                        escapar($fila['score']) . '/' . escapar($fila['max_score']) . '</td><td class="p-space-md">' .
                        escapar($fila['created_at']) . '</td></tr>';
                }
                echo '</tbody></table></div>';
            }
            echo '</section>';
            break;

        case 'repetir_actividad':
            ?>
            <!-- Historial de actividades completadas con filtros y opción de repetir intentos. -->
            <?php
            // Resume intentos y puntuaciones para mostrar actividades disponibles.
            $usuario = requerirUsuario();
            $historial = (new Progreso($obtenerDb()))->actividadesRepetibles((int) $usuario['id']);
            $intentosTotales = array_sum(array_map(static fn ($fila): int => (int) $fila['attempt_count'], $historial));
            $mejorPuntuacion = $historial ? max(array_map(static fn ($fila): int => (int) $fila['best_percentage'], $historial)) : 0;
            echo '<header class="mb-space-lg"><nav class="mb-space-sm flex items-center gap-space-xs font-label-md text-on-surface-variant" aria-label="Migas de pan"><a class="hover:text-secondary" href="' . urlVista('menu') . '">Menú</a><span class="material-symbols-outlined text-base">chevron_right</span><a class="hover:text-secondary" href="' . urlVista('progreso') . '">Progreso</a><span class="material-symbols-outlined text-base">chevron_right</span><span class="text-secondary">Repetir actividad</span></nav>';
            echo '<section class="relative overflow-hidden rounded-lg bg-surface-container-lowest p-space-xl shadow-sm"><div class="relative z-10 flex flex-col justify-between gap-space-lg lg:flex-row lg:items-center"><div><p class="mb-space-xs inline-flex items-center gap-space-xs font-label-sm uppercase tracking-wide text-secondary"><span class="material-symbols-outlined">replay</span>Práctica personalizada</p><h1 class="font-display text-display text-primary">Repite y mejora</h1><p class="mt-space-xs max-w-2xl font-body-md text-on-surface-variant">Vuelve a practicar los cuestionarios y ejercicios que ya completaste; cada intento te ayuda a reforzar lo aprendido.</p></div><div class="grid grid-cols-2 gap-space-sm sm:min-w-72"><div class="rounded-DEFAULT bg-surface-container-low p-space-md"><span class="font-label-sm text-on-surface-variant">Actividades</span><p class="font-headline-md font-bold text-primary">' . escapar(count($historial)) . '</p></div><div class="rounded-DEFAULT bg-surface-container-low p-space-md"><span class="font-label-sm text-on-surface-variant">Intentos totales</span><p class="font-headline-md font-bold text-primary">' . escapar($intentosTotales) . '</p></div></div></div></section></header>';
            if (!$historial) {
                listaVacia('Completa una actividad para verla aquí y poder repetirla.');
                break;
            }
            echo '<div class="mb-space-md flex flex-col justify-between gap-space-md sm:flex-row sm:items-center"><div data-activity-filter-group class="flex flex-wrap gap-space-xs" role="group" aria-label="Filtrar actividades">';
            echo '<button type="button" data-activity-filter="all" aria-pressed="true" class="rounded-full bg-primary px-space-md py-space-sm font-label-md text-on-primary">Todas (' . count($historial) . ')</button>';
            echo '<button type="button" data-activity-filter="quiz" aria-pressed="false" class="rounded-full bg-surface-container px-space-md py-space-sm font-label-md text-on-surface-variant">Cuestionarios</button>';
            echo '<button type="button" data-activity-filter="exercise" aria-pressed="false" class="rounded-full bg-surface-container px-space-md py-space-sm font-label-md text-on-surface-variant">Ejercicios prácticos</button></div>';
            echo '<label class="relative block sm:w-72"><span class="material-symbols-outlined absolute left-space-sm top-1/2 -translate-y-1/2 text-outline">search</span><span class="sr-only">Buscar actividades</span><input data-activity-search type="search" placeholder="Buscar por tema..." class="w-full rounded-full bg-surface-container-lowest py-space-sm pl-10 pr-space-md font-body-sm shadow-sm"></label></div>';
            echo '<section class="flex flex-col gap-space-sm">';
            foreach ($historial as $fila) {
                $tipoActividad = (string) ($fila['kind'] ?? 'exercise');
                echo '<article data-activity-card data-activity-kind="' . escapar($tipoActividad) . '" data-activity-title="' . escapar($fila['activity_title']) . '" class="flex flex-col justify-between gap-space-md rounded-lg bg-surface-container-lowest p-space-lg shadow-sm transition hover:shadow-md sm:flex-row sm:items-center"><div class="flex items-center gap-space-md"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-DEFAULT ' . ($tipoActividad === 'quiz' ? 'bg-secondary-fixed text-secondary' : 'bg-primary-fixed text-primary') . '"><span class="material-symbols-outlined text-2xl">' . ($tipoActividad === 'quiz' ? 'quiz' : 'category') . '</span></span><div><span class="font-label-sm text-secondary">' . ($tipoActividad === 'quiz' ? 'Cuestionario' : 'Ejercicio práctico') . '</span><h2 class="font-headline-sm text-primary">' .
                    escapar($fila['activity_title']) . '</h2><p class="mt-1 font-body-sm text-on-surface-variant">' . escapar($fila['attempt_count']) . ' intentos · mejor puntuación ' .
                    escapar($fila['best_percentage']) . '% · último ' . escapar($fila['last_percentage']) . '% · ' . escapar($fila['last_attempted_at']) . '</p></div></div>';
                formularioVista('repeat_activity', $paginaActual, ['activity_id' => $fila['activity_id']]);
                    echo '<button class="inline-flex items-center justify-center gap-space-xs rounded-full bg-secondary px-space-lg py-space-sm font-label-md text-on-secondary shadow-sm"><span class="material-symbols-outlined">replay</span>Repetir</button></form></article>';
            }
            echo '<p data-activity-empty class="hidden rounded-lg border border-dashed border-outline-variant bg-surface-container-lowest p-space-xl text-center font-body-md text-on-surface-variant">No hay actividades que coincidan con la búsqueda.</p></section>';
            break;

        case 'diagnostico':
        case 'evaluacion_final':
            ?>
            <!-- Evaluaciones de entrada o cierre con preguntas configuradas y consentimiento. -->
            <?php
            // La evaluación posterior queda bloqueada hasta completar todos los módulos publicados.
            $tipo = $paginaActual === 'diagnostico' ? 'diagnostic' : 'post';
            if ($tipo === 'post') {
                $modulosPublicados = (new Modulo($obtenerDb()))->listarPublicados();
                $resumen = (new Progreso($obtenerDb()))->resumen((int) $usuarioActual['id']);
                if (!$modulosPublicados || (int) $resumen['completed_modules'] < count($modulosPublicados)) {
                    listaVacia('Completa todos los módulos para desbloquear la evaluación posterior.');
                    echo '<a class="mt-4 inline-block rounded-full bg-action px-5 py-3 font-bold text-white" href="' .
                        urlVista('modulos') . '">Continuar aprendiendo</a>';
                    break;
                }
            }
            $preguntas = (new Evaluacion($obtenerDb()))->listar($tipo);
            if (!$preguntas) {
                listaVacia('Esta evaluación aún no tiene preguntas publicadas.');
                break;
            }
            echo '<header class="mb-space-lg"><div class="mb-space-xs flex items-center gap-space-xs font-label-sm uppercase tracking-wide text-secondary"><span class="material-symbols-outlined">' .
                ($tipo === 'diagnostic' ? 'fact_check' : 'workspace_premium') . '</span><span>' .
                ($tipo === 'diagnostic' ? 'Punto de partida' : 'Cierre de la ruta') . '</span></div><h1 class="font-display text-display text-primary">' .
                ($tipo === 'diagnostic' ? 'Evaluación diagnóstica' : 'Evaluación final') . '</h1><p class="mt-space-xs max-w-3xl font-body-md text-on-surface-variant">' .
                ($tipo === 'diagnostic'
                    ? 'Este cuestionario permite revisar los conocimientos iniciales. Las respuestas se registran de forma anónima: no se guardan nombre, cuenta ni dirección de red junto con ellas. No escribas nombres ni datos de contacto en los comentarios.'
                    : 'Esta evaluación del prototipo revisa lo aprendido y recoge tu opinión sobre la utilidad de las actividades, la claridad de las explicaciones y la facilidad de uso. Las respuestas se asocian a tu cuenta para comparar resultados.') .
                '</p></header>';
            echo '<p class="mb-space-lg rounded-DEFAULT border border-amber-300 bg-amber-50 p-space-md font-body-sm text-amber-950"><strong class="font-label-md">Aviso para pruebas con menores:</strong> Si estas respuestas se usarán en pruebas del proyecto con una persona menor de edad, el equipo debe obtener previamente las autorizaciones formales de su madre, padre o tutor y de la institución cuando corresponda. La confirmación siguiente no sustituye esos permisos documentados.</p>';
            echo '<form method="post" action="' . urlVista($paginaActual) . '" class="space-y-space-lg">';
            echo campoCsrf() . '<input type="hidden" name="action" value="submit_evaluation"><input type="hidden" name="return_page" value="' .
                escapar($paginaActual) . '"><input type="hidden" name="evaluation_type" value="' . escapar($tipo) . '">';
            echo '<div class="rounded-DEFAULT bg-surface-container-lowest p-space-md shadow-sm"><div class="mb-space-xs flex justify-between font-label-sm text-on-surface-variant"><span>Preguntas de la evaluación</span><span>' . escapar(count($preguntas)) . ' en total</span></div><div class="h-2.5 rounded-full bg-surface-container"><div class="h-full w-full rounded-full bg-secondary"></div></div></div>';
            foreach ($preguntas as $indice => $pregunta) {
                echo '<fieldset class="rounded-lg bg-surface-container-lowest p-space-lg shadow-sm"><legend class="mb-space-md block font-headline-sm text-primary">' .
                    escapar($indice + 1) . '. ' . escapar($pregunta['prompt']) . '</legend>';
                opcionesEvaluacion($pregunta);
                echo '</fieldset>';
            }
            echo '<label class="flex items-start gap-space-sm rounded-DEFAULT bg-surface-container-low p-space-md font-body-sm text-on-surface"><input class="mt-1 h-5 w-5 accent-secondary" type="checkbox" name="guardian_consent" value="1" required>' .
                '<span>Confirmo que cuento con las autorizaciones necesarias para participar en esta evaluación del prototipo; si soy menor de edad, cuento con autorización de mi madre, padre o tutor legal.</span></label>';
            echo '<button class="inline-flex items-center gap-space-xs rounded-full bg-secondary px-space-xl py-space-md font-label-md text-on-secondary shadow-md">Enviar evaluación<span class="material-symbols-outlined">arrow_forward</span></button></form>';
            break;
    }
    ?>
    </div>
</main>
<footer class="w-full border-t border-outline-variant bg-surface-container-lowest py-space-xl">
    <div class="mx-auto flex max-w-[1440px] flex-col items-center justify-between gap-space-md px-4 font-body-sm text-on-surface-variant lg:flex-row lg:px-margin">
        <p>© 2025 Lógica en juego. Plataforma lúdico-educativa de informática y lógica computacional.</p>
        <div class="flex flex-wrap items-center justify-center gap-space-lg">
            <a class="font-label-sm transition-colors hover:text-on-surface" href="<?= urlVista('inicio') ?>">Términos y privacidad</a>
            <a class="font-label-sm transition-colors hover:text-on-surface" href="<?= urlVista('modulos') ?>">Guía didáctica</a>
            <a class="font-label-sm transition-colors hover:text-on-surface" href="mailto:soporte@logicaenjuego.local">Soporte escolar</a>
        </div>
    </div>
</footer>
<script src="assets/js/principal.js?v=20261110-2" defer></script>
<script src="assets/js/validaciones.js" defer></script>
<script src="assets/js/cuestionarios.js" defer></script>
<script src="assets/js/ejercicios.js" defer></script>
<script src="assets/js/progreso.js" defer></script>
</body>
</html>
