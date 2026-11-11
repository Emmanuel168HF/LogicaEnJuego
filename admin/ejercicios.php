<?php
declare(strict_types=1);
// Atajo heredado al listado de ejercicios, protegido por la comprobación de rol.
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requerirAdministrador();
redirigir(rutaPagina('admin', ['section' => 'exercises']));
