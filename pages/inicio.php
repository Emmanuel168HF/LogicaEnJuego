<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la página de bienvenida.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida presenta la portada y los accesos principales.
$paginaActual = 'inicio';
require __DIR__ . '/vistas.php';
