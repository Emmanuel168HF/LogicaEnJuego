<?php
declare(strict_types=1);

// Gestiona las operaciones relacionadas con los tokens.
final class TokenController
{
    // Comparte la conexión PDO con el modelo Token.
    public function __construct(private readonly PDO $db)
    {
    }

    // Consulta el saldo del estudiante autenticado.
    public function consultarSaldo(): int
    {
        $usuario = requerirUsuario();

        $modelo = new Token($this->db);

        return $modelo->obtenerSaldo((int) $usuario['id']);
    }

    // Consulta el historial de movimientos.
    public function consultarHistorial(int $limite = 20): array
    {
        $usuario = requerirUsuario();

        $modelo = new Token($this->db);

        return $modelo->obtenerHistorial(
            (int) $usuario['id'],
            $limite
        );
    }

    // Permite utilizar tokens para solicitar una pista.
    public function solicitarPista(
        int $costo,
        string $descripcion,
        string $referencia
    ): int {
        $usuario = requerirUsuario();

        $modelo = new Token($this->db);

        return $modelo->gastar(
            (int) $usuario['id'],
            $costo,
            $descripcion,
            $referencia
        );
    }

    // Procesa la solicitud AJAX para descontar tokens y devolver la pista.
    public function usarPista(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $actividadId = (int) ($_POST['activity_id'] ?? 0);
            if ($actividadId <= 0) {
                throw new InvalidArgumentException('Actividad no válida.');
            }

            $costo = 12;
            $nuevoSaldo = $this->solicitarPista(
                $costo,
                'Pista en ejercicio #' . $actividadId,
                'hint_activity_' . $actividadId . '_' . time()
            );

            // TODO (Rama IA): Aquí llamarás a (new ServiceIA())->responder(...)
            $pista = 'Pista desbloqueada: analiza cada elemento e identifica su función principal antes de clasificarlo.';

            echo json_encode([
                'ok' => true,
                'saldo' => $nuevoSaldo,
                'pista' => $pista,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}
