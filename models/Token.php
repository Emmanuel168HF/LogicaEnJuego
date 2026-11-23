<?php
declare(strict_types=1);

// Administra el saldo y los movimientos de tokens de cada estudiante.
final class Token
{
    public function __construct(private readonly PDO $db)
    {
    }

    // Crea la billetera si el estudiante todavía no tiene una.
    private function asegurarBilletera(int $usuarioId): void
    {
        $consulta = $this->db->prepare(
            "INSERT IGNORE INTO user_wallets (user_id, balance)
             SELECT id, 0
             FROM users
             WHERE id = :user_id AND role = 'student'"
        );

        $consulta->execute(['user_id' => $usuarioId]);

        // Comprueba que exista una cuenta de estudiante.
        $verificar = $this->db->prepare(
            "SELECT id FROM users
             WHERE id = :user_id AND role = 'student'"
        );

        $verificar->execute(['user_id' => $usuarioId]);

        if (!$verificar->fetchColumn()) {
            throw new InvalidArgumentException(
                'No se encontró una cuenta de estudiante válida.'
            );
        }
    }

    // Consulta el saldo actual del estudiante.
    public function obtenerSaldo(int $usuarioId): int
    {
        $this->asegurarBilletera($usuarioId);

        $consulta = $this->db->prepare(
            'SELECT balance
             FROM user_wallets
             WHERE user_id = :user_id'
        );

        $consulta->execute(['user_id' => $usuarioId]);

        return (int) $consulta->fetchColumn();
    }

    // Agrega tokens al saldo.
    public function ganar(
        int $usuarioId,
        int $cantidad,
        string $descripcion,
        ?string $referencia = null
    ): int {
        return $this->registrarMovimiento(
            $usuarioId,
            $cantidad,
            'earn',
            $descripcion,
            $referencia
        );
    }

    // Agrega tokens e informa si la recompensa fue nueva.
    public function ganarConResultado(
        int $usuarioId,
        int $cantidad,
        string $descripcion,
        ?string $referencia = null
    ): array {
        return $this->registrarMovimientoConResultado(
            $usuarioId,
            $cantidad,
            'earn',
            $descripcion,
            $referencia
        );
    }

    // Descuenta tokens del saldo.
    public function gastar(
        int $usuarioId,
        int $cantidad,
        string $descripcion,
        ?string $referencia = null
    ): int {
        return $this->registrarMovimiento(
            $usuarioId,
            $cantidad,
            'spend',
            $descripcion,
            $referencia
        );
    }

    // Mantiene la compatibilidad con los métodos existentes.
    private function registrarMovimiento(
        int $usuarioId,
        int $cantidad,
        string $tipo,
        string $descripcion,
        ?string $referencia
    ): int {
        $resultado = $this->registrarMovimientoConResultado(
            $usuarioId,
            $cantidad,
            $tipo,
            $descripcion,
            $referencia
        );

        return $resultado['saldo'];
    }

    // Registra el movimiento e informa si fue una operación nueva.
    private function registrarMovimientoConResultado(
        int $usuarioId,
        int $cantidad,
        string $tipo,
        string $descripcion,
        ?string $referencia
    ): array {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException(
                'La cantidad de tokens debe ser mayor que cero.'
            );
        }

        if (!in_array($tipo, ['earn', 'spend'], true)) {
            throw new InvalidArgumentException(
                'El tipo de movimiento no es válido.'
            );
        }

        $descripcion = trim($descripcion);

        if ($descripcion === '' || mb_strlen($descripcion) > 255) {
            throw new InvalidArgumentException(
                'La descripción del movimiento no es válida.'
            );
        }

        if (
            $referencia !== null
            && ($referencia === '' || mb_strlen($referencia) > 150)
        ) {
            throw new InvalidArgumentException(
                'La referencia del movimiento no es válida.'
            );
        }

        try {
            $this->db->beginTransaction();

            $this->asegurarBilletera($usuarioId);

            // Bloquea la billetera para evitar operaciones simultáneas.
            $consulta = $this->db->prepare(
                'SELECT balance
                 FROM user_wallets
                 WHERE user_id = :user_id
                 FOR UPDATE'
            );

            $consulta->execute(['user_id' => $usuarioId]);
            $saldo = (int) $consulta->fetchColumn();

            // Evita registrar dos veces una misma operación.
            if ($referencia !== null) {
                $duplicado = $this->db->prepare(
                    'SELECT type, amount
                     FROM token_transactions
                     WHERE user_id = :user_id
                       AND reference_key = :reference_key
                     LIMIT 1'
                );

                $duplicado->execute([
                    'user_id' => $usuarioId,
                    'reference_key' => $referencia,
                ]);

                $movimientoExistente = $duplicado->fetch();

                if ($movimientoExistente) {
                    if (
                        $movimientoExistente['type'] !== $tipo
                        || (int) $movimientoExistente['amount'] !== $cantidad
                    ) {
                        throw new InvalidArgumentException(
                            'La referencia ya fue utilizada en otro movimiento.'
                        );
                    }

                    $this->db->commit();

                    return [
                        'saldo' => $saldo,
                        'registrado' => false,
                    ];
                }
            }

            // Impide que el saldo sea negativo.
            if ($tipo === 'spend' && $saldo < $cantidad) {
                throw new InvalidArgumentException(
                    'No tienes suficientes tokens para realizar esta operación.'
                );
            }

            $nuevoSaldo = $tipo === 'earn'
                ? $saldo + $cantidad
                : $saldo - $cantidad;

            if ($nuevoSaldo > 2147483647) {
                throw new InvalidArgumentException(
                    'El saldo de tokens alcanzó el límite permitido.'
                );
            }

            // Actualiza la billetera.
            $actualizar = $this->db->prepare(
                'UPDATE user_wallets
                 SET balance = :balance
                 WHERE user_id = :user_id'
            );

            $actualizar->execute([
                'balance' => $nuevoSaldo,
                'user_id' => $usuarioId,
            ]);

            // Guarda el movimiento en el historial.
            $insertar = $this->db->prepare(
                'INSERT INTO token_transactions
                    (user_id, type, amount, description, reference_key)
                 VALUES
                    (:user_id, :type, :amount, :description, :reference_key)'
            );

            $insertar->execute([
                'user_id' => $usuarioId,
                'type' => $tipo,
                'amount' => $cantidad,
                'description' => $descripcion,
                'reference_key' => $referencia,
            ]);

            $this->db->commit();

            return [
                'saldo' => $nuevoSaldo,
                'registrado' => true,
            ];

        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $error;
        }
    }

    // Obtiene los últimos movimientos del estudiante.
    public function obtenerHistorial(
        int $usuarioId,
        int $limite = 20
    ): array {
        $this->asegurarBilletera($usuarioId);

        $limite = max(1, min($limite, 100));

        $consulta = $this->db->prepare(
            'SELECT type, amount, description, created_at
             FROM token_transactions
             WHERE user_id = :user_id
             ORDER BY created_at DESC, id DESC
             LIMIT ' . $limite
        );

        $consulta->execute(['user_id' => $usuarioId]);

        return $consulta->fetchAll();
    }
}