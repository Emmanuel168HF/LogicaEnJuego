<?php
declare(strict_types=1);

// Administra preguntas y respuestas de las evaluaciones diagnóstica y posterior.
final class Evaluacion
{
    // Conserva la conexión usada por las consultas de evaluación.
    public function __construct(private readonly PDO $db)
    {
    }

    public function listar(string $tipo, bool $soloPublicadas = true): array
    {
        // Valida el tipo solicitado y devuelve sus preguntas, decodificando las opciones JSON.
        if (!in_array($tipo, ['diagnostic', 'post'], true)) {
            throw new InvalidArgumentException('Tipo de evaluación no válido.');
        }
        $sql = 'SELECT id,evaluation_type,field_key,prompt,input_type,choices,correct_answer,is_scored,is_required,sort_order,is_published
                FROM evaluation_questions WHERE evaluation_type=:type';
        if ($soloPublicadas) {
            $sql .= ' AND is_published=1';
        }
        $sql .= ' ORDER BY sort_order,id';
        $consulta = $this->db->prepare($sql);
        $consulta->execute(['type' => $tipo]);
        $preguntas = $consulta->fetchAll();
        foreach ($preguntas as &$pregunta) {
            $pregunta['choices'] = json_decode((string) $pregunta['choices'], true, 512, JSON_THROW_ON_ERROR);
        }
        unset($pregunta);
        return $preguntas;
    }

