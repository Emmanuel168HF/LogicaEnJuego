<?php
declare(strict_types=1);

// Carga utilidades, conexión y componentes de la aplicación antes de procesar cada solicitud.
require_once __DIR__ . '/funciones.php';
require_once dirname(__DIR__) . '/config/conexion.php';

// Registra modelos y controladores en el orden requerido por sus dependencias.
foreach ([
    'Usuario', 'Modulo', 'Leccion', 'Pregunta', 'Ejercicio', 'Progreso', 'Evaluacion',
    'controllers/usuarioController.php',
    'controllers/moduloController.php',
    'controllers/leccionController.php',
    'controllers/cuestionarioController.php',
    'controllers/ejercicioController.php',
    'controllers/progresoController.php',
    'controllers/adminController.php',
] as $archivo) {
    $ruta = str_contains($archivo, '/') ? dirname(__DIR__) . '/' . $archivo : dirname(__DIR__) . '/models/' . $archivo . '.php';
    require_once $ruta;
}

// Inicia una sesión con medidas seguras y evita exponer errores internos al navegador.
iniciarSesionSegura();
ini_set('display_errors', '0');
ini_set('log_errors', '1');
