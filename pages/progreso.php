<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna el resumen de progreso del estudiante.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida protege la ruta y consulta estadísticas e historial.
$paginaActual = 'progreso';
require __DIR__ . '/vistas.php';