    public function listarTodas(): array
    {
        // Lista el banco completo de preguntas para el panel administrativo.
        $filas = $this->db->query(
            'SELECT id,evaluation_type,field_key,prompt,input_type,choices,correct_answer,is_scored,is_required,sort_order,is_published
             FROM evaluation_questions ORDER BY evaluation_type,sort_order,id'
        )->fetchAll();
        foreach ($filas as &$fila) {
            $fila['choices'] = json_decode((string) $fila['choices'], true, 512, JSON_THROW_ON_ERROR);
        }
        unset($fila);
        return $filas;
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        // Valida estructura, respuestas y opciones antes de insertar o actualizar una pregunta.
        $tipo = (string) ($datos['evaluation_type'] ?? '');
        $key = strtolower(trim((string) ($datos['field_key'] ?? '')));
        $prompt = trim((string) ($datos['prompt'] ?? ''));
        $inputType = (string) ($datos['input_type'] ?? '');
        $choices = $datos['choices'] ?? [];
        if (!in_array($tipo, ['diagnostic', 'post'], true) ||
            !preg_match('/^[a-z][a-z0-9_]{1,79}$/', $key) || $prompt === '' ||
            !in_array($inputType, ['choice', 'rating', 'text'], true) || !is_array($choices)) {
            throw new InvalidArgumentException('Completa el tipo, la clave, la pregunta y un formato de respuesta válido.');
        }
        $lineasOpciones = [];
        foreach ($choices as $choice) {
            if (!is_string($choice)) {
                continue;
            }
            foreach (preg_split('/\r\n|\r|\n/', $choice) ?: [] as $linea) {
                if (trim($linea) !== '') {
                    $lineasOpciones[] = trim($linea);
                }
            }
        }
        // Normaliza el formato de cada opción para conservar un valor y una etiqueta.
        $choices = $lineasOpciones;
        if ($inputType !== 'text' && count($choices) < 2) {
            throw new InvalidArgumentException('Las preguntas de selección necesitan al menos dos opciones.');
        }
        $choicesNormalizadas = [];
        foreach ($choices as $choice) {
            [$value, $label] = array_pad(explode('|', $choice, 2), 2, null);
            $value = trim($value);
            $label = trim($label ?? $value);
            if ($value === '' || $label === '') {
                throw new InvalidArgumentException('Revisa las opciones de la evaluación.');
            }
            $choicesNormalizadas[] = ['value' => $value, 'label' => $label];
        }
        $choicesJson = json_encode($choicesNormalizadas, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $correct = trim((string) ($datos['correct_answer'] ?? ''));
        $scored = !empty($datos['is_scored']);
        if ($scored && ($inputType !== 'choice' || !in_array($correct, array_column($choicesNormalizadas, 'value'), true))) {
            throw new InvalidArgumentException('Selecciona una de las opciones como respuesta correcta.');
        }
        $valores = [
            'type' => $tipo,
            'field_key' => $key,
            'prompt' => $prompt,
            'input_type' => $inputType,
            'choices' => $choicesJson,
            'correct_answer' => $scored ? $correct : null,
            'is_scored' => $scored ? 1 : 0,
            'is_required' => !empty($datos['is_required']) ? 1 : 0,
            'sort_order' => max(0, (int) ($datos['sort_order'] ?? 0)),
            'is_published' => !empty($datos['is_published']) ? 1 : 0,
        ];
        if ($id === null) {
            $consulta = $this->db->prepare(
                'INSERT INTO evaluation_questions
                 (evaluation_type,field_key,prompt,input_type,choices,correct_answer,is_scored,is_required,sort_order,is_published)
                 VALUES (:type,:field_key,:prompt,:input_type,:choices,:correct_answer,:is_scored,:is_required,:sort_order,:is_published)'
            );
            $consulta->execute($valores);
            return (int) $this->db->lastInsertId();
        }
        $valores['id'] = $id;
        $consulta = $this->db->prepare(
            'UPDATE evaluation_questions SET evaluation_type=:type,field_key=:field_key,prompt=:prompt,input_type=:input_type,
             choices=:choices,correct_answer=:correct_answer,is_scored=:is_scored,is_required=:is_required,sort_order=:sort_order,
             is_published=:is_published WHERE id=:id'
        );
        $consulta->execute($valores);
        return $id;
    }

    public function eliminar(int $id): void
    {
        // Elimina la pregunta indicada del banco de evaluaciones.
        $consulta = $this->db->prepare('DELETE FROM evaluation_questions WHERE id=:id');
        $consulta->execute(['id' => $id]);
    }

    public function registrar(string $tipo, ?int $usuarioId, bool $consentimiento, array $respuestas): array
    {
        // Verifica tipo, consentimiento y acceso; el diagnóstico conserva el carácter anónimo.
        if (!in_array($tipo, ['diagnostic', 'post'], true)) {
            throw new InvalidArgumentException('Tipo de evaluación no válido.');
        }
        if (!$consentimiento) {
            throw new InvalidArgumentException('Confirma que cuentas con las autorizaciones necesarias antes de enviar la evaluación.');
        }
        if ($tipo === 'diagnostic') {
            $usuarioId = null;
        } elseif ($usuarioId === null) {
            throw new InvalidArgumentException('Inicia sesión para registrar la evaluación posterior.');
        }

        // Contrasta respuestas contra el formulario publicado y calcula únicamente las preguntas puntuables.
        $preguntas = $this->listar($tipo);
        if (!$preguntas) {
            throw new InvalidArgumentException('Esta evaluación no tiene preguntas publicadas.');
        }
        $mapa = [];
        $correctas = 0;
        $totalPuntuables = 0;
        foreach ($preguntas as $pregunta) {
            $key = $pregunta['field_key'];
            $respuesta = $respuestas[$key] ?? null;
            if ($pregunta['is_required'] && ($respuesta === null || $respuesta === '')) {
                throw new InvalidArgumentException('Responde todas las preguntas obligatorias antes de enviar.');
            }
            if ($respuesta === null || $respuesta === '') {
                continue;
            }
            if (!is_scalar($respuesta)) {
                throw new InvalidArgumentException('Una respuesta tiene formato no válido.');
            }
            $respuesta = trim((string) $respuesta);
            if ($pregunta['input_type'] === 'text') {
                if (strlen($respuesta) > 2000) {
                    throw new InvalidArgumentException('El comentario supera los 2000 caracteres.');
                }
            } elseif (!in_array($respuesta, array_map(
                static fn ($choice): string => is_array($choice) ? (string) ($choice['value'] ?? '') : (string) $choice,
                $pregunta['choices']
            ), true)) {
                throw new InvalidArgumentException('Una respuesta no coincide con las opciones de la evaluación.');
            }
            $mapa[$key] = $respuesta;
            if ($pregunta['is_scored']) {
                $totalPuntuables++;
                $correctas += hash_equals((string) $pregunta['correct_answer'], $respuesta) ? 1 : 0;
            }
        }
        $keysPermitidas = array_column($preguntas, 'field_key');
        if (array_diff(array_keys($respuestas), $keysPermitidas)) {
            throw new InvalidArgumentException('La evaluación contiene respuestas que no pertenecen a este formulario.');
        }
        // Guarda respuestas normalizadas y resultado, si la evaluación incluye preguntas calificables.
        $score = $totalPuntuables > 0 ? round($correctas * 100 / $totalPuntuables, 2) : null;
        $consulta = $this->db->prepare(
            'INSERT INTO evaluation_submissions (evaluation_type,user_id,guardian_consent,answers,score)
             VALUES (:type,:user_id,:consent,:answers,:score)'
        );
        $consulta->execute([
            'type' => $tipo,
            'user_id' => $usuarioId,
            'consent' => $consentimiento ? 1 : 0,
            'answers' => json_encode($mapa, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'score' => $score,
        ]);
        return ['id' => (int) $this->db->lastInsertId(), 'score' => $score];
    }
}
