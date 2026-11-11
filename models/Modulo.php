<?php
declare(strict_types=1);

// Consulta y mantiene módulos de aprendizaje y sus actividades tipo cuestionario.
final class Modulo
{
    // Recibe la conexión compartida con la base de datos.
    public function __construct(private readonly PDO $db)
    {
    }

    public function listarPublicados(?int $usuarioId = null): array
    {
        // Lista módulos activos y calcula cuántas lecciones tiene y cuántas completó el usuario.
        $consulta = $this->db->prepare(
            'SELECT m.id, m.slug, m.title, m.description, m.objectives, m.difficulty, m.estimated_minutes,
                    m.sort_order, COUNT(DISTINCT l.id) AS lesson_count,
                    COUNT(DISTINCT CASE WHEN lp.completed_at IS NOT NULL THEN lp.lesson_id END) AS completed_lessons
             FROM modules m
             LEFT JOIN lessons l ON l.module_id = m.id AND l.is_published = 1
             LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = :user_id
             WHERE m.is_published = 1 AND m.archived_at IS NULL
             GROUP BY m.id
             ORDER BY m.sort_order, m.id'
        );
        $consulta->execute(['user_id' => $usuarioId]);
        return $consulta->fetchAll();
    }

    public function listarTodos(): array
    {
        // Devuelve los módulos publicados, borradores y archivados para administración.
        return $this->db->query(
            'SELECT id, slug, title, description, objectives, difficulty, estimated_minutes, sort_order, is_published, archived_at
             FROM modules ORDER BY sort_order, id'
        )->fetchAll();
    }

    public function buscarPorId(int $id, bool $soloPublicados = true): ?array
    {
        // Busca un módulo no archivado y permite incluir borradores solo cuando se solicita.
        $sql = 'SELECT id, slug, title, description, objectives, difficulty, estimated_minutes, sort_order, is_published
                FROM modules WHERE id = :id AND archived_at IS NULL';
        if ($soloPublicados) {
            $sql .= ' AND is_published = 1';
        }
        $consulta = $this->db->prepare($sql);
        $consulta->execute(['id' => $id]);
        $modulo = $consulta->fetch();
        return $modulo ?: null;
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        // Valida identidad y campos del módulo, y aplica requisitos adicionales si se publica.
        $tituloEntrada = $datos['title'] ?? null;
        $slugEntrada = $datos['slug'] ?? null;
        if (!is_string($tituloEntrada) || !is_string($slugEntrada)) {
            throw new InvalidArgumentException('El título o el identificador URL del módulo no es válido.');
        }
        $titulo = trim($tituloEntrada);
        $slug = strtolower(trim($slugEntrada));
        if ($titulo === '' || strlen($titulo) > 180 ||
            !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException('El título o el identificador URL del módulo no es válido.');
        }

        $duracionEntrada = $datos['estimated_minutes'] ?? null;
        $ordenEntrada = $datos['sort_order'] ?? null;
        $valores = [
            'slug' => $slug,
            'title' => $titulo,
            'description' => is_string($datos['description'] ?? null) ? trim($datos['description']) : '',
            'objectives' => is_string($datos['objectives'] ?? null) ? trim($datos['objectives']) : '',
            'difficulty' => in_array($datos['difficulty'] ?? '', ['inicial', 'intermedio', 'avanzado'], true)
                ? $datos['difficulty'] : 'inicial',
            'estimated_minutes' => max(1, min(600, is_scalar($duracionEntrada) ? (int) $duracionEntrada : 15)),
            'sort_order' => max(0, is_scalar($ordenEntrada) ? (int) $ordenEntrada : 0),
            'is_published' => !empty($datos['is_published']) ? 1 : 0,
        ];
        // Un módulo publicado requiere descripción, objetivos y al menos una lección publicada.
        if ($valores['is_published']) {
            if ($valores['description'] === '' || $valores['objectives'] === '' ||
                empty($valores['difficulty']) || empty($valores['estimated_minutes'])) {
                throw new InvalidArgumentException('Antes de publicar el módulo, completa su descripción, objetivos, dificultad y duración.');
            }
            if ($id === null) {
                throw new InvalidArgumentException('Primero crea el módulo como borrador, agrega al menos una lección publicada y luego publícalo.');
            }
            $lecciones = $this->db->prepare('SELECT COUNT(*) FROM lessons WHERE module_id=:id AND is_published=1');
            $lecciones->execute(['id' => $id]);
            if ((int) $lecciones->fetchColumn() === 0) {
                throw new InvalidArgumentException('Publica al menos una lección antes de publicar este módulo.');
            }
        }

        if ($id === null) {
            $consulta = $this->db->prepare(
                'INSERT INTO modules (slug, title, description, objectives, difficulty, estimated_minutes, sort_order, is_published)
                 VALUES (:slug, :title, :description, :objectives, :difficulty, :estimated_minutes, :sort_order, :is_published)'
            );
            $consulta->execute($valores);
            return (int) $this->db->lastInsertId();
        }

        $valores['id'] = $id;
        $consulta = $this->db->prepare(
            'UPDATE modules SET slug = :slug, title = :title, description = :description, objectives = :objectives,
             difficulty = :difficulty, estimated_minutes = :estimated_minutes, sort_order = :sort_order, is_published = :is_published
             WHERE id = :id AND archived_at IS NULL'
        );
        $consulta->execute($valores);
        return $id;
    }

