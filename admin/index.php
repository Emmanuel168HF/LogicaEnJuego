<?php
declare(strict_types=1);

// Panel protegido para administrar el contenido curricular de la plataforma.
require_once dirname(__DIR__) . '/includes/bootstrap.php';
// Solo los usuarios con permisos de administración pueden gestionar contenidos.
requerirAdministrador();
// Si se accede directamente al archivo, se redirige al enrutador principal.
if (str_ends_with(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '')), '/admin/index.php')) {
    redirigir(rutaPagina('admin'));
}

// Carga los catálogos y modelos usados por los formularios y listados del panel.
$db = obtenerConexion();
$moduloModelo = new Modulo($db);
$leccionModelo = new Leccion($db);
$ejercicioModelo = new Ejercicio($db);
$preguntaModelo = new Pregunta($db);
$evaluacionModelo = new Evaluacion($db);
$modulos = $moduloModelo->listarTodos();
$lecciones = $leccionModelo->listarTodas();
$actividades = $ejercicioModelo->listarTodos();
$preguntas = $preguntaModelo->listarTodas();
$preguntasEvaluacion = $evaluacionModelo->listarTodas();
// Acepta únicamente una de las secciones de gestión disponibles.
$section = $_GET['section'] ?? 'modules';
$sections = [
    'modules' => 'Módulos',
    'lessons' => 'Lecciones',
    'questions' => 'Cuestionarios y preguntas',
    'exercises' => 'Ejercicios',
    'evaluations' => 'Evaluaciones',
];
if (!is_string($section) || !isset($sections[$section])) {
    $section = 'modules';
}
// Recupera los registros solicitados para edición y los elementos asociados a actividades.
$moduleId = filter_var($_GET['edit_module'] ?? null, FILTER_VALIDATE_INT);
$moduleEdit = $moduleId ? $moduloModelo->buscarPorId((int) $moduleId, false) : null;
$lessonId = filter_var($_GET['edit_lesson'] ?? null, FILTER_VALIDATE_INT);
$lessonEdit = null;
if ($lessonId) {
    $query = $db->prepare('SELECT id,module_id,title,content,estimated_minutes,sort_order,is_published FROM lessons WHERE id=:id');
    $query->execute(['id' => $lessonId]);
    $lessonEdit = $query->fetch() ?: null;
}
$questionId = filter_var($_GET['edit_question'] ?? null, FILTER_VALIDATE_INT);
$questionEdit = $questionId ? $preguntaModelo->buscarAdmin((int) $questionId) : null;
$activityId = filter_var($_GET['activity'] ?? null, FILTER_VALIDATE_INT);
$activityEdit = null;
$items = [];
$itemId = filter_var($_GET['edit_item'] ?? null, FILTER_VALIDATE_INT);
$itemEdit = null;
if ($activityId) {
    $query = $db->prepare('SELECT * FROM activities WHERE id=:id');
    $query->execute(['id' => $activityId]);
    $activityEdit = $query->fetch() ?: null;
    if ($activityEdit && $activityEdit['type'] !== 'quiz') {
        // Decodifica las opciones de consignas para completar los controles de edición.
        $query = $db->prepare('SELECT id,prompt,choices,correct_answer,sort_order FROM exercise_items WHERE activity_id=:id ORDER BY sort_order,id');
        $query->execute(['id' => $activityId]);
        $items = $query->fetchAll();
        foreach ($items as &$item) {
            $item['choices'] = json_decode((string) $item['choices'], true, 512, JSON_THROW_ON_ERROR);
            if ($itemId && (int) $item['id'] === (int) $itemId) {
                $itemEdit = $item;
            }
        }
        unset($item);
    }
}
$evaluationId = filter_var($_GET['edit_evaluation'] ?? null, FILTER_VALIDATE_INT);
$evaluationEdit = null;
// Localiza la pregunta evaluativa que se seleccionó para modificar.
foreach ($preguntasEvaluacion as $preguntaEvaluacion) {
    if ($evaluationId && (int) $preguntaEvaluacion['id'] === (int) $evaluationId) {
        $evaluationEdit = $preguntaEvaluacion;
        break;
    }
}

