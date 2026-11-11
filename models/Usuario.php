<?php
declare(strict_types=1);

// Crea cuentas de estudiante y valida credenciales para iniciar sesión.
final class Usuario
{
    private const AVATARES = [
        '🧠', '🚀', '🦊', '🐼', '🐸', '🦄', '🐙', '🤖', '🐢', '🦁',
        '🐱', '🐶', '🐻', '🐨', '🐯', '🐰', '🐵', '🐧', '🐬', '🦋',
        '🐝', '🦉', '🦖', '🐳', '🦈', '🦩', '🐲', '🐥', '🦔', '🐮',
        '👽', '👾', '🤠', '🥷', '🧙', '🧑‍🚀', '🧑‍💻', '🦸', '🧚', '🧛',
        '⭐', '🌈', '⚡', '🔥', '🌻', '🍀', '🍉', '🎮', '🎨', '🎸',
    ];

    private const ESTADOS_MEXICO = [
        'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche',
        'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima',
        'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo',
        'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca',
        'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa',
        'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán',
        'Zacatecas',
    ];

    // Recibe la conexión de persistencia de usuarios.
    public function __construct(private readonly PDO $db)
    {
    }

    public function registrar(string $nombre, string $correo, string $contrasena): int
    {
        // Valida los datos y almacena únicamente el hash de contraseña, gestionando correos duplicados.
        $correo = strtolower(trim($correo));
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 190) {
            throw new InvalidArgumentException('Escribe un correo electrónico válido.');
        }
        if (strlen($nombre) < 2 || strlen($nombre) > 480) {
            throw new InvalidArgumentException('El nombre debe tener entre 2 y 120 caracteres.');
        }
        if (strlen($contrasena) < 10 || strlen($contrasena) > 4096) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 10 caracteres.');
        }

        $consulta = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, \'student\')'
        );
        try {
            $consulta->execute([
                'name' => trim($nombre),
                'email' => $correo,
                'password_hash' => password_hash($contrasena, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                throw new InvalidArgumentException('Ya existe una cuenta registrada con ese correo.');
            }
            throw $error;
        }
        return (int) $this->db->lastInsertId();
    }

    public function autenticar(string $correo, string $contrasena): ?array
    {
        // Verifica cuenta activa y contraseña; renueva hashes obsoletos antes de devolver datos de sesión.
        $consulta = $this->db->prepare(
            'SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = :email LIMIT 1'
        );
        $consulta->execute(['email' => strtolower(trim($correo))]);
        $usuario = $consulta->fetch();
        if (!$usuario || !(bool) $usuario['is_active'] || !password_verify($contrasena, $usuario['password_hash'])) {
            return null;
        }
        if (password_needs_rehash($usuario['password_hash'], PASSWORD_DEFAULT)) {
            $actualizar = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $actualizar->execute([
                'hash' => password_hash($contrasena, PASSWORD_DEFAULT),
                'id' => $usuario['id'],
            ]);
        }
        unset($usuario['password_hash'], $usuario['is_active']);
        return $usuario;
    }

    public function avatares(): array
    {
        return self::AVATARES;
    }

    public function estadosMexico(): array
    {
        return self::ESTADOS_MEXICO;
    }

    public function perfil(int $usuarioId): array
    {
        $consultaPerfil = $this->db->prepare('SELECT user_id FROM user_profiles WHERE user_id = :user_id');
        $consultaPerfil->execute(['user_id' => $usuarioId]);
        if (!$consultaPerfil->fetchColumn()) {
            $consultaUsuario = $this->db->prepare('SELECT name FROM users WHERE id = :user_id');
            $consultaUsuario->execute(['user_id' => $usuarioId]);
            $nombreCompleto = $consultaUsuario->fetchColumn();
            if (!is_string($nombreCompleto)) {
                throw new RuntimeException('No se encontró la cuenta de este perfil.');
            }
            $nombre = explode(' ', trim($nombreCompleto), 2);
            $insertar = $this->db->prepare(
                'INSERT INTO user_profiles (user_id, username, first_name, last_name)
                 VALUES (:user_id, :username, :first_name, :last_name)'
            );
            $username = 'est' . $usuarioId;
            try {
                $insertar->execute([
                    'user_id' => $usuarioId,
                    'username' => $username,
                    'first_name' => $nombre[0],
                    'last_name' => $nombre[1] ?? '',
                ]);
            } catch (PDOException $error) {
                if ($error->getCode() !== '23000') {
                    throw $error;
                }
                $consultaPerfil->execute(['user_id' => $usuarioId]);
                if (!$consultaPerfil->fetchColumn()) {
                    $insertar->execute([
                        'user_id' => $usuarioId,
                        'username' => 'est' . substr((string) $usuarioId, -10) . '_' . bin2hex(random_bytes(3)),
                        'first_name' => $nombre[0],
                        'last_name' => $nombre[1] ?? '',
                    ]);
                }
            }
        }

        $consulta = $this->db->prepare(
            'SELECT p.username, p.first_name, p.last_name, p.avatar, p.country, p.state, p.age,
                    p.description, u.email, u.created_at
             FROM user_profiles p JOIN users u ON u.id = p.user_id
             WHERE p.user_id = :user_id'
        );
        $consulta->execute(['user_id' => $usuarioId]);
        $perfil = $consulta->fetch();
        if (!$perfil) {
            throw new RuntimeException('No se encontró el perfil de esta cuenta.');
        }
        return $perfil;
    }

    public function actualizarPerfil(
        int $usuarioId,
        string $username,
        string $nombre,
        string $apellido,
        string $estado,
        ?int $edad,
        string $descripcion
    ): void
    {
        $username = strtolower(trim($username));
        $nombre = trim($nombre);
        $apellido = trim($apellido);
        $estado = trim($estado);
        $descripcion = trim($descripcion);
        if (!preg_match('/^[a-zA-Z0-9_]{3,24}$/', $username)) {
            throw new InvalidArgumentException('El nombre de usuario debe tener de 3 a 24 caracteres: letras, números o guion bajo.');
        }
        if (
            $nombre === ''
            || $apellido === ''
            || self::longitudCaracteres($nombre) > 60
            || self::longitudCaracteres($apellido) > 60
            || self::longitudCaracteres($nombre . ' ' . $apellido) > 120
        ) {
            throw new InvalidArgumentException('Escribe un nombre y un apellido de hasta 60 caracteres cada uno.');
        }
        if ($estado !== '' && !in_array($estado, self::ESTADOS_MEXICO, true)) {
            throw new InvalidArgumentException('Selecciona un estado válido de México.');
        }
        if ($edad !== null && ($edad < 5 || $edad > 120)) {
            throw new InvalidArgumentException('La edad debe estar entre 5 y 120 años.');
        }
        if (self::longitudCaracteres($descripcion) > 280) {
            throw new InvalidArgumentException('La descripción no puede superar 280 caracteres.');
        }

        try {
            $this->db->beginTransaction();
            $actualizarPerfil = $this->db->prepare(
                'UPDATE user_profiles SET username = :username, first_name = :first_name,
                    last_name = :last_name, state = :state, age = :age,
                    description = :description WHERE user_id = :user_id'
            );
            $actualizarPerfil->execute([
                'username' => $username,
                'first_name' => $nombre,
                'last_name' => $apellido,
                'state' => $estado,
                'age' => $edad,
                'description' => $descripcion,
                'user_id' => $usuarioId,
            ]);
            $actualizarNombre = $this->db->prepare('UPDATE users SET name = :name WHERE id = :user_id');
            $actualizarNombre->execute([
                'name' => trim($nombre . ' ' . $apellido),
                'user_id' => $usuarioId,
            ]);
            $this->db->commit();
        } catch (PDOException $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($error->getCode() === '23000') {
                throw new InvalidArgumentException('Ese nombre de usuario ya está en uso. Prueba con otro.');
            }
            throw $error;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function actualizarAvatar(int $usuarioId, string $avatar): void
    {
        if (!in_array($avatar, self::AVATARES, true)) {
            throw new InvalidArgumentException('El avatar seleccionado no está disponible.');
        }
        $consulta = $this->db->prepare('UPDATE user_profiles SET avatar = :avatar WHERE user_id = :user_id');
        $consulta->execute(['avatar' => $avatar, 'user_id' => $usuarioId]);
        if ($consulta->rowCount() === 0) {
            $existe = $this->db->prepare('SELECT 1 FROM user_profiles WHERE user_id = :user_id');
            $existe->execute(['user_id' => $usuarioId]);
            if (!$existe->fetchColumn()) {
                throw new RuntimeException('No se encontró el perfil para actualizar el avatar.');
            }
        }
    }

    public function cambiarContrasena(int $usuarioId, string $actual, string $nueva, string $confirmacion): void
    {
        if (strlen($actual) > 4096 || strlen($nueva) < 10 || strlen($nueva) > 4096) {
            throw new InvalidArgumentException('La nueva contraseña debe tener entre 10 y 4096 caracteres.');
        }
        if (!hash_equals($nueva, $confirmacion)) {
            throw new InvalidArgumentException('La nueva contraseña y su confirmación no coinciden.');
        }
        if (hash_equals($actual, $nueva)) {
            throw new InvalidArgumentException('Elige una contraseña diferente a la actual.');
        }

        $consulta = $this->db->prepare('SELECT password_hash FROM users WHERE id = :user_id AND is_active = 1');
        $consulta->execute(['user_id' => $usuarioId]);
        $hashActual = $consulta->fetchColumn();
        if (!is_string($hashActual) || !password_verify($actual, $hashActual)) {
            throw new InvalidArgumentException('La contraseña actual no es correcta.');
        }

        $actualizar = $this->db->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE id = :user_id AND is_active = 1'
        );
        $actualizar->execute([
            'password_hash' => password_hash($nueva, PASSWORD_DEFAULT),
            'user_id' => $usuarioId,
        ]);
        if ($actualizar->rowCount() !== 1) {
            throw new RuntimeException('No se pudo actualizar la contraseña. Inténtalo de nuevo.');
        }
    }

    public function registrarInicioSesion(int $usuarioId): void
    {
        $consulta = $this->db->prepare(
            'INSERT INTO user_activity_days (user_id, activity_date, login_count)
             VALUES (:user_id, :activity_date, 1)
             ON DUPLICATE KEY UPDATE login_count = 1'
        );
        $consulta->execute(['user_id' => $usuarioId, 'activity_date' => date('Y-m-d')]);
    }

    public function registrarTiempoActivo(int $usuarioId, int $segundos): void
    {
        if ($segundos < 1 || $segundos > 90) {
            throw new InvalidArgumentException('El tiempo de actividad no es válido.');
        }
        $consulta = $this->db->prepare(
            'INSERT INTO user_activity_days (user_id, activity_date, active_seconds)
             VALUES (:user_id, :activity_date, :active_seconds)
             ON DUPLICATE KEY UPDATE active_seconds = active_seconds + VALUES(active_seconds)'
        );
        $consulta->execute([
            'user_id' => $usuarioId,
            'activity_date' => date('Y-m-d'),
            'active_seconds' => $segundos,
        ]);
    }

    public function estadisticasPerfil(int $usuarioId): array
    {
        $consulta = $this->db->prepare(
            'SELECT activity_date FROM user_activity_days
             WHERE user_id = :user_id AND login_count > 0
             ORDER BY activity_date DESC'
        );
        $consulta->execute(['user_id' => $usuarioId]);
        $dias = array_column($consulta->fetchAll(), 'activity_date');

        $totales = $this->db->prepare(
            'SELECT COALESCE(SUM(login_count > 0), 0) AS login_days, COALESCE(SUM(active_seconds), 0) AS active_seconds
             FROM user_activity_days WHERE user_id = :user_id'
        );
        $totales->execute(['user_id' => $usuarioId]);
        $resumen = $totales->fetch();

        $hoy = new DateTimeImmutable('today');
        $ayer = $hoy->modify('-1 day');
        $racha = 0;
        if ($dias && ($dias[0] === $hoy->format('Y-m-d') || $dias[0] === $ayer->format('Y-m-d'))) {
            $esperado = new DateTimeImmutable($dias[0]);
            foreach ($dias as $dia) {
                if ($dia !== $esperado->format('Y-m-d')) {
                    break;
                }
                $racha++;
                $esperado = $esperado->modify('-1 day');
            }
        }

        return [
            'login_days' => (int) $resumen['login_days'],
            'active_seconds' => (int) $resumen['active_seconds'],
            'streak_days' => $racha,
        ];
    }

    private static function longitudCaracteres(string $valor): int
    {
        $resultado = preg_match_all('/./us', $valor);
        if ($resultado === false) {
            throw new InvalidArgumentException('El nombre contiene caracteres no válidos.');
        }
        return $resultado;
    }
}
