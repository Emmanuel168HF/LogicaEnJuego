<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

try {
    validarCsrf();
    $usuario = requerirUsuario();
    $db = obtenerConexion();

    $modo = ($_POST['modo'] ?? 'chat') === 'consejo' ? 'consejo' : 'chat';
    $mensaje = trim((string) ($_POST['mensaje'] ?? ''));
    $temaActual = trim((string) ($_POST['contexto'] ?? 'Plataforma Lógica en juego'));

    if ($modo === 'chat' && $mensaje === '') {
        throw new InvalidArgumentException('Escribe una pregunta para tu tutor.');
    }

    // Control simple anti-spam en sesión (máximo 1 petición cada 3 segundos)
    $ahora = time();
    if (($ahora - (int) ($_SESSION['ultima_consulta_ia'] ?? 0)) < 3) {
        throw new RuntimeException('Espera un par de segundos antes de enviar otro mensaje.');
    }
    $_SESSION['ultima_consulta_ia'] = $ahora;

    $perfil = (new Usuario($db))->perfil((int) $usuario['id']);
    $edad = (int) ($perfil['age'] ?? 12);
    $contenidoPantalla = trim((string) ($_POST['contenido'] ?? ''));
    $historialRaw = json_decode((string) ($_POST['historial'] ?? '[]'), true);
    $historial = is_array($historialRaw) ? array_slice($historialRaw, -6) : [];

    $ia = new ServiceIA();
    $resultado = $ia->interactuar($modo, $edad, $mensaje, [
        'titulo' => $temaActual,
        'descripcion' => $contenidoPantalla,
        'historial' => $historial,
    ]);

    echo json_encode([
        'ok' => true,
        'respuesta' => $resultado['texto'],
        'sugerencias' => $resultado['sugerencias'] ?? [],
        'mascota' => $resultado['mascota']['nombre'],
        'avatar' => $resultado['mascota']['avatar'],
        'origen' => $resultado['origen'],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
exit;