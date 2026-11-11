<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista del cuestionario.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida usa esta clave para cargar preguntas y mostrar el formulario.
$paginaActual = 'cuestionario';
require __DIR__ . '/vistas.php';
