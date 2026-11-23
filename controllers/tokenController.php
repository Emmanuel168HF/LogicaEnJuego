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
            $usuario = requerirUsuario();
            $actividadId = (int) ($_POST['activity_id'] ?? 0);
            if ($actividadId <= 0) {
                throw new InvalidArgumentException('Actividad no válida.');
            }

            $actividad = (new Ejercicio($this->db))->buscar($actividadId);
            if (!$actividad) {
                throw new InvalidArgumentException('No se encontró el ejercicio.');
            }

            // Verifica saldo antes de llamar a la IA
            $costo = 12;
            if ($this->consultarSaldo() < $costo) {
                throw new RuntimeException('No tienes suficientes tokens para solicitar una pista.');
            }

            $perfil = (new Usuario($this->db))->perfil((int) $usuario['id']);
            $edad = (int) ($perfil['age'] ?? 12);

            $ia = new ServiceIA();
            $respuestaIA = $ia->interactuar('pista', $edad, '', [
                'titulo' => $actividad['title'] ?? '',
                'descripcion' => $actividad['description'] ?? '',
                'items' => $actividad['items'] ?? [],
            ]);

            // Descuenta los 12 tokens una vez lista la pista
            $nuevoSaldo = $this->solicitarPista(
                $costo,
                'Pista IA en: ' . ($actividad['title'] ?? 'Ejercicio #' . $actividadId),
                'hint_activity_' . $actividadId . '_' . time()
            );

            echo json_encode([
                'ok' => true,
                'saldo' => $nuevoSaldo,
                'pista' => $respuestaIA['texto'],
                'origen' => $respuestaIA['origen'],
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
