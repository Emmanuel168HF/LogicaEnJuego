<?php
declare(strict_types=1);

// Expone acciones administrativas para crear, actualizar y archivar módulos.
final class ModuloController
{
    // Conserva la conexión usada por el modelo de módulos.
    public function __construct(private readonly PDO $db)
    {
    }

    public function guardar(): void
    {
        // Verifica permisos y delega al modelo la validación y persistencia del módulo.
        requerirAdministrador();
        (new Modulo($this->db))->guardar($_POST, enteroOpcionalPost('id'));
        mensajeFlash('ok', 'El módulo se guardó correctamente.');
        redirigir(rutaPagina('admin', ['section' => 'modules']));
    }

    public function archivar(): void
    {
        // Archiva el módulo mediante el modelo para retirarlo del catálogo público.
        requerirAdministrador();
        (new Modulo($this->db))->archivar(enteroPost('id'));
        mensajeFlash('ok', 'El módulo se archivó y dejó de aparecer en el catálogo.');
        redirigir(rutaPagina('admin', ['section' => 'modules']));
    }
}
