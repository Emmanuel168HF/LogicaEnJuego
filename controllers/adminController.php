<?php
declare(strict_types=1);

// Atiende operaciones administrativas sobre cuestionarios, publicación y eliminación de actividades.
final class AdminController
{
    // Conserva la conexión usada por las operaciones administrativas.
    public function __construct(private readonly PDO $db)
    {
    }

    public function guardarCuestionario(): void
    {
        // Exige rol administrador, delega validación/guardado al modelo y vuelve al editor.
        requerirAdministrador();
        $actividadId = (new Modulo($this->db))->guardarActividad(
            $_POST,
            enteroOpcionalPost('id')
        );
        mensajeFlash('ok', 'El cuestionario se guardó.');
        redirigir(rutaPagina('admin', ['section' => 'questions', 'activity' => $actividadId]));
    }

    public function publicarActividad(): void
    {
        // Verifica permisos, existencia y contenido completo antes de cambiar la publicación.
        requerirAdministrador();
        $id = enteroPost('id');
        $publicar = ($_POST['publish'] ?? '') === '1';
        $consulta = $this->db->prepare(
            'SELECT a.type,a.module_id,m.is_published,m.archived_at
             FROM activities a JOIN modules m ON m.id=a.module_id WHERE a.id=:id'
        );
        $consulta->execute(['id' => $id]);
        $actividad = $consulta->fetch();
        if (!$actividad) {
            throw new InvalidArgumentException('No se encontró la actividad.');
        }
        // Al publicar, confirma que el módulo esté disponible y que cada pregunta/consigna tenga respuestas.
        if ($publicar) {
            if (!(bool) $actividad['is_published'] || $actividad['archived_at'] !== null) {
                throw new InvalidArgumentException('Publica primero el módulo de esta actividad.');
            }
            if ($actividad['type'] === 'quiz') {
                $comprobar = $this->db->prepare(
                    'SELECT COUNT(*) AS questions,
                            SUM((SELECT COUNT(*) FROM answer_options o WHERE o.question_id=q.id AND o.is_correct=1)) AS correct_answers
                     FROM questions q WHERE q.activity_id=:activity_id'
                );
            } elseif ($actividad['type'] === 'order') {
                $comprobar = $this->db->prepare(
                    'SELECT COUNT(*) AS items,COUNT(*) AS configured
                     FROM exercise_items WHERE activity_id=:activity_id'
                );
            } else {
                $comprobar = $this->db->prepare(
                    'SELECT COUNT(*) AS items,
                            SUM(CASE WHEN JSON_LENGTH(choices)>=2 AND JSON_CONTAINS(choices,JSON_QUOTE(correct_answer))
                                     THEN 1 ELSE 0 END) AS configured
                     FROM exercise_items WHERE activity_id=:activity_id'
                );
            }
            $comprobar->execute(['activity_id' => $id]);
            $contenido = $comprobar->fetch();
            if ((int) ($contenido['questions'] ?? $contenido['items'] ?? 0) === 0 ||
                ($actividad['type'] === 'quiz' && (int) $contenido['correct_answers'] !== (int) $contenido['questions']) ||
                ($actividad['type'] !== 'quiz' && (int) $contenido['configured'] !== (int) $contenido['items']) ||
                ($actividad['type'] === 'order' && (int) $contenido['items'] < 2)) {
                throw new InvalidArgumentException('Completa y revisa todas las preguntas u opciones antes de publicar esta actividad.');
            }
        }
        $actualizar = $this->db->prepare('UPDATE activities SET is_published=:published WHERE id=:id');
        $actualizar->execute(['id' => $id, 'published' => $publicar ? 1 : 0]);
        mensajeFlash('ok', 'Se actualizó el estado de publicación de la actividad.');
        redirigir(rutaPagina('admin', ['section' => $actividad['type'] === 'quiz' ? 'questions' : 'exercises']));
    }

    public function eliminarActividad(): void
    {
        // Elimina el cuestionario o ejercicio mediante el modelo correspondiente.
        requerirAdministrador();
        $id = enteroPost('id');
        $tipo = (string) ($_POST['type'] ?? '');
        if ($tipo === 'quiz') {
            $consulta = $this->db->prepare('DELETE FROM activities WHERE id=:id AND type=\'quiz\'');
            $consulta->execute(['id' => $id]);
        } else {
            (new Ejercicio($this->db))->eliminar($id);
        }
        mensajeFlash('ok', 'La actividad se eliminó junto con su contenido e intentos asociados.');
        redirigir(rutaPagina('admin', ['section' => $tipo === 'quiz' ? 'questions' : 'exercises']));
    }

    public function eliminarItemEjercicio(): void
    {
        // Elimina una consigna individual y regresa al ejercicio que la contiene.
        requerirAdministrador();
        (new Ejercicio($this->db))->eliminarItem(enteroPost('id'));
        mensajeFlash('ok', 'La consigna se eliminó.');
        redirigir(rutaPagina('admin', ['section' => 'exercises', 'activity' => enteroPost('activity_id')]));
    }
}
