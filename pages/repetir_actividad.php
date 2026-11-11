<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista de actividades repetibles.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida carga el historial y permite iniciar nuevos intentos.
$paginaActual = 'repetir_actividad';
require __DIR__ . '/vistas.php';
