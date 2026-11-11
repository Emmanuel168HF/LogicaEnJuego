<?php
declare(strict_types=1);

// Herramienta de consola para renovar la contraseña de una cuenta administradora activa.
// Limita la ejecución a CLI para impedir cambios de credenciales mediante solicitudes web.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/conexion.php';

try {
    // Lee los datos de manera interactiva y valida el correo y la longitud de la contraseña.
    $leer = static function (string $prompt): string {
        fwrite(STDOUT, $prompt);
        $entrada = fgets(STDIN);
        if ($entrada === false) {
            throw new RuntimeException('No se pudo leer la entrada.');
        }
        return rtrim($entrada, "\r\n");
    };

    $correo = strtolower(trim($leer('Correo del administrador: ')));
    $contrasena = $leer('Nueva contraseña (mínimo 12 caracteres): ');
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($contrasena) < 12 || strlen($contrasena) > 4096) {
        throw new InvalidArgumentException('Correo o contraseña no válidos.');
    }

    // Actualiza únicamente la cuenta administradora activa que coincide con el correo.
    $db = obtenerConexion();
    $consulta = $db->prepare(
        "UPDATE users SET password_hash = :password_hash WHERE email = :email AND role = 'admin' AND is_active = 1"
    );
    $consulta->execute([
        'password_hash' => password_hash($contrasena, PASSWORD_DEFAULT),
        'email' => $correo,
    ]);

    if ($consulta->rowCount() !== 1) {
        throw new RuntimeException('No se encontró un administrador activo con ese correo.');
    }
    fwrite(STDOUT, "Contraseña de administrador actualizada.\n");
} catch (Throwable $error) {
    // Informa del fallo por STDERR y devuelve un código distinto de cero a la consola.
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
