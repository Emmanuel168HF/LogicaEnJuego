<?php
declare(strict_types=1);

// Coordina los intentos de ejercicios y las operaciones administrativas relacionadas.
final class EjercicioController
{
    // Recibe la conexión que utilizarán las acciones del controlador.
    public function __construct(private readonly PDO $db)
    {
    }

    public function enviar(): void
{
    // Autentica al participante.
    $usuario = requerirUsuario();

    $usuarioId = (int) $usuario['id'];
    $actividadId = enteroPost('activity_id');
    $respuestas = $_POST['answers'] ?? [];

    if (!is_array($respuestas) || count($respuestas) > 200) {
        throw new InvalidArgumentException(
            'Las respuestas del ejercicio no son válidas.'
        );
    }

    // Registra y evalúa el intento.
    $ejercicio = new Ejercicio($this->db);

    $resultado = $ejercicio->registrarIntento(
        $usuarioId,
        $actividadId,
        $respuestas
    );

    // Actualiza las insignias.
    (new Progreso($this->db))->otorgarInsignia(
        $usuarioId,
        'primer-reto'
    );

    // Entrega 10 puntos si se alcanzó la calificación mínima.
    $recompensa = [
        'registrado' => false,
    ];

    if ($resultado['aprobado']) {
        $token = new Token($this->db);

        $recompensa = $token->ganarConResultado(
            $usuarioId,
            10,
            'Recompensa por completar un reto',
            'challenge_completed_' . $actividadId
        );
    }

    // Guarda los datos para la pantalla de retroalimentación.
    iniciarSesionSegura();

    $_SESSION['ultimo_resultado'] = $resultado + [
        'kind' => 'exercise',
        'activity_id' => $actividadId,
    ];

    // Prepara la notificación.
    if ($recompensa['registrado']) {
        mensajeRecompensaFlash(
            '¡Reto completado!',
            10
        );
    }

    redirigir(rutaPagina('retroalimentacion', [
        'attempt' => $resultado['attempt_id']
    ]));
}

    public function guardar(): void
    {
        // Restringe la creación/edición al personal administrador y vuelve al editor del ejercicio.
        requerirAdministrador();
        $actividadId = (new Ejercicio($this->db))->guardar($_POST, enteroOpcionalPost('id'));
        mensajeFlash('ok', 'La actividad se guardó. Puedes agregar sus consignas desde el formulario de ejercicios.');
        redirigir(rutaPagina('admin', ['section' => 'exercises', 'activity' => $actividadId]));
    }

    public function guardarItem(): void
    {
        // Guarda una consigna de ejercicio validada y abre la actividad recién actualizada.
        requerirAdministrador();
        $actividadId = enteroPost('activity_id');
        (new Ejercicio($this->db))->guardarItem($actividadId, $_POST, enteroOpcionalPost('id'));
        mensajeFlash('ok', 'La consigna del ejercicio se guardó.');
        redirigir(rutaPagina('admin', ['section' => 'exercises', 'activity' => $actividadId]));
    }
}