// Funciones de ayuda para generar campos, formularios protegidos y botones del panel.
$campo = static function (string $nombre, string $valor = '', string $tipo = 'text', array $atributos = []): void {
    echo '<input type="' . escapar($tipo) . '" name="' . escapar($nombre) . '" value="' . escapar($valor) .
        '" class="w-full rounded-xl border border-slate-300 p-3"';
    foreach ($atributos as $atributo => $atributoValor) {
        echo ' ' . escapar($atributo) . '="' . escapar($atributoValor) . '"';
    }
    echo '>';
};
$abRirFormulario = static function (string $action): void {
    echo '<form method="post" action="index.php?page=admin" class="space-y-4">';
    echo campoCsrf();
    echo '<input type="hidden" name="return_page" value="admin"><input type="hidden" name="action" value="' . escapar($action) . '">';
};
$boton = static function (string $texto): void {
    echo '<button class="rounded-full bg-[#0051d5] px-5 py-3 font-bold text-white hover:bg-blue-700">' . escapar($texto) . '</button>';
};
?>
<!doctype html>
<!-- Estructura común del documento y recursos visuales del área administrativa. -->
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administración de contenidos | Lógica en juego</title>
    <link rel="stylesheet" href="assets/css/tema.css?v=20261110">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    <script src="assets/js/tema.js?v=20261110"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config={theme:{extend:{
            fontFamily:{sans:['Plus Jakarta Sans','sans-serif']},
            colors:{primary:'rgb(var(--app-primary) / <alpha-value>)',secondary:'rgb(var(--app-secondary) / <alpha-value>)',background:'rgb(var(--app-background) / <alpha-value>)','surface-container-lowest':'rgb(var(--app-surface-container-lowest) / <alpha-value>)','surface-container-low':'rgb(var(--app-surface-container-low) / <alpha-value>)','surface-container':'rgb(var(--app-surface-container) / <alpha-value>)','surface-container-high':'rgb(var(--app-surface-container-high) / <alpha-value>)','outline-variant':'rgb(var(--app-outline-variant) / <alpha-value>)','on-surface':'rgb(var(--app-on-surface) / <alpha-value>)','on-surface-variant':'rgb(var(--app-on-surface-variant) / <alpha-value>)','on-primary':'rgb(var(--app-on-primary) / <alpha-value>)','on-secondary':'rgb(var(--app-on-secondary) / <alpha-value>)','error-container':'rgb(var(--app-error-container) / <alpha-value>)','on-error-container':'rgb(var(--app-on-error-container) / <alpha-value>)'},
            borderRadius:{DEFAULT:'1rem',lg:'2rem',xl:'3rem'}
        }}};
    </script>
</head>
<body class="min-h-screen bg-background font-sans text-on-surface antialiased">
<!-- Cabecera del panel con accesos al sitio y cierre de sesión. -->
<header class="border-b border-outline-variant bg-surface-container-lowest shadow-sm">
    <div class="mx-auto flex h-16 max-w-[1440px] flex-wrap items-center justify-between gap-4 px-4 lg:px-10">
        <a class="flex items-center gap-3 text-xl font-extrabold text-primary" href="index.php?page=admin"><img src="assets/images/logo.png" alt="" class="h-11 w-11 object-contain">Lógica en juego <span class="hidden font-label-sm font-medium text-secondary sm:inline">Administración</span></a>
        <nav class="flex flex-wrap items-center gap-2">
            <button type="button" data-theme-toggle aria-pressed="false" class="theme-toggle">
                <span class="material-symbols-outlined theme-toggle-icon" aria-hidden="true">dark_mode</span>
                <span class="theme-toggle-label">Modo oscuro</span>
            </button>
            <a class="rounded-full px-4 py-2 font-semibold text-on-surface-variant transition hover:bg-surface-container-low" href="index.php?page=menu">Ir al sitio</a>
            <form method="post" action="index.php?page=admin">
                <?= campoCsrf() ?><input type="hidden" name="action" value="logout"><input type="hidden" name="return_page" value="inicio">
                <button class="rounded-full bg-primary px-4 py-2 font-semibold text-on-primary">Cerrar sesión</button>
            </form>
        </nav>
    </div>
</header>
<!-- Contexto del área de trabajo y de los contenidos administrados. -->
<div class="border-b border-outline-variant bg-surface-container-low">
    <div class="mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-2 px-4 py-2 font-label-sm text-on-surface-variant lg:px-10">
        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-secondary"></span><strong class="text-primary">Panel curricular</strong><span>/</span><span>Gestión pedagógica y didáctica</span></div>
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-base">sync</span><span>Contenidos de la plataforma</span></div>
    </div>
