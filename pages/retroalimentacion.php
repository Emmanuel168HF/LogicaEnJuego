<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista de retroalimentación.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida presenta los aciertos y explicaciones del intento.
$paginaActual = 'retroalimentacion';
require __DIR__ . '/vistas.php';
