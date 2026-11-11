<?php
declare(strict_types=1);

// Consolida avance, intentos, insignias y resultados de las evaluaciones de cada usuario.
final class Progreso
{
    // Recibe la conexión utilizada para las consultas de progreso.
    public function __construct(private readonly PDO $db)
    {
    }

    public function resumen(int $usuarioId): array
    {
        // Calcula totales de lecciones, módulos, puntos e insignias y deriva el porcentaje completado.
        $consulta = $this->db->prepare(
            'SELECT
                (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id=l.module_id
                 WHERE l.is_published=1 AND m.is_published=1 AND m.archived_at IS NULL) AS total_lessons,
                (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id=lp.lesson_id
                 WHERE lp.user_id=:user_completed AND lp.completed_at IS NOT NULL AND l.is_published=1) AS completed_lessons,
                (SELECT COUNT(*) FROM modules m WHERE m.is_published=1 AND m.archived_at IS NULL
                 AND EXISTS (SELECT 1 FROM lessons l WHERE l.module_id=m.id AND l.is_published=1)
                 AND NOT EXISTS (SELECT 1 FROM lessons l LEFT JOIN lesson_progress lp ON lp.lesson_id=l.id AND lp.user_id=:user_module
                     WHERE l.module_id=m.id AND l.is_published=1 AND lp.completed_at IS NULL)) AS completed_modules,
                (SELECT COALESCE(SUM(score),0) FROM quiz_attempts WHERE user_id=:user_quiz) +
                (SELECT COALESCE(SUM(score),0) FROM activity_attempts WHERE user_id=:user_activity) AS points,
                (SELECT COUNT(*) FROM user_badges WHERE user_id=:user_badge) AS badges'
        );
        $consulta->execute([
            'user_completed' => $usuarioId,
            'user_module' => $usuarioId,
            'user_quiz' => $usuarioId,
            'user_activity' => $usuarioId,
            'user_badge' => $usuarioId,
        ]);
        $resumen = $consulta->fetch();
        $resumen['completion_percentage'] = (int) $resumen['total_lessons'] > 0
            ? (int) round((int) $resumen['completed_lessons'] * 100 / (int) $resumen['total_lessons'])
            : 0;
        return $resumen;
    }

    public function historial(int $usuarioId, int $limite = 50): array
    {
        // Combina intentos de cuestionarios y ejercicios en un historial ordenado por fecha.
        $limite = max(1, min(100, $limite));
        $consulta = $this->db->prepare(
            'SELECT a.id AS activity_id,a.title AS activity_title, qa.score, qa.max_score, qa.percentage, qa.attempt_number, qa.created_at, \'cuestionario\' AS kind
             FROM quiz_attempts qa JOIN activities a ON a.id=qa.activity_id WHERE qa.user_id=:quiz_user
             UNION ALL
             SELECT a.id,a.title, aa.score, aa.max_score, aa.percentage, aa.attempt_number, aa.created_at, \'ejercicio\'
             FROM activity_attempts aa JOIN activities a ON a.id=aa.activity_id WHERE aa.user_id=:exercise_user
             ORDER BY created_at DESC LIMIT ' . $limite
        );
        $consulta->execute(['quiz_user' => $usuarioId, 'exercise_user' => $usuarioId]);
        return $consulta->fetchAll();
    }

