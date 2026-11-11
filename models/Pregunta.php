<?php
declare(strict_types=1);

// Gestiona preguntas, opciones y la calificación transaccional de cuestionarios.
final class Pregunta
{
    // Recibe la conexión requerida para las consultas y transacciones del modelo.
    public function __construct(private readonly PDO $db)
    {
    }

    public function cuestionario(int $actividadId): array
    {
        // Recupera las preguntas publicadas con sus opciones visibles, agrupadas por pregunta.
        $consulta = $this->db->prepare(
            'SELECT q.id, q.prompt, q.explanation, q.sort_order,
                    o.id AS option_id, o.label AS option_label, o.sort_order AS option_order
             FROM questions q
             JOIN activities a ON a.id = q.activity_id
             LEFT JOIN answer_options o ON o.question_id = q.id
             WHERE q.activity_id = :activity_id AND a.type = \'quiz\' AND a.is_published = 1
             ORDER BY q.sort_order, q.id, o.sort_order, o.id'
        );
        $consulta->execute(['activity_id' => $actividadId]);
        $preguntas = [];
        foreach ($consulta->fetchAll() as $fila) {
            $id = (int) $fila['id'];
            if (!isset($preguntas[$id])) {
                $preguntas[$id] = [
                    'id' => $id,
                    'prompt' => $fila['prompt'],
                    'explanation' => $fila['explanation'],
                    'options' => [],
                ];
            }
            if ($fila['option_id'] !== null) {
                $preguntas[$id]['options'][] = [
                    'id' => (int) $fila['option_id'],
                    'label' => $fila['option_label'],
                ];
            }
        }
        return array_values($preguntas);
    }

    public function actividad(int $id): ?array
    {
        // Busca una actividad de tipo cuestionario que esté publicada.
        $consulta = $this->db->prepare(
            'SELECT id, module_id, lesson_id, type, title, description, max_attempts, passing_score
             FROM activities WHERE id = :id AND type = \'quiz\' AND is_published = 1'
        );
        $consulta->execute(['id' => $id]);
        return $consulta->fetch() ?: null;
    }

    public function listarTodas(): array
    {
        // Lista preguntas con su actividad asociada para el panel administrativo.
        return $this->db->query(
            'SELECT q.id,q.activity_id,q.prompt,q.explanation,q.sort_order,a.title AS activity_title
             FROM questions q JOIN activities a ON a.id=q.activity_id ORDER BY a.title,q.sort_order,q.id'
        )->fetchAll();
    }

    public function buscarAdmin(int $id): ?array
    {
        // Recupera una pregunta y organiza sus opciones en el formato esperado por el formulario de edición.
        $consulta = $this->db->prepare('SELECT id,activity_id,prompt,explanation,sort_order FROM questions WHERE id=:id');
        $consulta->execute(['id' => $id]);
        $pregunta = $consulta->fetch();
        if (!$pregunta) {
            return null;
        }
        $opciones = $this->db->prepare('SELECT id,label,is_correct,sort_order FROM answer_options WHERE question_id=:id ORDER BY sort_order');
        $opciones->execute(['id' => $id]);
        $pregunta['options'] = array_fill(0, 8, '');
        foreach ($opciones->fetchAll() as $opcion) {
            $pregunta['options'][(int) $opcion['sort_order']] = $opcion['label'];
            if ($opcion['is_correct']) {
                $pregunta['correct_option'] = (int) $opcion['sort_order'] + 1;
            }
        }
        return $pregunta;
    }

