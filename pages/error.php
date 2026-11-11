<!doctype html>
<!-- Pantalla común para comunicar un problema y ofrecer una salida segura al inicio. -->
<html lang="es">
<head>
    <!-- Metadatos, título y recursos visuales de la vista. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ocurrió un problema | Lógica en juego</title>
    <link rel="stylesheet" href="assets/css/tema.css?v=20261110">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    <script src="assets/js/tema.js?v=20261110"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-8 text-slate-900">
    <div class="mx-auto mb-4 flex max-w-xl justify-end">
        <button type="button" data-theme-toggle aria-pressed="false" class="theme-toggle">
            <span class="material-symbols-outlined theme-toggle-icon" aria-hidden="true">dark_mode</span>
            <span class="theme-toggle-label">Modo oscuro</span>
        </button>
    </div>
    <!-- Mensaje del problema y navegación de regreso. -->
    <main class="mx-auto max-w-xl rounded-2xl bg-white p-8 shadow">
        <h1 class="text-2xl font-bold">Ocurrió un problema</h1>
        <p class="mt-3"><?= escapar($mensaje ?? 'No se pudo completar la solicitud.') ?></p>
        <a class="mt-5 inline-block font-bold text-blue-700" href="<?= escapar(rutaPagina('inicio')) ?>">Volver al inicio</a>
    </main>
</body>
</html>
