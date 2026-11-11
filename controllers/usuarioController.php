<?php
declare(strict_types=1);

// Gestiona registro, autenticación y cierre de sesión de las cuentas.
final class UsuarioController
{
    // Comparte la conexión con el modelo de usuarios.
    public function __construct(private readonly PDO $db)
    {
    }

    public function registrar(): void
    {
        // Valida la confirmación, crea la cuenta y establece una sesión regenerada para el nuevo usuario.
        $nombre = valorPost('name', 480);
        $correo = valorPost('email', 190);
        $contrasena = valorPost('password', 4096);
        if (!hash_equals($contrasena, valorPost('password_confirmation', 4096))) {
            throw new InvalidArgumentException('Las contraseñas no coinciden.');
        }

        $modelo = new Usuario($this->db);
        $id = $modelo->registrar($nombre, $correo, $contrasena);
        $perfil = $modelo->perfil($id);
        $modelo->registrarInicioSesion($id);
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['usuario'] = [
            'id' => $id,
            'name' => trim($nombre),
            'email' => strtolower(trim($correo)),
            'role' => 'student',
            'username' => $perfil['username'],
            'avatar' => $perfil['avatar'],
        ];
        $_SESSION['profile_activity_last_ping'] = time();
        mensajeFlash('ok', 'Tu cuenta se creó correctamente.');
        redirigir(rutaPagina('menu'));
    }

    public function iniciarSesion(): void
    {
        // Autentica credenciales y cambia el identificador de sesión antes de guardar la identidad.
        $modelo = new Usuario($this->db);
        $usuario = $modelo->autenticar(valorPost('email', 190), valorPost('password', 4096));
        if ($usuario === null) {
            throw new InvalidArgumentException('El correo o la contraseña no son correctos.');
        }
        $perfil = $modelo->perfil((int) $usuario['id']);
        $modelo->registrarInicioSesion((int) $usuario['id']);
        $usuario['username'] = $perfil['username'];
        $usuario['avatar'] = $perfil['avatar'];
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['usuario'] = $usuario;
        $_SESSION['profile_activity_last_ping'] = time();
        mensajeFlash('ok', 'Sesión iniciada.');
        redirigir($usuario['role'] === 'admin' ? rutaPagina('admin') : rutaPagina('menu'));
    }

    public function guardarPerfil(): void
    {
        $usuario = requerirUsuario();
        $modelo = new Usuario($this->db);
        $modelo->actualizarPerfil(
            (int) $usuario['id'],
            valorPost('username', 24),
            valorPost('first_name', 60),
            valorPost('last_name', 60),
            valorPost('state', 60),
            enteroOpcionalPost('age'),
            valorPost('description', 280)
        );
        $perfil = $modelo->perfil((int) $usuario['id']);
        $_SESSION['usuario']['name'] = trim($perfil['first_name'] . ' ' . $perfil['last_name']);
        $_SESSION['usuario']['username'] = $perfil['username'];
        mensajeFlash('ok', '¡Tu perfil quedó actualizado!');
        redirigir(rutaPagina('perfil'));
    }

    public function guardarAvatar(): void
    {
        $usuario = requerirUsuario();
        $avatar = valorPost('avatar', 16);
        $modelo = new Usuario($this->db);
        $modelo->actualizarAvatar((int) $usuario['id'], $avatar);
        $_SESSION['usuario']['avatar'] = $avatar;
        mensajeFlash('ok', '¡Tu avatar se actualizó!');
        redirigir(rutaPagina('perfil'));
    }

    public function cambiarContrasena(): void
    {
        $usuario = requerirUsuario();
        (new Usuario($this->db))->cambiarContrasena(
            (int) $usuario['id'],
            valorPost('current_password', 4096),
            valorPost('new_password', 4096),
            valorPost('new_password_confirmation', 4096)
        );
        mensajeFlash('ok', 'Tu contraseña se actualizó correctamente.');
        redirigir(rutaPagina('perfil'));
    }

    public function registrarActividad(): never
    {
        $usuario = requerirUsuario();
        $ahora = time();
        $anterior = (int) ($_SESSION['profile_activity_last_ping'] ?? $ahora);
        $transcurrido = $ahora - $anterior;
        if ($transcurrido >= 45 && $transcurrido <= 180) {
            (new Usuario($this->db))->registrarTiempoActivo((int) $usuario['id'], min(90, $transcurrido));
        }
        $_SESSION['profile_activity_last_ping'] = $ahora;
        http_response_code(204);
        exit;
    }

    public function cerrarSesion(): void
    {
        // Vacía los datos de sesión y elimina la cookie antes de finalizarla.
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], $parametros['secure'], $parametros['httponly']);
        }
        session_destroy();
        redirigir(rutaPagina('inicio'));
    }
}
