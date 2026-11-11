<?php
declare(strict_types=1);

// Consulta, administra y califica las actividades prácticas almacenadas en la base de datos.
final class Ejercicio
{
    // Recibe la conexión que se utiliza para las consultas y transacciones del modelo.
    public function __construct(private readonly PDO $db)
    {
    }

    public function listarPublicados(?int $moduloId = null): array
    {
        // Obtiene ejercicios visibles y permite limitar el resultado a un módulo.
        $sql = 'SELECT id,module_id,lesson_id,type,title,description,max_attempts,passing_score
                FROM activities WHERE type <> \'quiz\' AND is_published=1';
        $parametros = [];
        if ($moduloId !== null) {
            $sql .= ' AND module_id=:module_id';
            $parametros['module_id'] = $moduloId;
        }
        $sql .= ' ORDER BY id';
        $consulta = $this->db->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchAll();
    }

    public function listarTodos(): array
    {
        // Devuelve todas las actividades prácticas para su gestión en administración.
        return $this->db->query(
            'SELECT a.id,a.module_id,a.lesson_id,a.type,a.title,a.description,a.max_attempts,a.passing_score,a.is_published,m.title AS module_title
             FROM activities a JOIN modules m ON m.id=a.module_id ORDER BY m.sort_order,a.id'
        )->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        // Recupera un ejercicio publicado y decodifica las opciones de cada consigna.
        $consulta = $this->db->prepare(
            'SELECT id,module_id,lesson_id,type,title,description,max_attempts,passing_score
             FROM activities WHERE id=:id AND type <> \'quiz\' AND is_published=1'
        );
        $consulta->execute(['id' => $id]);
        $actividad = $consulta->fetch();
        if (!$actividad) {
            return null;
        }
        $items = $this->db->prepare(
            'SELECT id,prompt,choices,correct_answer,sort_order FROM exercise_items WHERE activity_id=:activity_id ORDER BY sort_order,id'
        );
        $items->execute(['activity_id' => $id]);
        $actividad['items'] = $items->fetchAll();
        foreach ($actividad['items'] as &$item) {
            $item['choices'] = json_decode((string) $item['choices'], true, 512, JSON_THROW_ON_ERROR);
        }
        unset($item);
        return $actividad;
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        // Valida los datos del ejercicio y crea o actualiza la actividad sin publicar cambios existentes.
        $moduloId = is_scalar($datos['module_id'] ?? null) ? filter_var($datos['module_id'], FILTER_VALIDATE_INT) : false;
        $titulo = is_string($datos['title'] ?? null) ? trim($datos['title']) : '';
        $tipo = is_string($datos['type'] ?? null) ? $datos['type'] : '';
        if (!$moduloId || $titulo === '' || !in_array($tipo, ['identify', 'classify', 'order'], true)) {
            throw new InvalidArgumentException('El ejercicio necesita módulo, título y un tipo válido.');
        }
        if ($id !== null) {
            $existente = $this->db->prepare('SELECT type FROM activities WHERE id=:id');
            $existente->execute(['id' => $id]);
            if ($existente->fetchColumn() === 'quiz') {
                throw new InvalidArgumentException('Un cuestionario no puede convertirse en ejercicio.');
            }
        }
        $maxAttemptsInput = $datos['max_attempts'] ?? null;
        $maxAttempts = is_scalar($maxAttemptsInput) ? filter_var($maxAttemptsInput, FILTER_VALIDATE_INT) : false;
        $publicacion = 0;
        if ($id !== null) {
            $actual = $this->db->prepare("SELECT is_published FROM activities WHERE id=:id AND type <> 'quiz'");
            $actual->execute(['id' => $id]);
            $publicacion = (int) $actual->fetchColumn();
        }
        $valores = [
            'module_id' => $moduloId,
            'lesson_id' => is_scalar($datos['lesson_id'] ?? null) ? (filter_var($datos['lesson_id'], FILTER_VALIDATE_INT) ?: null) : null,
            'type' => $tipo,
            'title' => $titulo,
            'description' => is_string($datos['description'] ?? null) ? trim($datos['description']) : '',
            'max_attempts' => $maxAttempts !== false && $maxAttempts > 0 ? $maxAttempts : null,
            'passing_score' => max(0, min(100, is_scalar($datos['passing_score'] ?? null) ? (int) $datos['passing_score'] : 70)),
            'is_published' => $publicacion,
        ];
        if ($id === null) {
            $consulta = $this->db->prepare(
                'INSERT INTO activities (module_id,lesson_id,type,title,description,max_attempts,passing_score,is_published)
                 VALUES (:module_id,:lesson_id,:type,:title,:description,:max_attempts,:passing_score,:is_published)'
            );
            $consulta->execute($valores);
            return (int) $this->db->lastInsertId();
        }
        $valores['id'] = $id;
        $consulta = $this->db->prepare(
            'UPDATE activities SET module_id=:module_id,lesson_id=:lesson_id,type=:type,title=:title,description=:description,
             max_attempts=:max_attempts,passing_score=:passing_score,is_published=:is_published WHERE id=:id'
        );
        $consulta->execute($valores);
        return $id;
    }

