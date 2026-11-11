<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista de resultados de actividad.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida muestra la puntuación y el detalle de las respuestas.
$paginaActual = 'resultados';
require __DIR__ . '/vistas.php';
