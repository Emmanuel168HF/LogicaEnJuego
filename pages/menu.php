<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna el panel de navegación del estudiante.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida compone el menú, el resumen y las rutas de aprendizaje.
$paginaActual = 'menu';
require __DIR__ . '/vistas.php';
