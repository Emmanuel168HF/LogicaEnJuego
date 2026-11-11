<?php
declare(strict_types=1);

// Coordina repetición de actividades, evaluaciones y su administración.
final class ProgresoController
{
    // Recibe la conexión compartida con modelos de progreso y evaluación.
    public function __construct(private readonly PDO $db)
    {
    }

    public function repetir(): void
    {
        // Restringe la repetición al usuario y lo dirige según el tipo de actividad publicada.
        $usuario = requerirUsuario();
        $actividadId = enteroPost('activity_id');
        $actividad = $this->db->prepare('SELECT type FROM activities WHERE id=:id AND is_published=1');
        $actividad->execute(['id' => $actividadId]);
        $tipo = $actividad->fetchColumn();
        if ($tipo === 'quiz') {
            redirigir(rutaPagina('cuestionario', ['activity' => $actividadId]));
        }
        if (in_array($tipo, ['identify', 'classify', 'order'], true)) {
            redirigir(rutaPagina('ejercicio', ['activity' => $actividadId]));
        }
        throw new InvalidArgumentException('La actividad solicitada no está disponible.');
    }

    public function evaluacion(): void
    {
        // Comprueba formato, autorización y requisitos de acceso antes de registrar respuestas.
        $tipo = valorPost('evaluation_type', 20);
        $respuestas = $_POST['answers'] ?? [];
        if (!is_array($respuestas) || count($respuestas) > 100) {
            throw new InvalidArgumentException('Las respuestas de la evaluación no son válidas.');
        }
        foreach ($respuestas as $clave => $respuesta) {
            if (!is_string($clave) || (!is_scalar($respuesta) && !is_array($respuesta))) {
                throw new InvalidArgumentException('El formato de las respuestas de la evaluación no es válido.');
            }
        }
        $consentimiento = isset($_POST['guardian_consent']) && $_POST['guardian_consent'] === '1';
        $usuario = $tipo === 'post' ? requerirUsuario() : null;
        if ($tipo === 'post') {
            $modulosPublicados = (new Modulo($this->db))->listarPublicados();
            $resumen = (new Progreso($this->db))->resumen((int) $usuario['id']);
            if (!$modulosPublicados || (int) $resumen['completed_modules'] < count($modulosPublicados)) {
                throw new InvalidArgumentException('Completa todos los módulos para desbloquear la evaluación posterior.');
            }
        }
        // Persiste la evaluación, conserva un resumen temporal y vuelve a la pantalla de resultado.
        $resultado = (new Evaluacion($this->db))->registrar(
            $tipo,
            $usuario ? (int) $usuario['id'] : null,
            $consentimiento,
            $respuestas
        );
        iniciarSesionSegura();
        $_SESSION['ultima_evaluacion'] = ['type' => $tipo, 'id' => $resultado['id'], 'score' => $resultado['score']];
        mensajeFlash(
            'ok',
            $tipo === 'diagnostic'
                ? 'Tu diagnóstico se registró de forma anónima.'
                : 'Tu evaluación del prototipo se registró correctamente.'
        );
        redirigir(rutaPagina($tipo === 'diagnostic' ? 'diagnostico' : 'evaluacion_final'));
    }

    public function guardarPreguntaEvaluacion(): void
    {
        // Limita la edición de preguntas de evaluación a administradores.
        requerirAdministrador();
        (new Evaluacion($this->db))->guardar($_POST, enteroOpcionalPost('id'));
        mensajeFlash('ok', 'La pregunta de evaluación se guardó.');
        redirigir(rutaPagina('admin', ['section' => 'evaluations']));
    }

    public function eliminarPreguntaEvaluacion(): void
    {
        // Elimina una pregunta de evaluación con autorización administrativa.
        requerirAdministrador();
        (new Evaluacion($this->db))->eliminar(enteroPost('id'));
        mensajeFlash('ok', 'La pregunta de evaluación se eliminó.');
        redirigir(rutaPagina('admin', ['section' => 'evaluations']));
    }
}
