<!doctype html>
<!-- Página informativa que se muestra cuando la ruta solicitada no existe. -->
<html lang="es">
<head>
    <!-- Metadatos básicos del documento y título de error. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página no encontrada | Lógica en juego</title>
    <link rel="stylesheet" href="assets/css/tema.css?v=20261110">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    <script src="assets/js/tema.js?v=20261110"></script>
</head>
<body class="error-page">
    <!-- Aviso de página no encontrada y acceso para regresar al inicio. -->
    <main class="error-card">
        <div class="error-actions">
            <button type="button" data-theme-toggle aria-pressed="false" class="theme-toggle">
                <span class="material-symbols-outlined theme-toggle-icon" aria-hidden="true">dark_mode</span>
                <span class="theme-toggle-label">Modo oscuro</span>
            </button>
        </div>
        <h1>Página no encontrada</h1>
        <p>La sección solicitada no existe.</p>
        <a class="error-link" href="index.php">Volver al inicio</a>
    </main>
</body>
</html>
