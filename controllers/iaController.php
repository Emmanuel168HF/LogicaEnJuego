<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$usuario = usuarioActual();
if (!$usuario) {
    http_response_code(401);
    echo json_encode(['error' => 'Debes iniciar sesión para usar LIPAL.']);
    exit;
}

$input = json_decode((string) file_get_contents('php://input'), true);
$mensaje = trim((string) ($input['mensaje'] ?? ''));
$contexto = trim((string) ($input['contexto'] ?? 'Plataforma Lógica en Juego'));

if ($mensaje === '') {
    echo json_encode(['error' => 'Escribe una duda o pregunta primero.']);
    exit;
}

// Toma la edad de la sesión; si el usuario aún no llena su perfil, usa 15 por defecto
$edad = isset($usuario['age']) && $usuario['age'] !== null ? (int) $usuario['age'] : 15;

$ia = new ServiceIA();
$respuesta = $ia->responder($mensaje, $edad, $contexto);

echo json_encode([
    'respuesta' => $respuesta,
    'perfil' => ServiceIA::obtenerPerfilPorEdad($edad)['nombre']
]);