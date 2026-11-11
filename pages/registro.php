<?php
// Pantalla de alta de cuentas de estudiante.
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
// Las sesiones existentes continúan directamente en el menú.
if (usuarioActual()) {
    redirigir(rutaPagina('menu'));
}
?>
<!doctype html>
<!-- Estructura visual del formulario de registro. -->
<html lang="es">
<head>
    <!-- Metadatos, título y recursos tipográficos y visuales. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | Lógica en juego</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/css/tema.css?v=20261110">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    <script src="assets/js/tema.js?v=20261110"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="auth-page min-h-screen bg-[#f8f9ff] font-['Plus_Jakarta_Sans'] text-slate-900">
<main class="mx-auto max-w-lg px-4 py-16">
    <div class="mb-4 flex justify-end">
        <button type="button" data-theme-toggle aria-pressed="false" class="theme-toggle">
            <span class="material-symbols-outlined theme-toggle-icon" aria-hidden="true">dark_mode</span>
            <span class="theme-toggle-label">Modo oscuro</span>
        </button>
    </div>
    <!-- Mensajes temporales de resultado de acciones anteriores. -->
    <?php mostrarMensajesFlash(); ?>
    <!-- Formulario de datos de cuenta y enlace para usuarios registrados. -->
    <section class="auth-card rounded-3xl bg-white p-8 shadow-lg">
        <a class="auth-brand inline-flex items-center gap-2 font-extrabold text-[#000f3f]" href="<?= escapar(rutaPagina('inicio')) ?>"><img src="assets/images/logo.png" alt="" class="h-12 w-12 object-contain">Lógica en juego</a>
        <h1 class="auth-title mt-8 text-3xl font-extrabold text-[#000f3f]">Crear cuenta de estudiante</h1>
        <p class="mt-2 text-slate-600">Tu cuenta guarda tu avance y tus actividades.</p>
        <form class="mt-6 space-y-4" method="post" action="<?= escapar(rutaPagina('registro')) ?>">
            <?= campoCsrf() ?>
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="return_page" value="registro">
            <label class="block font-semibold">Nombre
                <input class="mt-1 w-full rounded-xl border border-slate-300 p-3" type="text" name="name" minlength="2" maxlength="120" autocomplete="name" required>
            </label>
            <label class="block font-semibold">Correo electrónico
                <input class="mt-1 w-full rounded-xl border border-slate-300 p-3" type="email" name="email" maxlength="190" autocomplete="email" required>
            </label>
            <label class="block font-semibold">Contraseña (mínimo 10 caracteres)
                <input class="mt-1 w-full rounded-xl border border-slate-300 p-3" type="password" name="password" minlength="10" maxlength="4096" autocomplete="new-password" required>
            </label>
            <label class="block font-semibold">Confirma la contraseña
                <input class="mt-1 w-full rounded-xl border border-slate-300 p-3" type="password" name="password_confirmation" minlength="10" maxlength="4096" autocomplete="new-password" required>
            </label>
            <button class="w-full rounded-full bg-[#0051d5] px-5 py-3 font-bold text-white">Crear cuenta</button>
        </form>
        <p class="mt-5 text-sm">¿Ya tienes cuenta? <a class="font-bold text-[#0051d5]" href="<?= escapar(rutaPagina('login')) ?>">Inicia sesión</a></p>
    </section>
</main>
</body>
</html>
