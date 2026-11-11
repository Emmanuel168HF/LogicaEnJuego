<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna el catálogo de módulos.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida lista los módulos y aplica el filtro solicitado.
$paginaActual = 'modulos';
require __DIR__ . '/vistas.php';
