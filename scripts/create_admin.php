<?php
declare(strict_types=1);

// Herramienta de consola para crear la primera cuenta administradora del sistema.
// No permite su ejecución desde el navegador ni crear administradores adicionales.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/conexion.php';

try {
    // Comprueba que aún no haya cuentas de administrador antes de solicitar los datos iniciales.
    $db = obtenerConexion();
    $hayAdmin = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    if ($hayAdmin > 0) {
        throw new RuntimeException('Ya existe una cuenta administradora. El alta inicial solo se permite una vez.');
    }
    // Recoge los datos, aplica validaciones básicas y almacena una contraseña cifrada.
    $leer = static function (string $prompt): string {
        fwrite(STDOUT, $prompt);
        return rtrim((string) fgets(STDIN), "\r\n");
    };
    $nombre = trim($leer('Nombre del administrador: '));
    $correo = strtolower(trim($leer('Correo electrónico: ')));
    $contrasena = $leer('Contraseña (mínimo 12 caracteres): ');
    if (strlen($nombre) < 2 || strlen($nombre) > 120 || !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($contrasena) < 12 || strlen($contrasena) > 4096) {
        throw new InvalidArgumentException('Nombre, correo o contraseña no válidos.');
    }

    $consulta = $db->prepare(
        'INSERT INTO users (name,email,password_hash,role,is_active)
         VALUES (:name,:email,:password_hash,\'admin\',1)'
    );
    $consulta->execute([
        'name' => $nombre,
        'email' => $correo,
        'password_hash' => password_hash($contrasena, PASSWORD_DEFAULT),
    ]);
    fwrite(STDOUT, "Cuenta administradora creada.\n");
} catch (Throwable $error) {
    // Informa del motivo del fallo y señala el error al proceso que invocó el script.
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
