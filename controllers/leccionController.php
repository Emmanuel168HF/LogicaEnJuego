<?php
declare(strict_types=1);

// Gestiona la finalización de lecciones y su mantenimiento administrativo.
final class LeccionController
{
    // Mantiene disponible la conexión para las operaciones sobre lecciones.
    public function __construct(private readonly PDO $db)
    {
    }

    public function completar(): void
    {
        // Registra el avance del usuario y comprueba si corresponde otorgar insignias.
        $usuario = requerirUsuario();
        (new Leccion($this->db))->marcarCompletada((int) $usuario['id'], enteroPost('lesson_id'));
        $progreso = new Progreso($this->db);
        $progreso->otorgarInsignia((int) $usuario['id'], 'primer-paso');
        $progreso->comprobarRutaCompleta((int) $usuario['id']);
        mensajeFlash('ok', 'Lección marcada como completada.');
        redirigir(rutaPagina('progreso'));
    }

    public function guardar(): void
    {
        // Protege el mantenimiento de lecciones y delega sus reglas de datos al modelo.
        requerirAdministrador();
        (new Leccion($this->db))->guardar($_POST, enteroOpcionalPost('id'));
        mensajeFlash('ok', 'La lección se guardó correctamente.');
        redirigir(rutaPagina('admin', ['section' => 'lessons']));
    }

    public function eliminar(): void
    {
        // Limita la eliminación a administradores; el modelo resuelve el borrado asociado.
        requerirAdministrador();
        (new Leccion($this->db))->eliminar(enteroPost('id'));
        mensajeFlash('ok', 'La lección se eliminó junto con su progreso asociado.');
        redirigir(rutaPagina('admin', ['section' => 'lessons']));
    }
}