    public function guardarItem(int $actividadId, array $datos, ?int $id = null): int
    {
        // Normaliza opciones, valida su relación con el tipo de actividad y guarda la consigna.
        $prompt = is_string($datos['prompt'] ?? null) ? trim($datos['prompt']) : '';
        $correcta = is_string($datos['correct_answer'] ?? null) ? trim($datos['correct_answer']) : '';
        $choices = $datos['choices'] ?? [];
        if (isset($datos['choices_text']) && is_string($datos['choices_text'])) {
            $choices = preg_split('/\r\n|\r|\n/', $datos['choices_text']) ?: [];
        } elseif (is_array($choices) && count($choices) === 1 && is_string($choices[0])) {
            $choices = preg_split('/\r\n|\r|\n/', $choices[0]) ?: [];
        }
        if ($prompt === '' || !is_array($choices)) {
            throw new InvalidArgumentException('El ejercicio necesita una consigna, respuesta correcta y opciones válidas.');
        }
        $typeQuery = $this->db->prepare('SELECT type FROM activities WHERE id=:id');
        $typeQuery->execute(['id' => $actividadId]);
        $tipoActividad = $typeQuery->fetchColumn();
        if (!in_array($tipoActividad, ['identify', 'classify', 'order'], true)) {
            throw new InvalidArgumentException('La consigna debe pertenecer a un ejercicio existente.');
        }
        if ($correcta === '' && $tipoActividad === 'order') {
            $correcta = $prompt;
        }
        if ($correcta === '') {
            throw new InvalidArgumentException('Indica la respuesta correcta de esta consigna.');
        }
        $ordenEntrada = $datos['sort_order'] ?? null;
        $orden = max(0, is_scalar($ordenEntrada) ? (int) $ordenEntrada : 0);
        // Limpia las opciones vacías antes de almacenarlas como JSON.
        $choices = array_values(array_filter(array_map(
            static fn ($choice): string => is_string($choice) ? trim($choice) : '',
            $choices
        ), static fn (string $choice): bool => $choice !== ''));
        if (count($choices) > 30) {
            throw new InvalidArgumentException('El ejercicio supera el máximo de opciones permitidas.');
        }
        $json = json_encode($choices, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        if ($id === null) {
            $consulta = $this->db->prepare(
                'INSERT INTO exercise_items (activity_id,prompt,choices,correct_answer,sort_order)
                 VALUES (:activity_id,:prompt,:choices,:correct_answer,:sort_order)'
            );
            $consulta->execute([
                'activity_id' => $actividadId,
                'prompt' => $prompt,
                'choices' => $json,
                'correct_answer' => $correcta,
                'sort_order' => $orden,
            ]);
            return (int) $this->db->lastInsertId();
        }
        $consulta = $this->db->prepare(
            'UPDATE exercise_items SET prompt=:prompt,choices=:choices,correct_answer=:correct_answer,sort_order=:sort_order
             WHERE id=:id AND activity_id=:activity_id'
        );
        $consulta->execute([
            'id' => $id,
            'activity_id' => $actividadId,
            'prompt' => $prompt,
            'choices' => $json,
            'correct_answer' => $correcta,
            'sort_order' => $orden,
        ]);
        return $id;
    }

    public function eliminar(int $id): void
    {
        // Elimina solo actividades que no sean cuestionarios.
        $consulta = $this->db->prepare('DELETE FROM activities WHERE id=:id AND type<>\'quiz\'');
        $consulta->execute(['id' => $id]);
    }

    public function eliminarItem(int $id): void
    {
        // Borra una consigna individual por su identificador.
        $consulta = $this->db->prepare('DELETE FROM exercise_items WHERE id=:id');
        $consulta->execute(['id' => $id]);
    }

    public function registrarIntento(int $usuarioId, int $actividadId, array $respuestas): array
    {
        // Evalúa y registra el intento en una transacción para mantener coherentes los resultados.
        $this->db->beginTransaction();
        try {
            // Bloquea la actividad durante la comprobación de publicación y del límite de intentos.
            $lock = $this->db->prepare("SELECT max_attempts FROM activities WHERE id=:id AND type <> 'quiz' AND is_published=1 FOR UPDATE");
            $lock->execute(['id' => $actividadId]);
            $actividad = $lock->fetch();
            if (!$actividad) {
                throw new InvalidArgumentException('No se encontró el ejercicio solicitado.');
            }
            $count = $this->db->prepare('SELECT COUNT(*) FROM activity_attempts WHERE user_id=:user_id AND activity_id=:activity_id');
            $count->execute(['user_id' => $usuarioId, 'activity_id' => $actividadId]);
            $numeroIntento = (int) $count->fetchColumn() + 1;
            if ($actividad['max_attempts'] !== null && $numeroIntento > (int) $actividad['max_attempts']) {
                throw new InvalidArgumentException('Ya alcanzaste el número máximo de intentos para esta actividad.');
            }

            $itemQuery = $this->db->prepare('SELECT id,prompt,correct_answer,sort_order FROM exercise_items WHERE activity_id=:activity_id ORDER BY sort_order,id');
            $itemQuery->execute(['activity_id' => $actividadId]);
            $items = $itemQuery->fetchAll();
            if (!$items) {
                throw new InvalidArgumentException('Este ejercicio todavía no tiene consignas configuradas.');
            }
            $puntaje = 0;
            $revisiones = [];
            $typeQuery = $this->db->prepare('SELECT type FROM activities WHERE id=:activity_id');
            $typeQuery->execute(['activity_id' => $actividadId]);
            $tipo = $typeQuery->fetchColumn();

            // Comprueba una permutación completa para ejercicios de orden y respuestas válidas en los demás tipos.
            if ($tipo === 'order') {
                $ordenEnviado = $respuestas['order'] ?? null;
                if (is_array($ordenEnviado)) {
                    foreach ($ordenEnviado as $itemId) {
                        if (!is_scalar($itemId) || !ctype_digit((string) $itemId)) {
                            throw new InvalidArgumentException('El orden de los elementos no es válido.');
                        }
                    }
                    $ordenEnviado = array_map('strval', $ordenEnviado);
                }
                $ordenCorrecto = array_map(static fn ($item): string => (string) $item['id'], $items);
                if (!is_array($ordenEnviado) || count($ordenEnviado) !== count($items) ||
                    array_diff($ordenEnviado, $ordenCorrecto) || array_diff($ordenCorrecto, $ordenEnviado) ||
                    count(array_unique($ordenEnviado)) !== count($items)) {
                    throw new InvalidArgumentException('Selecciona cada elemento una vez para ordenar la actividad.');
                }
                foreach ($ordenCorrecto as $indice => $id) {
                    $correcta = (string) $ordenEnviado[$indice] === $id;
                    $puntaje += $correcta ? 1 : 0;
                    $revisiones[] = [
                        'item_id' => (int) $id,
                        'prompt' => $items[$indice]['prompt'],
                        'respuesta' => (int) $ordenEnviado[$indice],
                        'correct' => $correcta,
                    ];
                }
            } else {
                if (array_diff(array_keys($respuestas), array_map(static fn ($item): string => (string) $item['id'], $items))) {
                    throw new InvalidArgumentException('El ejercicio contiene respuestas no válidas.');
                }
                foreach ($items as $item) {
                    $id = (string) $item['id'];
                    $respuesta = $respuestas[$id] ?? '';
                    if (!is_scalar($respuesta) || trim((string) $respuesta) === '') {
                        throw new InvalidArgumentException('Responde todas las consignas antes de enviar el ejercicio.');
                    }
                    $respuesta = trim((string) $respuesta);
                    $correcta = hash_equals((string) $item['correct_answer'], $respuesta);
                    $puntaje += $correcta ? 1 : 0;
                    $revisiones[] = [
                        'item_id' => (int) $item['id'],
                        'prompt' => $item['prompt'],
                        'respuesta' => $respuesta,
                        'correct' => $correcta,
                        'correct_answer' => (string) $item['correct_answer'],
                    ];
                }
            }
            // Calcula porcentaje y registra el detalle del intento antes de confirmar la transacción.
            $maximo = count($items);
            $porcentaje = (int) round($puntaje * 100 / $maximo);
            $insert = $this->db->prepare(
                'INSERT INTO activity_attempts (activity_id,user_id,attempt_number,score,max_score,percentage,answers)
                 VALUES (:activity_id,:user_id,:attempt_number,:score,:max_score,:percentage,:answers)'
            );
            $insert->execute([
                'activity_id' => $actividadId,
                'user_id' => $usuarioId,
                'attempt_number' => $numeroIntento,
                'score' => $puntaje,
                'max_score' => $maximo,
                'percentage' => $porcentaje,
                'answers' => json_encode($revisiones, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
            $this->db->commit();
            return [
                'attempt_id' => (int) $this->db->lastInsertId(),
                'score' => $puntaje,
                'max_score' => $maximo,
                'percentage' => $porcentaje,
                'answers' => $revisiones,
            ];
        } catch (Throwable $error) {
            // Revierte cualquier escritura parcial y conserva el error para quien invocó el modelo.
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }
}
