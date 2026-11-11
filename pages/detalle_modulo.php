<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista de detalle de módulo.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida obtiene el módulo y presenta su ruta de lecciones.
$paginaActual = 'detalle_modulo';
require __DIR__ . '/vistas.php';
