<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la evaluación posterior al aprendizaje.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida verifica disponibilidad y presenta el formulario evaluativo.
$paginaActual = 'evaluacion_final';
require __DIR__ . '/vistas.php';
