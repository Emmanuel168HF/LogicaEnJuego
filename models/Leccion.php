<?php
declare(strict_types=1);

// Lee y mantiene lecciones, además de registrar su finalización por estudiante.
final class Leccion
{
    // Recibe la conexión de base de datos que utilizarán las operaciones del modelo.
    public function __construct(private readonly PDO $db)
    {
    }

    public function listarPorModulo(int $moduloId, ?int $usuarioId = null): array
    {
        // Lista lecciones publicadas del módulo y agrega el estado de avance del usuario.
        $consulta = $this->db->prepare(
            'SELECT l.id, l.module_id, l.title, l.content, l.estimated_minutes, l.sort_order,
                    (lp.completed_at IS NOT NULL) AS is_completed
             FROM lessons l
             LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = :user_id
             WHERE l.module_id = :module_id AND l.is_published = 1
             ORDER BY l.sort_order, l.id'
        );
        $consulta->execute(['module_id' => $moduloId, 'user_id' => $usuarioId]);
        return $consulta->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        // Recupera una lección solo si tanto ella como su módulo están publicados y disponibles.
        $consulta = $this->db->prepare(
            'SELECT l.id, l.module_id, l.title, l.content, l.estimated_minutes, l.sort_order, m.title AS module_title
             FROM lessons l JOIN modules m ON m.id = l.module_id
             WHERE l.id = :id AND l.is_published = 1 AND m.is_published = 1 AND m.archived_at IS NULL'
        );
        $consulta->execute(['id' => $id]);
        return $consulta->fetch() ?: null;
    }

    public function listarTodas(): array
    {
        // Devuelve todas las lecciones con el título de su módulo para el panel.
        return $this->db->query(
            'SELECT l.id, l.title, l.content, l.estimated_minutes, l.sort_order, l.is_published, m.title AS module_title, l.module_id
             FROM lessons l JOIN modules m ON m.id = l.module_id ORDER BY m.sort_order, l.sort_order, l.id'
        )->fetchAll();
    }

    public function guardar(array $datos, ?int $id = null): int
    {
        // Valida campos obligatorios y límites antes de crear o actualizar una lección.
        $moduloId = is_scalar($datos['module_id'] ?? null) ? filter_var($datos['module_id'], FILTER_VALIDATE_INT) : false;
        $titulo = is_string($datos['title'] ?? null) ? trim($datos['title']) : '';
        $contenido = is_string($datos['content'] ?? null) ? trim($datos['content']) : '';
        if (!$moduloId || $titulo === '' || $contenido === '' || strlen($titulo) > 180) {
            throw new InvalidArgumentException('Completa el módulo, el título y el contenido de la lección.');
        }
        $valores = [
            'module_id' => $moduloId,
            'title' => $titulo,
            'content' => $contenido,
            'estimated_minutes' => max(1, min(600, is_scalar($datos['estimated_minutes'] ?? null) ? (int) $datos['estimated_minutes'] : 10)),
            'sort_order' => max(0, is_scalar($datos['sort_order'] ?? null) ? (int) $datos['sort_order'] : 0),
            'is_published' => !empty($datos['is_published']) ? 1 : 0,
        ];
        if ($id === null) {
            $consulta = $this->db->prepare(
                'INSERT INTO lessons (module_id, title, content, estimated_minutes, sort_order, is_published)
                 VALUES (:module_id, :title, :content, :estimated_minutes, :sort_order, :is_published)'
            );
            $consulta->execute($valores);
            return (int) $this->db->lastInsertId();
        }
        $valores['id'] = $id;
        $consulta = $this->db->prepare(
            'UPDATE lessons SET module_id=:module_id, title=:title, content=:content, estimated_minutes=:estimated_minutes,
             sort_order=:sort_order, is_published=:is_published WHERE id=:id'
        );
        $consulta->execute($valores);
        return $id;
    }

    public function eliminar(int $id): void
    {
        // Elimina la lección; las relaciones asociadas se resuelven según las reglas de la base de datos.
        $consulta = $this->db->prepare('DELETE FROM lessons WHERE id = :id');
        $consulta->execute(['id' => $id]);
    }

    public function marcarCompletada(int $usuarioId, int $leccionId): void
    {
        // Confirma que la lección siga publicada y registra su finalización sin sobrescribir la fecha inicial.
        $validar = $this->db->prepare('SELECT id FROM lessons WHERE id=:lesson_id AND is_published=1');
        $validar->execute(['lesson_id' => $leccionId]);
        if (!$validar->fetchColumn()) {
            throw new InvalidArgumentException('No se encontró la lección solicitada.');
        }
        $consulta = $this->db->prepare(
            'INSERT INTO lesson_progress (user_id, lesson_id, completed_at)
             SELECT :user_id, l.id, CURRENT_TIMESTAMP FROM lessons l
             WHERE l.id = :lesson_id AND l.is_published = 1
             ON DUPLICATE KEY UPDATE completed_at = COALESCE(completed_at, CURRENT_TIMESTAMP)'
        );
        $consulta->execute(['user_id' => $usuarioId, 'lesson_id' => $leccionId]);
    }
}
