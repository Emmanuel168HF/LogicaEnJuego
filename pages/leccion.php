<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista de lectura de lección.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida obtiene el contenido y la navegación entre lecciones.
$paginaActual = 'leccion';
require __DIR__ . '/vistas.php';