    public function archivar(int $id): void
    {
        // Retira el módulo del catálogo al marcarlo archivado y despublicarlo.
        $consulta = $this->db->prepare('UPDATE modules SET archived_at = CURRENT_TIMESTAMP, is_published = 0 WHERE id = :id');
        $consulta->execute(['id' => $id]);
    }

    public function actividades(int $moduloId): array
    {
        // Obtiene únicamente las actividades publicadas que pertenecen al módulo.
        $consulta = $this->db->prepare(
            'SELECT id,module_id,lesson_id,type,title,description,max_attempts,passing_score
             FROM activities WHERE module_id=:module_id AND is_published=1 ORDER BY id'
        );
        $consulta->execute(['module_id' => $moduloId]);
        return $consulta->fetchAll();
    }

    public function guardarActividad(array $datos, ?int $id = null): int
    {
        // Crea o actualiza un cuestionario manteniendo su estado de publicación previo.
        $moduloId = is_scalar($datos['module_id'] ?? null) ? filter_var($datos['module_id'], FILTER_VALIDATE_INT) : false;
        $tituloEntrada = $datos['title'] ?? null;
        $titulo = is_string($tituloEntrada) ? trim($tituloEntrada) : '';
        if (!$moduloId || $titulo === '' || strlen($titulo) > 180) {
            throw new InvalidArgumentException('El cuestionario necesita un módulo y un título válido.');
        }
        $maxAttemptsInput = $datos['max_attempts'] ?? null;
        $maxAttempts = is_scalar($maxAttemptsInput) ? filter_var($maxAttemptsInput, FILTER_VALIDATE_INT) : false;
        $publicacion = 0;
        if ($id !== null) {
            $actual = $this->db->prepare('SELECT is_published FROM activities WHERE id=:id AND type=\'quiz\'');
            $actual->execute(['id' => $id]);
            $publicacion = (int) $actual->fetchColumn();
        }
        $valores = [
            'module_id' => $moduloId,
            'lesson_id' => is_scalar($datos['lesson_id'] ?? null) ? (filter_var($datos['lesson_id'], FILTER_VALIDATE_INT) ?: null) : null,
            'title' => $titulo,
            'description' => is_string($datos['description'] ?? null) ? trim($datos['description']) : '',
            'max_attempts' => $maxAttempts !== false && $maxAttempts > 0 ? $maxAttempts : null,
            'passing_score' => max(0, min(100, is_scalar($datos['passing_score'] ?? null) ? (int) $datos['passing_score'] : 70)),
            'is_published' => $publicacion,
        ];
        if ($id === null) {
            $consulta = $this->db->prepare(
                'INSERT INTO activities (module_id,lesson_id,type,title,description,max_attempts,passing_score,is_published)
                 VALUES (:module_id,:lesson_id,\'quiz\',:title,:description,:max_attempts,:passing_score,:is_published)'
            );
            $consulta->execute($valores);
            return (int) $this->db->lastInsertId();
        }
        $valores['id'] = $id;
        $consulta = $this->db->prepare(
            'UPDATE activities SET module_id=:module_id,lesson_id=:lesson_id,title=:title,description=:description,
             max_attempts=:max_attempts,passing_score=:passing_score,is_published=:is_published
             WHERE id=:id AND type=\'quiz\''
        );
        $consulta->execute($valores);
        return $id;
    }
}
