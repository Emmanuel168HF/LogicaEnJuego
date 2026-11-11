<?php
// Exige tipos estrictos para reducir conversiones de datos inesperadas.
declare(strict_types=1);

// Coordina el envío de cuestionarios y la gestión administrativa de preguntas.
final class CuestionarioController
{
    // Recibe la conexión a la base de datos que utilizarán las operaciones del controlador.
    public function __construct(private readonly PDO $db)
    {
    }

    // Valida y registra las respuestas del estudiante y muestra el resultado del intento.
    public function enviar(): void
    {
        // Impide que una persona no autenticada envíe respuestas.
        $usuario = requerirUsuario();
        // Obtiene el identificador de la actividad enviada por el formulario.
        $actividadId = enteroPost('activity_id');
        // Recupera las respuestas del formulario; si no se enviaron, usa una lista vacía.
        $respuestas = $_POST['answers'] ?? [];
        if (!is_array($respuestas)) {
            throw new InvalidArgumentException('Las respuestas enviadas no son válidas.');
        }
        // Registra el intento y calcula sus resultados para el usuario actual.
        $resultado = (new Pregunta($this->db))->registrarIntento((int) $usuario['id'], $actividadId, $respuestas);
        // Actualiza las insignias obtenidas según la actividad completada.
        $progreso = new Progreso($this->db);
        $progreso->otorgarInsignia((int) $usuario['id'], 'primer-reto');
        if ($resultado['percentage'] === 100) {
            $progreso->otorgarInsignia((int) $usuario['id'], 'arquitecto-de-silicio');
        }
        // Guarda el resultado en la sesión para que la página siguiente pueda mostrarlo.
        iniciarSesionSegura();
        $_SESSION['ultimo_resultado'] = $resultado + ['kind' => 'quiz', 'activity_id' => $actividadId];
        // Abre la página de resultados correspondiente al intento recién registrado.
        redirigir(rutaPagina('resultados', ['attempt' => $resultado['attempt_id']]));
    }

    // Guarda una pregunta nueva o actualiza una existente desde el panel de administración.
    public function guardarPregunta(): void
    {
        // Restringe esta operación a usuarios con permisos administrativos.
        requerirAdministrador();
        (new Pregunta($this->db))->guardar($_POST, enteroOpcionalPost('id'));
        // Muestra un aviso y vuelve a la lista de preguntas del panel.
        mensajeFlash('ok', 'La pregunta y sus opciones se guardaron.');
        redirigir(rutaPagina('admin', ['section' => 'questions']));
    }

    // Elimina la pregunta indicada desde el panel de administración.
    public function eliminarPregunta(): void
    {
        // Evita que usuarios sin permisos puedan modificar el cuestionario.
        requerirAdministrador();
        (new Pregunta($this->db))->eliminar(enteroPost('id'));
        // Confirma la operación y regresa a la lista de preguntas.
        mensajeFlash('ok', 'La pregunta se eliminó.');
        redirigir(rutaPagina('admin', ['section' => 'questions']));
    }
}