</div>
<!-- Espacio principal con mensajes, navegación por secciones y formularios de gestión. -->
<main class="mx-auto max-w-[1440px] px-4 py-8 lg:px-10">
    <?php mostrarMensajesFlash(); ?>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div><p class="mb-1 font-label-sm uppercase tracking-wide text-secondary">Espacio de trabajo</p><h1 class="text-3xl font-extrabold text-primary">Administración de contenidos</h1></div>
        <span class="rounded-full bg-surface-container px-4 py-2 font-label-sm text-on-surface-variant">Gestión de módulos y evaluaciones</span>
    </div>
    <div class="grid items-start gap-6 lg:grid-cols-12">
    <aside class="lg:col-span-3">
        <!-- Navegación entre las cinco áreas de administración de contenidos. -->
        <nav class="flex flex-row gap-2 overflow-x-auto rounded-lg bg-surface-container-lowest p-3 shadow-sm lg:flex-col" aria-label="Secciones de administración">
            <?php foreach ($sections as $key => $label): ?>
                <a class="flex shrink-0 items-center gap-2 rounded-DEFAULT px-4 py-3 font-semibold transition-colors <?= $key === $section ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-container-low' ?>"
                   href="index.php?page=admin&amp;section=<?= escapar($key) ?>"><span class="material-symbols-outlined"><?= escapar(['modules' => 'widgets', 'lessons' => 'menu_book', 'questions' => 'quiz', 'exercises' => 'extension', 'evaluations' => 'fact_check'][$key]) ?></span><?= escapar($label) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <section class="min-w-0 lg:col-span-9">

    <?php if ($section === 'modules'): ?>
        <!-- Administración de módulos: formulario de alta/edición y listado existente. -->
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)]">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold"><?= $moduleEdit ? 'Editar módulo' : 'Crear módulo' ?></h2>
                <?php $abRirFormulario('save_module'); ?>
                    <?php if ($moduleEdit): ?><input type="hidden" name="id" value="<?= escapar($moduleEdit['id']) ?>"><?php endif; ?>
                    <label class="block font-semibold">Nombre<?php $campo('title', $moduleEdit['title'] ?? '', 'text', ['maxlength' => '180', 'required' => 'required']); ?></label>
                    <label class="block font-semibold">Identificador URL (slug)<?php $campo('slug', $moduleEdit['slug'] ?? '', 'text', ['maxlength' => '180', 'pattern' => '[a-z0-9]+(?:-[a-z0-9]+)*', 'required' => 'required']); ?></label>
                    <label class="block font-semibold">Descripción<textarea class="w-full rounded-xl border border-slate-300 p-3" name="description" rows="3"><?= escapar($moduleEdit['description'] ?? '') ?></textarea></label>
                    <label class="block font-semibold">Objetivos<textarea class="w-full rounded-xl border border-slate-300 p-3" name="objectives" rows="3"><?= escapar($moduleEdit['objectives'] ?? '') ?></textarea></label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block font-semibold">Dificultad<select class="w-full rounded-xl border border-slate-300 p-3" name="difficulty"><option value="inicial">Inicial</option><option value="intermedio" <?= ($moduleEdit['difficulty'] ?? '') === 'intermedio' ? 'selected' : '' ?>>Intermedio</option><option value="avanzado" <?= ($moduleEdit['difficulty'] ?? '') === 'avanzado' ? 'selected' : '' ?>>Avanzado</option></select></label>
                        <label class="block font-semibold">Duración en minutos<?php $campo('estimated_minutes', (string) ($moduleEdit['estimated_minutes'] ?? '15'), 'number', ['min' => '1', 'max' => '600']); ?></label>
                    </div>
                    <label class="block font-semibold">Orden<?php $campo('sort_order', (string) ($moduleEdit['sort_order'] ?? '0'), 'number', ['min' => '0']); ?></label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" <?= !empty($moduleEdit['is_published']) ? 'checked' : '' ?>> Publicar en el catálogo</label>
                    <?php $boton($moduleEdit ? 'Guardar módulo' : 'Crear módulo'); ?>
                </form>
            </section>
            <section class="space-y-3">
                <h2 class="text-xl font-bold">Módulos existentes</h2>
                <?php foreach ($modulos as $modulo): ?>
                    <article class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <div><strong><?= escapar($modulo['title']) ?></strong><p class="text-sm text-slate-600"><?= !empty($modulo['is_published']) ? 'Publicado' : 'Borrador' ?> · <?= escapar($modulo['slug']) ?></p></div>
                        <div class="flex gap-2">
                            <a class="rounded-full border px-3 py-2" href="index.php?page=admin&amp;section=modules&amp;edit_module=<?= escapar($modulo['id']) ?>">Editar</a>
                            <?php $abRirFormulario('archive_module'); ?><input type="hidden" name="id" value="<?= escapar($modulo['id']) ?>"><button class="rounded-full border border-red-200 px-3 py-2 text-red-700" onclick="return confirm('¿Archivar este módulo?')">Archivar</button></form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </div>
    <?php elseif ($section === 'lessons'): ?>
        <!-- Administración de lecciones: contenido, publicación y asociación con módulos. -->
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.3fr)]">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold"><?= $lessonEdit ? 'Editar lección' : 'Crear lección' ?></h2>
                <?php if (!$modulos): ?><p>Primero debes crear un módulo.</p><?php else: ?>
                <?php $abRirFormulario('save_lesson'); ?>
                    <?php if ($lessonEdit): ?><input type="hidden" name="id" value="<?= escapar($lessonEdit['id']) ?>"><?php endif; ?>
                    <label class="block font-semibold">Módulo<select class="w-full rounded-xl border border-slate-300 p-3" name="module_id" required><?php foreach ($modulos as $modulo): ?><option value="<?= escapar($modulo['id']) ?>" <?= (int) ($lessonEdit['module_id'] ?? 0) === (int) $modulo['id'] ? 'selected' : '' ?>><?= escapar($modulo['title']) ?></option><?php endforeach; ?></select></label>
                    <label class="block font-semibold">Título<?php $campo('title', $lessonEdit['title'] ?? '', 'text', ['required' => 'required', 'maxlength' => '180']); ?></label>
                    <label class="block font-semibold">Contenido<textarea class="w-full rounded-xl border border-slate-300 p-3" name="content" rows="10" required><?= escapar($lessonEdit['content'] ?? '') ?></textarea></label>
                    <label class="block font-semibold">Duración (minutos)<?php $campo('estimated_minutes', (string) ($lessonEdit['estimated_minutes'] ?? '10'), 'number', ['min' => '1']); ?></label>
                    <label class="block font-semibold">Orden<?php $campo('sort_order', (string) ($lessonEdit['sort_order'] ?? '0'), 'number', ['min' => '0']); ?></label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" <?= !empty($lessonEdit['is_published']) ? 'checked' : '' ?>> Publicar lección</label>
                    <?php $boton($lessonEdit ? 'Guardar lección' : 'Crear lección'); ?>
                </form><?php endif; ?>
            </section>
            <section class="space-y-3"><h2 class="text-xl font-bold">Lecciones</h2>
                <?php foreach ($lecciones as $leccion): ?>
                    <article class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <div><strong><?= escapar($leccion['title']) ?></strong><p class="text-sm text-slate-600"><?= escapar($leccion['module_title']) ?> · <?= !empty($leccion['is_published']) ? 'Publicada' : 'Borrador' ?></p></div>
                        <div class="flex gap-2"><a class="rounded-full border px-3 py-2" href="index.php?page=admin&amp;section=lessons&amp;edit_lesson=<?= escapar($leccion['id']) ?>">Editar</a>
                            <?php $abRirFormulario('delete_lesson'); ?><input type="hidden" name="id" value="<?= escapar($leccion['id']) ?>"><button class="rounded-full border border-red-200 px-3 py-2 text-red-700" onclick="return confirm('¿Eliminar esta lección?')">Eliminar</button></form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </div>
    <?php elseif ($section === 'questions'): ?>
        <?php
        // Separa los cuestionarios de otras actividades para filtrar sus preguntas.
        $quizActivities = array_values(array_filter($actividades, static fn ($activity): bool => $activity['type'] === 'quiz'));
        $quizId = filter_var($_GET['activity'] ?? null, FILTER_VALIDATE_INT);
        $quizEdit = null;
        if ($quizId) {
            $query = $db->prepare('SELECT * FROM activities WHERE id=:id AND type=\'quiz\'');
            $query->execute(['id' => $quizId]);
            $quizEdit = $query->fetch() ?: null;
        }
        ?>
        <!-- Administración de cuestionarios y sus preguntas de opción múltiple. -->
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold"><?= $quizEdit ? 'Editar cuestionario' : 'Crear cuestionario' ?></h2>
                <?php if (!$modulos): ?><p>Primero crea un módulo.</p><?php else: ?>
                <?php $abRirFormulario('save_quiz'); ?>
                    <?php if ($quizEdit): ?><input type="hidden" name="id" value="<?= escapar($quizEdit['id']) ?>"><?php endif; ?>
                    <label class="block font-semibold">Módulo<select class="w-full rounded-xl border border-slate-300 p-3" name="module_id" required><?php foreach ($modulos as $modulo): ?><option value="<?= escapar($modulo['id']) ?>" <?= (int) ($quizEdit['module_id'] ?? 0) === (int) $modulo['id'] ? 'selected' : '' ?>><?= escapar($modulo['title']) ?></option><?php endforeach; ?></select></label>
                    <label class="block font-semibold">Título<?php $campo('title', $quizEdit['title'] ?? '', 'text', ['required' => 'required']); ?></label>
                    <label class="block font-semibold">Descripción<textarea class="w-full rounded-xl border p-3" name="description"><?= escapar($quizEdit['description'] ?? '') ?></textarea></label>
                    <label class="block font-semibold">Máximo de intentos (vacío = sin límite)<?php $campo('max_attempts', (string) ($quizEdit['max_attempts'] ?? ''), 'number', ['min' => '1']); ?></label>
                    <label class="block font-semibold">Puntaje de aprobación (%)<?php $campo('passing_score', (string) ($quizEdit['passing_score'] ?? '70'), 'number', ['min' => '0', 'max' => '100']); ?></label>
                    <?php $boton($quizEdit ? 'Guardar cuestionario' : 'Crear cuestionario'); ?>
                </form><?php endif; ?>
            </section>
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold"><?= $questionEdit ? 'Editar pregunta' : 'Crear pregunta' ?></h2>
                <?php if (!$quizActivities): ?><p>Crea un cuestionario antes de agregar preguntas.</p><?php else: ?>
                <?php $abRirFormulario('save_question'); ?>
                    <?php if ($questionEdit): ?><input type="hidden" name="id" value="<?= escapar($questionEdit['id']) ?>"><?php endif; ?>
                    <label class="block font-semibold">Cuestionario<select class="w-full rounded-xl border border-slate-300 p-3" name="activity_id" required><?php foreach ($quizActivities as $activity): ?><option value="<?= escapar($activity['id']) ?>" <?= (int) ($questionEdit['activity_id'] ?? 0) === (int) $activity['id'] ? 'selected' : '' ?>><?= escapar($activity['title']) ?></option><?php endforeach; ?></select></label>
                    <label class="block font-semibold">Pregunta<textarea class="w-full rounded-xl border p-3" name="prompt" required><?= escapar($questionEdit['prompt'] ?? '') ?></textarea></label>
                    <label class="block font-semibold">Explicación<textarea class="w-full rounded-xl border p-3" name="explanation"><?= escapar($questionEdit['explanation'] ?? '') ?></textarea></label>
                    <?php for ($index = 0; $index < 8; $index++): $option = $questionEdit['options'][$index] ?? ''; ?>
                        <label class="block font-semibold">Opción <?= escapar($index + 1) ?><input class="mt-1 w-full rounded-xl border p-3" name="options[]" value="<?= escapar($option) ?>" <?= $index < 2 ? 'required' : '' ?>></label>
                    <?php endfor; ?>
                    <label class="block font-semibold">Opción correcta<select class="w-full rounded-xl border p-3" name="correct_option"><?php for ($i = 1; $i <= 8; $i++): ?><option value="<?= $i ?>" <?= (int) ($questionEdit['correct_option'] ?? 1) === $i ? 'selected' : '' ?>>Opción <?= $i ?></option><?php endfor; ?></select></label>
                    <label class="block font-semibold">Orden<?php $campo('sort_order', (string) ($questionEdit['sort_order'] ?? '0'), 'number', ['min' => '0']); ?></label>
                    <?php $boton($questionEdit ? 'Guardar pregunta' : 'Crear pregunta'); ?>
                </form><?php endif; ?>
            </section>
        </div>
        <section class="mt-8 space-y-3"><h2 class="text-xl font-bold">Cuestionarios y preguntas</h2>
            <?php foreach ($quizActivities as $activity): ?>
                <article class="rounded-xl bg-white p-4 shadow-sm"><div class="flex flex-wrap items-center justify-between gap-3"><div><strong><?= escapar($activity['title']) ?></strong><p><?= escapar($activity['module_title']) ?> · <?= !empty($activity['is_published']) ? 'Publicado' : 'Borrador' ?></p></div>
                    <div class="flex gap-2"><a class="rounded-full border px-3 py-2" href="index.php?page=admin&amp;section=questions&amp;activity=<?= escapar($activity['id']) ?>">Editar</a>
                    <?php $abRirFormulario('set_activity_publication'); ?><input type="hidden" name="id" value="<?= escapar($activity['id']) ?>"><button name="publish" value="<?= !empty($activity['is_published']) ? '0' : '1' ?>" class="rounded-full border px-3 py-2"><?= !empty($activity['is_published']) ? 'Despublicar' : 'Publicar' ?></button></form>
                    <?php $abRirFormulario('delete_activity'); ?><input type="hidden" name="id" value="<?= escapar($activity['id']) ?>"><input type="hidden" name="type" value="quiz"><button class="rounded-full border border-red-200 px-3 py-2 text-red-700" onclick="return confirm('¿Eliminar este cuestionario y todos sus intentos?')">Eliminar</button></form></div></div>
                    <?php foreach ($preguntas as $pregunta): if ((int) $pregunta['activity_id'] !== (int) $activity['id']) continue; ?>
                        <div class="mt-3 flex items-center justify-between border-t pt-3"><span><?= escapar($pregunta['prompt']) ?></span><div class="flex gap-2"><a class="rounded-full border px-3 py-1" href="index.php?page=admin&amp;section=questions&amp;edit_question=<?= escapar($pregunta['id']) ?>">Editar</a><?php $abRirFormulario('delete_question'); ?><input type="hidden" name="id" value="<?= escapar($pregunta['id']) ?>"><button class="rounded-full px-3 py-1 text-red-700">Eliminar</button></form></div></div>
                    <?php endforeach; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php elseif ($section === 'exercises'): ?>
        <!-- Administración de ejercicios, consignas, opciones y publicación. -->
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold"><?= $activityEdit ? 'Editar ejercicio' : 'Crear ejercicio' ?></h2>
                <?php if (!$modulos): ?><p>Primero crea un módulo.</p><?php else: ?>
                <?php $abRirFormulario('save_exercise'); ?>
                    <?php if ($activityEdit): ?><input type="hidden" name="id" value="<?= escapar($activityEdit['id']) ?>"><?php endif; ?>
                    <label class="block font-semibold">Módulo<select class="w-full rounded-xl border p-3" name="module_id" required><?php foreach ($modulos as $modulo): ?><option value="<?= escapar($modulo['id']) ?>" <?= (int) ($activityEdit['module_id'] ?? 0) === (int) $modulo['id'] ? 'selected' : '' ?>><?= escapar($modulo['title']) ?></option><?php endforeach; ?></select></label>
                    <label class="block font-semibold">Tipo<select class="w-full rounded-xl border p-3" name="type"><option value="identify">Identificar</option><option value="classify" <?= ($activityEdit['type'] ?? '') === 'classify' ? 'selected' : '' ?>>Clasificar</option><option value="order" <?= ($activityEdit['type'] ?? '') === 'order' ? 'selected' : '' ?>>Ordenar</option></select></label>
                    <label class="block font-semibold">Título<?php $campo('title', $activityEdit['title'] ?? '', 'text', ['required' => 'required']); ?></label>
                    <label class="block font-semibold">Descripción<textarea class="w-full rounded-xl border p-3" name="description"><?= escapar($activityEdit['description'] ?? '') ?></textarea></label>
                    <label class="block font-semibold">Máximo de intentos (vacío = sin límite)<?php $campo('max_attempts', (string) ($activityEdit['max_attempts'] ?? ''), 'number', ['min' => '1']); ?></label>
                    <label class="block font-semibold">Puntaje para aprobar (%)<?php $campo('passing_score', (string) ($activityEdit['passing_score'] ?? '70'), 'number', ['min' => '0', 'max' => '100']); ?></label>
                    <?php $boton($activityEdit ? 'Guardar ejercicio' : 'Crear ejercicio'); ?>
                </form><?php endif; ?>
            </section>
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold"><?= $activityEdit ? 'Agregar o editar consigna' : 'Consignas del ejercicio' ?></h2>
                <?php if (!$activityEdit || $activityEdit['type'] === 'quiz'): ?><p>Guarda primero un ejercicio para administrar sus consignas.</p><?php else: ?>
                <?php $abRirFormulario('save_exercise_item'); ?>
                    <input type="hidden" name="activity_id" value="<?= escapar($activityEdit['id']) ?>">
                    <?php if ($itemEdit): ?><input type="hidden" name="id" value="<?= escapar($itemEdit['id']) ?>"><?php endif; ?>
                    <label class="block font-semibold">Consigna<textarea class="w-full rounded-xl border p-3" name="prompt" required><?= escapar($itemEdit['prompt'] ?? '') ?></textarea></label>
                    <label class="block font-semibold">Opciones (una por línea)<textarea class="w-full rounded-xl border p-3" name="choices_text" rows="4"><?= escapar(implode("\n", $itemEdit['choices'] ?? [])) ?></textarea></label>
                    <label class="block font-semibold">Respuesta correcta<?php $campo('correct_answer', $itemEdit['correct_answer'] ?? '', 'text', (($activityEdit['type'] ?? '') === 'order') ? [] : ['required' => 'required']); ?></label>
                    <label class="block font-semibold">Orden<?php $campo('sort_order', (string) ($itemEdit['sort_order'] ?? count($items)), 'number', ['min' => '0']); ?></label>
                    <?php $boton($itemEdit ? 'Guardar consigna' : 'Agregar consigna'); ?>
                </form>
                <?php foreach ($items as $item): ?>
                    <article class="mt-3 rounded-xl border p-3"><div class="flex items-center justify-between"><strong><?= escapar($item['prompt']) ?></strong><a class="rounded-full border px-3 py-1" href="index.php?page=admin&amp;section=exercises&amp;activity=<?= escapar($activityEdit['id']) ?>&amp;edit_item=<?= escapar($item['id']) ?>">Editar</a></div><p class="text-sm">Respuesta definida por el administrador</p>
                        <?php $abRirFormulario('delete_exercise_item'); ?><input type="hidden" name="id" value="<?= escapar($item['id']) ?>"><input type="hidden" name="activity_id" value="<?= escapar($activityEdit['id']) ?>"><button class="text-sm text-red-700">Eliminar consigna</button></form>
                    </article>
                <?php endforeach; endif; ?>
            </section>
        </div>
        <section class="mt-8 space-y-3"><h2 class="text-xl font-bold">Ejercicios</h2>
            <?php foreach ($actividades as $activity): if ($activity['type'] === 'quiz') continue; ?>
                <article class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4"><div><strong><?= escapar($activity['title']) ?></strong><p><?= escapar($activity['module_title']) ?> · <?= escapar($activity['type']) ?> · <?= !empty($activity['is_published']) ? 'Publicado' : 'Borrador' ?></p></div><div class="flex gap-2">
                    <a class="rounded-full border px-3 py-2" href="index.php?page=admin&amp;section=exercises&amp;activity=<?= escapar($activity['id']) ?>">Editar / consignas</a>
                    <?php $abRirFormulario('set_activity_publication'); ?><input type="hidden" name="id" value="<?= escapar($activity['id']) ?>"><button name="publish" value="<?= !empty($activity['is_published']) ? '0' : '1' ?>" class="rounded-full border px-3 py-2"><?= !empty($activity['is_published']) ? 'Despublicar' : 'Publicar' ?></button></form>
                    <?php $abRirFormulario('delete_activity'); ?><input type="hidden" name="id" value="<?= escapar($activity['id']) ?>"><input type="hidden" name="type" value="<?= escapar($activity['type']) ?>"><button class="rounded-full border border-red-200 px-3 py-2 text-red-700" onclick="return confirm('¿Eliminar este ejercicio y sus intentos?')">Eliminar</button></form>
                </div></article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <!-- Administración de evaluaciones y consulta de resultados agregados. -->
        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-xl font-bold"><?= $evaluationEdit ? 'Editar pregunta de evaluación' : 'Crear pregunta de evaluación' ?></h2>
                <?php $abRirFormulario('save_evaluation_question'); ?>
                    <?php if ($evaluationEdit): ?><input type="hidden" name="id" value="<?= escapar($evaluationEdit['id']) ?>"><?php endif; ?>
                    <label class="block font-semibold">Evaluación<select class="w-full rounded-xl border p-3" name="evaluation_type"><option value="diagnostic" <?= ($evaluationEdit['evaluation_type'] ?? '') === 'diagnostic' ? 'selected' : '' ?>>Diagnóstico</option><option value="post" <?= ($evaluationEdit['evaluation_type'] ?? '') === 'post' ? 'selected' : '' ?>>Posterior</option></select></label>
                    <label class="block font-semibold">Clave única<?php $campo('field_key', $evaluationEdit['field_key'] ?? '', 'text', ['required' => 'required', 'pattern' => '[a-z][a-z0-9_]{1,79}']); ?></label>
                    <label class="block font-semibold">Pregunta<textarea class="w-full rounded-xl border p-3" name="prompt" required><?= escapar($evaluationEdit['prompt'] ?? '') ?></textarea></label>
                    <label class="block font-semibold">Tipo de respuesta<select class="w-full rounded-xl border p-3" name="input_type"><option value="choice">Opción múltiple</option><option value="rating" <?= ($evaluationEdit['input_type'] ?? '') === 'rating' ? 'selected' : '' ?>>Escala</option><option value="text" <?= ($evaluationEdit['input_type'] ?? '') === 'text' ? 'selected' : '' ?>>Texto libre</option></select></label>
                    <label class="block font-semibold">Opciones (una por línea: valor|etiqueta)<textarea class="w-full rounded-xl border p-3" name="choices[]" rows="5"><?php foreach (($evaluationEdit['choices'] ?? []) as $choice): echo escapar((is_array($choice) ? ($choice['value'] . '|' . $choice['label']) : $choice) . "\n"); endforeach; ?></textarea></label>
                    <label class="block font-semibold">Respuesta correcta (valor)<?php $campo('correct_answer', $evaluationEdit['correct_answer'] ?? ''); ?></label>
                    <label class="block font-semibold">Orden<?php $campo('sort_order', (string) ($evaluationEdit['sort_order'] ?? '0'), 'number', ['min' => '0']); ?></label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_scored" value="1" <?= !empty($evaluationEdit['is_scored']) ? 'checked' : '' ?>> Puntúa para medir conocimientos</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_required" value="1" <?= !isset($evaluationEdit['is_required']) || !empty($evaluationEdit['is_required']) ? 'checked' : '' ?>> Obligatoria</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" <?= !isset($evaluationEdit['is_published']) || !empty($evaluationEdit['is_published']) ? 'checked' : '' ?>> Publicar</label>
                    <?php $boton($evaluationEdit ? 'Guardar pregunta' : 'Crear pregunta'); ?>
                </form>
            </section>
            <section class="space-y-3"><h2 class="text-xl font-bold">Preguntas de evaluación</h2>
                <?php foreach ($preguntasEvaluacion as $pregunta): ?>
                    <article class="rounded-xl bg-white p-4 shadow-sm"><div class="flex justify-between gap-3"><div><strong><?= escapar($pregunta['prompt']) ?></strong><p><?= $pregunta['evaluation_type'] === 'diagnostic' ? 'Diagnóstico anónimo' : 'Evaluación posterior' ?> · <?= !empty($pregunta['is_published']) ? 'Publicada' : 'Borrador' ?></p></div>
                        <div class="flex gap-2"><a class="rounded-full border px-3 py-2" href="index.php?page=admin&amp;section=evaluations&amp;edit_evaluation=<?= escapar($pregunta['id']) ?>">Editar</a><?php $abRirFormulario('delete_evaluation_question'); ?><input type="hidden" name="id" value="<?= escapar($pregunta['id']) ?>"><button class="text-red-700">Eliminar</button></form></div>
                    </div></article>
                <?php endforeach; ?>
                <!-- Comparación estadística agregada entre evaluación inicial y posterior. -->
                <h2 class="mt-8 text-xl font-bold">Comparación agregada de evaluaciones</h2>
                <?php foreach ((new Progreso($db))->compararEvaluaciones() as $resultado): ?>
                    <p class="rounded-xl bg-white p-4"><?= $resultado['evaluation_type'] === 'diagnostic' ? 'Diagnósticos' : 'Evaluaciones posteriores' ?>: <?= escapar($resultado['submissions']) ?> respuestas · promedio de conocimientos <?= escapar($resultado['average_score'] ?? 'sin datos') ?>%</p>
                <?php endforeach; ?>
            </section>
        </div>
    <?php endif; ?>
    </section>
    </div>
</main>
</body>
</html>
