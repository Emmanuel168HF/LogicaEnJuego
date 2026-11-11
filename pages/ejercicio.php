<?php
declare(strict_types=1);
// Inicializa los servicios comunes y asigna la vista de ejercicio interactivo.
require_once __DIR__ . '/../includes/bootstrap.php';
// La plantilla compartida prepara la consigna y las opciones de respuesta.
$paginaActual = 'ejercicio';
require __DIR__ . '/vistas.php';
