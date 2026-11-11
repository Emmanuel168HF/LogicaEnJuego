<?php
declare(strict_types=1);

// Carga configuración local y centraliza la creación de la conexión PDO a MySQL.
function cargarVariablesEntorno(): void
{
    // Lee pares clave-valor del archivo .env sin reemplazar variables ya definidas.
    $archivo = dirname(__DIR__) . '/.env';
    if (!is_file($archivo) || !is_readable($archivo)) {
        return;
    }

    foreach (file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }

        [$nombre, $valor] = explode('=', $linea, 2);
        $nombre = trim($nombre);
        $valor = trim($valor);
        if ($nombre === '' || getenv($nombre) !== false) {
            continue;
        }

        if (strlen($valor) >= 2 && (($valor[0] === '"' && $valor[-1] === '"') || ($valor[0] === "'" && $valor[-1] === "'"))) {
            $valor = substr($valor, 1, -1);
        }

        putenv($nombre . '=' . $valor);
        $_ENV[$nombre] = $valor;
    }
}

// Devuelve una conexión reutilizable y aplica validaciones y opciones seguras al conectar.
function obtenerConexion(): PDO
{
    static $conexion = null;
    if ($conexion instanceof PDO) {
        return $conexion;
    }

    // Obtiene y valida los parámetros de conexión antes de construir el DSN.
    cargarVariablesEntorno();
    $host = getenv('DB_HOST');
    $puerto = getenv('DB_PORT') ?: '3306';
    $baseDatos = getenv('DB_NAME');
    $usuario = getenv('DB_USER');
    $contrasena = getenv('DB_PASSWORD');

    if (!$host || !$baseDatos || $usuario === false || $contrasena === false) {
        throw new RuntimeException('Falta configurar la conexión MySQL. Revisa las variables DB_HOST, DB_NAME, DB_USER y DB_PASSWORD.');
    }
    if (!preg_match('/^[a-zA-Z0-9.:-]+$/', $host) || !preg_match('/^[a-zA-Z0-9_]+$/', $baseDatos)) {
        throw new RuntimeException('DB_HOST o DB_NAME contiene caracteres no admitidos.');
    }
    if (!ctype_digit((string) $puerto) || (int) $puerto < 1 || (int) $puerto > 65535) {
        throw new RuntimeException('DB_PORT debe ser un puerto válido.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        (int) $puerto,
        $baseDatos
    );

    // Desactiva consultas preparadas emuladas y configura TLS cuando se proporciona una CA.
    try {
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];
        $ca = getenv('DB_SSL_CA');
        if ($ca !== false && $ca !== '') {
            if (!is_file($ca) || !defined('PDO::MYSQL_ATTR_SSL_CA')) {
                throw new RuntimeException('DB_SSL_CA debe apuntar a un certificado CA legible y la extensión PDO MySQL debe permitir TLS.');
            }
            $opciones[constant('PDO::MYSQL_ATTR_SSL_CA')] = $ca;
            if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $opciones[constant('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')] = true;
            }
        }
        $conexion = new PDO($dsn, $usuario, $contrasena, $opciones);
    } catch (RuntimeException $error) {
        throw $error;
    } catch (PDOException $error) {
        error_log('Error al conectar con MySQL: ' . $error->getMessage());
        throw new RuntimeException('No fue posible conectar con la base de datos. Verifica los datos de conexión y que el proveedor permita conexiones desde este servidor.');
    }

    return $conexion;
}