    public function eliminar(int $id): void
    {
        // Elimina la pregunta indicada y delega el tratamiento de opciones relacionadas a la base de datos.
        $consulta = $this->db->prepare('DELETE FROM questions WHERE id=:id');
        $consulta->execute(['id' => $id]);
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        // Valida actividad, texto y opciones; reemplaza la pregunta y sus respuestas en una transacción.
        $actividadId = is_scalar($datos['activity_id'] ?? null) ? filter_var($datos['activity_id'], FILTER_VALIDATE_INT) : false;
        $prompt = is_string($datos['prompt'] ?? null) ? trim($datos['prompt']) : '';
        $opciones = $datos['options'] ?? [];
        $correctaEntrada = $datos['correct_option'] ?? null;
        $correcta = is_scalar($correctaEntrada) ? filter_var($correctaEntrada, FILTER_VALIDATE_INT) : false;
        if (!$actividadId || $prompt === '' || !is_array($opciones) || count($opciones) < 2 ||
            count($opciones) > 8 || !$correcta) {
            throw new InvalidArgumentException('Cada pregunta necesita un cuestionario, texto y al menos dos opciones.');
        }
        $opciones = array_map(static fn ($opcion): string => is_string($opcion) ? trim($opcion) : '', $opciones);
        $respuestaCorrecta = $opciones[$correcta - 1] ?? '';
        if ($respuestaCorrecta === '' || count(array_filter($opciones, static fn (string $opcion): bool => $opcion !== '')) < 2) {
            throw new InvalidArgumentException('Revisa las opciones y selecciona una respuesta correcta.');
        }
        $tipoActividad = $this->db->prepare('SELECT type FROM activities WHERE id=:id');
        $tipoActividad->execute(['id' => $actividadId]);
        if ($tipoActividad->fetchColumn() !== 'quiz') {
            throw new InvalidArgumentException('Las preguntas solo pueden pertenecer a un cuestionario.');
        }
        // Mantiene sincronizados el registro de pregunta y sus opciones, incluso al editar.
        $this->db->beginTransaction();
        try {
            if ($id === null) {
                $consulta = $this->db->prepare(
                    'INSERT INTO questions (activity_id, prompt, explanation, sort_order) VALUES (:activity_id,:prompt,:explanation,:sort_order)'
                );
                $consulta->execute([
                    'activity_id' => $actividadId,
                    'prompt' => $prompt,
                    'explanation' => is_string($datos['explanation'] ?? null) ? trim($datos['explanation']) : '',
                    'sort_order' => max(0, (int) ($datos['sort_order'] ?? 0)),
                ]);
                $id = (int) $this->db->lastInsertId();
            } else {
                $consulta = $this->db->prepare(
                    'UPDATE questions SET activity_id=:activity_id,prompt=:prompt,explanation=:explanation,sort_order=:sort_order WHERE id=:id'
                );
                $consulta->execute([
                    'id' => $id,
                    'activity_id' => $actividadId,
                    'prompt' => $prompt,
                    'explanation' => is_string($datos['explanation'] ?? null) ? trim($datos['explanation']) : '',
                    'sort_order' => max(0, (int) ($datos['sort_order'] ?? 0)),
                ]);
                $delete = $this->db->prepare('DELETE FROM answer_options WHERE question_id=:id');
                $delete->execute(['id' => $id]);
            }
            $insert = $this->db->prepare(
                'INSERT INTO answer_options (question_id,label,is_correct,sort_order) VALUES (:question_id,:label,:is_correct,:sort_order)'
            );
            foreach ($opciones as $indice => $opcion) {
                if ($opcion === '') {
                    continue;
                }
                $insert->execute([
                    'question_id' => $id,
                    'label' => $opcion,
                    'is_correct' => $indice === $correcta - 1 ? 1 : 0,
                    'sort_order' => $indice,
                ]);
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function registrarIntento(int $usuarioId, int $actividadId, array $respuestas): array
    {
        // Bloquea el cuestionario, valida intentos y respuestas, califica y persiste el resultado completo.
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare('SELECT max_attempts FROM activities WHERE id=:id AND type=\'quiz\' AND is_published=1 FOR UPDATE');
            $lock->execute(['id' => $actividadId]);
            $actividad = $lock->fetch();
            if (!$actividad) {
                throw new InvalidArgumentException('No se encontró el cuestionario solicitado.');
            }
            $count = $this->db->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE user_id=:user_id AND activity_id=:activity_id');
            $count->execute(['user_id' => $usuarioId, 'activity_id' => $actividadId]);
            $numeroIntento = (int) $count->fetchColumn() + 1;
            if ($actividad['max_attempts'] !== null && $numeroIntento > (int) $actividad['max_attempts']) {
                throw new InvalidArgumentException('Ya alcanzaste el número máximo de intentos para este cuestionario.');
            }

            // Solo acepta cuestionarios completos, con exactamente una opción correcta por pregunta.
            $questionQuery = $this->db->prepare(
                'SELECT q.id,q.prompt,q.explanation,
                    (SELECT COUNT(*) FROM answer_options o WHERE o.question_id=q.id AND o.is_correct=1) AS correct_count,
                    (SELECT o.id FROM answer_options o WHERE o.question_id=q.id AND o.is_correct=1 ORDER BY o.id LIMIT 1) AS correct_option_id,
                    (SELECT o.label FROM answer_options o WHERE o.question_id=q.id AND o.is_correct=1 ORDER BY o.id LIMIT 1) AS correct_label
                 FROM questions q
                 WHERE q.activity_id=:activity_id ORDER BY q.sort_order,q.id'
            );
            $questionQuery->execute(['activity_id' => $actividadId]);
            $preguntas = $questionQuery->fetchAll();
            if (!$preguntas || array_filter($preguntas, static fn ($pregunta): bool => (int) $pregunta['correct_count'] !== 1)) {
                throw new InvalidArgumentException('El cuestionario todavía no está completo.');
            }
            if (array_diff(array_keys($respuestas), array_map(static fn ($pregunta): string => (string) $pregunta['id'], $preguntas))) {
                throw new InvalidArgumentException('El cuestionario contiene respuestas no válidas.');
            }
            // Compara cada opción con su pregunta y prepara la retroalimentación para la vista de resultados.
            $puntaje = 0;
            $feedback = [];
            $respuestasGuardadas = [];
            foreach ($preguntas as $pregunta) {
                $preguntaId = (int) $pregunta['id'];
                $respuesta = $respuestas[(string) $preguntaId] ?? null;
                $opcionId = is_scalar($respuesta) ? filter_var($respuesta, FILTER_VALIDATE_INT) : false;
                if (!$opcionId || $opcionId < 1) {
                    throw new InvalidArgumentException('Responde todas las preguntas antes de enviar el cuestionario.');
                }
                $esCorrecta = false;
                $respuestaSeleccionada = null;
                $validar = $this->db->prepare(
                    'SELECT is_correct,label FROM answer_options WHERE id=:option_id AND question_id=:question_id'
                );
                $validar->execute(['option_id' => $opcionId, 'question_id' => $preguntaId]);
                $opcion = $validar->fetch();
                if (!$opcion) {
                    throw new InvalidArgumentException('Una de las respuestas seleccionadas no pertenece a la pregunta.');
                }
                $esCorrecta = (bool) $opcion['is_correct'];
                $respuestaSeleccionada = $opcion['label'];
                $puntaje += $esCorrecta ? 1 : 0;
                $respuestasGuardadas[] = [
                    'question_id' => $preguntaId,
                    'option_id' => $opcionId,
                    'is_correct' => $esCorrecta ? 1 : 0,
                ];
                $feedback[] = [
                    'question_id' => $preguntaId,
                    'prompt' => $pregunta['prompt'],
                    'explanation' => $pregunta['explanation'],
                    'correct' => $esCorrecta,
                    'selected_answer' => $respuestaSeleccionada,
                    'correct_answer' => $pregunta['correct_label'],
                ];
            }
            $maximo = count($preguntas);
            $porcentaje = (int) round($puntaje * 100 / $maximo);
            $insert = $this->db->prepare(
                'INSERT INTO quiz_attempts (activity_id,user_id,attempt_number,score,max_score,percentage)
                 VALUES (:activity_id,:user_id,:attempt_number,:score,:max_score,:percentage)'
            );
            $insert->execute([
                'activity_id' => $actividadId,
                'user_id' => $usuarioId,
                'attempt_number' => $numeroIntento,
                'score' => $puntaje,
                'max_score' => $maximo,
                'percentage' => $porcentaje,
            ]);
            $attemptId = (int) $this->db->lastInsertId();
            $guardarRespuesta = $this->db->prepare(
                'INSERT INTO quiz_attempt_answers (attempt_id,question_id,selected_option_id,is_correct)
                 VALUES (:attempt_id,:question_id,:option_id,:is_correct)'
            );
            foreach ($respuestasGuardadas as $respuesta) {
                $guardarRespuesta->execute([
                    'attempt_id' => $attemptId,
                    'question_id' => $respuesta['question_id'],
                    'option_id' => $respuesta['option_id'],
                    'is_correct' => $respuesta['is_correct'],
                ]);
            }
            // Persiste la puntuación y cada respuesta seleccionada como una unidad atómica.
            $this->db->commit();
            return [
                'attempt_id' => $attemptId,
                'score' => $puntaje,
                'max_score' => $maximo,
                'percentage' => $porcentaje,
                'answers' => $feedback,
            ];
        } catch (Throwable $error) {
            // Evita conservar intentos incompletos si falla cualquier paso de la calificación.
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }
}
