<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista de evaluación diagnóstica.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida carga la evaluación y sus preguntas publicadas.
$paginaActual = 'diagnostico';
require __DIR__ . '/vistas.php';
