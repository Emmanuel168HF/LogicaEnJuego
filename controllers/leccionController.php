<?php
declare(strict_types=1);

// Gestiona la finalización de lecciones y su mantenimiento administrativo.
final class LeccionController
{
    // Mantiene disponible la conexión para las operaciones sobre lecciones.
    public function __construct(private readonly PDO $db)
    {
    }

    public function completar(): void {
        $usuario = requerirUsuario();

        $usuarioId = (int) $usuario['id'];
        $leccionId = enteroPost('lesson_id');

        // Marca la lección como completada.
        $leccion = new Leccion($this->db);

        $leccion->marcarCompletada(
            $usuarioId,
            $leccionId
        );

        $token = new Token($this->db);

        // Recompensa por completar la lección.
        $recompensaLeccion = $token->ganarConResultado(
            $usuarioId,
            5,
            'Recompensa por completar una lección',
            'lesson_completed_' . $leccionId
        );

        // Comprueba si todas las lecciones del módulo están completadas.
        $moduloId = $leccion->obtenerModuloCompletado(
            $usuarioId,
            $leccionId
        );

        $recompensaModulo = [
            'registrado' => false,
        ];

        // Recompensa por completar el módulo.
        if ($moduloId !== null) {
            $recompensaModulo = $token->ganarConResultado(
                $usuarioId,
                15,
                'Recompensa por completar un módulo',
                'module_completed_' . $moduloId
            );
        }

        // Actualiza las insignias y el progreso.
        $progreso = new Progreso($this->db);

        $progreso->otorgarInsignia(
            $usuarioId,
            'primer-paso'
        );

        $progreso->comprobarRutaCompleta($usuarioId);

        // Calcula las recompensas realmente registradas.
        $puntosGanados = 0;

        if ($recompensaLeccion['registrado']) {
            $puntosGanados += 5;
        }

        if ($recompensaModulo['registrado']) {
            $puntosGanados += 15;
        }

        // Muestra una notificación según las recompensas obtenidas.
        if ($puntosGanados === 20) {
            mensajeRecompensaFlash(
                '¡Lección y módulo completados!',
                20
            );
        } elseif ($puntosGanados === 15) {
            mensajeRecompensaFlash(
                '¡Módulo completado!',
                15
            );
        } elseif ($puntosGanados === 5) {
            mensajeRecompensaFlash(
                '¡Lección completada!',
                5
            );
        } else {
            mensajeFlash(
                'ok',
                'Lección marcada como completada.'
            );
        }

        redirigir(rutaPagina('leccion', [
            'lesson' => $leccionId
        ]));
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