    public function actividadesRepetibles(int $usuarioId): array
    {
        // Resume mejor y último resultado para cada actividad que el usuario puede volver a intentar.
        $consulta = $this->db->prepare(
            'SELECT a.id AS activity_id,a.title AS activity_title,COUNT(*) AS attempt_count,MAX(qa.percentage) AS best_percentage,
                    (SELECT latest.percentage FROM quiz_attempts latest WHERE latest.user_id=:quiz_latest_user
                     AND latest.activity_id=a.id ORDER BY latest.created_at DESC,latest.id DESC LIMIT 1) AS last_percentage,
                    MAX(qa.created_at) AS last_attempted_at,\'quiz\' AS kind
             FROM quiz_attempts qa JOIN activities a ON a.id=qa.activity_id
             WHERE qa.user_id=:quiz_user GROUP BY a.id,a.title
             UNION ALL
             SELECT a.id,a.title,COUNT(*),MAX(aa.percentage),
                    (SELECT latest.percentage FROM activity_attempts latest WHERE latest.user_id=:exercise_latest_user
                     AND latest.activity_id=a.id ORDER BY latest.created_at DESC,latest.id DESC LIMIT 1),
                    MAX(aa.created_at),\'exercise\'
             FROM activity_attempts aa JOIN activities a ON a.id=aa.activity_id
             WHERE aa.user_id=:exercise_user GROUP BY a.id,a.title
             ORDER BY last_attempted_at DESC'
        );
        $consulta->execute([
            'quiz_latest_user' => $usuarioId,
            'quiz_user' => $usuarioId,
            'exercise_latest_user' => $usuarioId,
            'exercise_user' => $usuarioId,
        ]);
        return $consulta->fetchAll();
    }

    public function insignias(int $usuarioId): array
    {
        // Recupera las insignias obtenidas y la fecha de cada reconocimiento.
        $consulta = $this->db->prepare(
            'SELECT b.slug,b.name,b.description,ub.awarded_at
             FROM user_badges ub JOIN badges b ON b.id=ub.badge_id WHERE ub.user_id=:user_id ORDER BY ub.awarded_at DESC'
        );
        $consulta->execute(['user_id' => $usuarioId]);
        return $consulta->fetchAll();
    }

    public function otorgarInsignia(int $usuarioId, string $slug): void
    {
        // Asocia una insignia existente sin duplicar registros para el mismo usuario.
        $consulta = $this->db->prepare(
            'INSERT INTO user_badges (user_id,badge_id)
             SELECT :user_id,id FROM badges WHERE slug=:slug
             ON DUPLICATE KEY UPDATE awarded_at=awarded_at'
        );
        $consulta->execute(['user_id' => $usuarioId, 'slug' => $slug]);
    }

    public function comprobarRutaCompleta(int $usuarioId): void
    {
        // Comprueba la finalización de todas las lecciones publicadas antes de otorgar la insignia de ruta.
        $consulta = $this->db->prepare(
            'SELECT COUNT(*) AS total,
                    SUM(CASE WHEN lp.completed_at IS NOT NULL THEN 1 ELSE 0 END) AS completed
             FROM lessons l JOIN modules m ON m.id=l.module_id
             LEFT JOIN lesson_progress lp ON lp.lesson_id=l.id AND lp.user_id=:user_id
             WHERE l.is_published=1 AND m.is_published=1 AND m.archived_at IS NULL'
        );
        $consulta->execute(['user_id' => $usuarioId]);
        $resultado = $consulta->fetch();
        if ((int) $resultado['total'] > 0 && (int) $resultado['total'] === (int) $resultado['completed']) {
            $this->otorgarInsignia($usuarioId, 'ruta-completa');
        }
    }

    public function registrarEvaluacion(string $tipo, ?int $usuarioId, bool $consentimiento, array $respuestas): int
    {
        // Valida consentimiento e identidad; registra el diagnóstico anónimamente y la evaluación posterior con usuario.
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
        $json = json_encode($respuestas, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $consulta = $this->db->prepare(
            'INSERT INTO evaluation_submissions (evaluation_type,user_id,guardian_consent,answers)
             VALUES (:type,:user_id,:consent,:answers)'
        );
        $consulta->execute([
            'type' => $tipo,
            'user_id' => $usuarioId,
            'consent' => $consentimiento ? 1 : 0,
            'answers' => $json,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function compararEvaluaciones(): array
    {
        // Resume cantidad y puntuación media de las evaluaciones calificadas por tipo.
        $consulta = $this->db->query(
            'SELECT evaluation_type,COUNT(*) AS submissions,AVG(score) AS average_score
             FROM evaluation_submissions WHERE score IS NOT NULL GROUP BY evaluation_type'
        );
        return $consulta->fetchAll();
    }
}
