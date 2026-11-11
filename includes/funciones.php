<?php
declare(strict_types=1);

// Funciones compartidas para presentación segura, navegación, sesión y validación de formularios.
function escapar(string|int|float|null $valor): string
{
    // Codifica valores antes de insertarlos en HTML para prevenir interpretación como marcado.
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Construye enlaces al enrutador principal respetando la ubicación de la instalación.
function rutaPagina(string $pagina, array $parametros = []): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $directorio = str_replace('\\', '/', dirname($script));
    if (in_array(basename($directorio), ['admin', 'auth', 'pages'], true)) {
        $directorio = dirname($directorio);
    }
    $prefijo = trim($directorio, "/\\");
    $base = $prefijo === '' ? '' : '/' . $prefijo;
    return $base . '/index.php?' . http_build_query(['page' => $pagina] + $parametros);
}

// Configura e inicia una sesión usando cookies no accesibles desde JavaScript.
function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

// Crea o recupera el token aleatorio asociado a la sesión actual.
function tokenCsrf(): string
{
    iniciarSesionSegura();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Genera el campo oculto que acompaña a los formularios protegidos contra CSRF.
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escapar(tokenCsrf()) . '">';
}

// Compara el token enviado con el de la sesión y rechaza solicitudes no válidas.
function validarCsrf(): void
{
    iniciarSesionSegura();
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        throw new RuntimeException('La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.');
    }
}

// Devuelve los datos de la cuenta autenticada, si existe.
function usuarioActual(): ?array
{
    iniciarSesionSegura();
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
}

// Restringe una acción a usuarios autenticados y redirige al acceso si falta la sesión.
function requerirUsuario(): array
{
    $usuario = usuarioActual();
    if ($usuario === null) {
        mensajeFlash('error', 'Inicia sesión para continuar.');
        redirigir(rutaPagina('login'));
    }
    return $usuario;
}

// Verifica el rol administrativo después de comprobar que hay una sesión válida.
function requerirAdministrador(): array
{
    $usuario = requerirUsuario();
    if (($usuario['role'] ?? '') !== 'admin') {
        http_response_code(403);
        throw new RuntimeException('No tienes permiso para acceder a esta sección.');
    }
    return $usuario;
}

// Añade un aviso temporal a la sesión para mostrarlo después de una redirección.
function mensajeFlash(string $tipo, string $mensaje): void
{
    iniciarSesionSegura();
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

// Recupera los avisos pendientes y los elimina para que se presenten una sola vez.
function obtenerMensajesFlash(): array
{
    iniciarSesionSegura();
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($mensajes) ? $mensajes : [];
}

// Envía una redirección posterior a una operación y finaliza la solicitud.
function redirigir(string $destino): never
{
    header('Location: ' . $destino, true, 303);
    exit;
}

// Obtiene texto del formulario, comprueba su tipo y limita su longitud.
function valorPost(string $campo, int $limite = 20000): string
{
    $valor = $_POST[$campo] ?? '';
    if (!is_string($valor)) {
        throw new InvalidArgumentException('El formulario enviado no es válido.');
    }

    $valor = trim($valor);
    if (strlen($valor) > $limite) {
        throw new InvalidArgumentException('Uno de los campos supera la longitud permitida.');
    }
    return $valor;
}

// Valida un identificador entero obligatorio recibido mediante POST.
function enteroPost(string $campo, int $minimo = 1): int
{
    $entrada = $_POST[$campo] ?? null;
    $valor = is_scalar($entrada) ? filter_var($entrada, FILTER_VALIDATE_INT) : false;
    if ($valor === false || $valor < $minimo) {
        throw new InvalidArgumentException('El formulario enviado contiene un identificador no válido.');
    }
    return $valor;
}

// Valida un identificador opcional; los valores vacíos se convierten en null.
function enteroOpcionalPost(string $campo): ?int
{
    $entrada = $_POST[$campo] ?? null;
    if ($entrada === null || $entrada === '') {
        return null;
    }
    if (!is_scalar($entrada)) {
        throw new InvalidArgumentException('El formulario enviado contiene un identificador no válido.');
    }
    $valor = filter_var($entrada, FILTER_VALIDATE_INT);
    if ($valor === false || $valor < 1) {
        throw new InvalidArgumentException('El formulario enviado contiene un identificador no válido.');
    }
    return (int) $valor;
}

// Muestra los avisos temporales con texto escapado y estilos según su tipo.
function mostrarMensajesFlash(): void
{
    foreach (obtenerMensajesFlash() as $mensaje) {
        $clase = ($mensaje['tipo'] ?? '') === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-900';
        echo '<div role="status" class="mx-auto my-4 max-w-4xl rounded-xl px-5 py-3 ' . $clase . '">' .
            escapar($mensaje['mensaje'] ?? '') . '</div>';
    }
}
