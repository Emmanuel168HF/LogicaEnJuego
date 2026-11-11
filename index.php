<?php
declare(strict_types=1);

// Enrutador de entrada: inicializa dependencias, procesa acciones POST y renderiza páginas autorizadas.
require_once __DIR__ . '/includes/bootstrap.php';

// Asocia identificadores de página con los controladores de vista correspondientes.
$rutas = [
    'inicio' => __DIR__ . '/pages/vistas.php',
    'menu' => __DIR__ . '/pages/vistas.php',
    'modulos' => __DIR__ . '/pages/vistas.php',
    'detalle_modulo' => __DIR__ . '/pages/vistas.php',
    'leccion' => __DIR__ . '/pages/vistas.php',
    'cuestionario' => __DIR__ . '/pages/vistas.php',
    'ejercicio' => __DIR__ . '/pages/vistas.php',
    'retroalimentacion' => __DIR__ . '/pages/vistas.php',
    'resultados' => __DIR__ . '/pages/vistas.php',
    'perfil' => __DIR__ . '/pages/vistas.php',
    'progreso' => __DIR__ . '/pages/vistas.php',
    'repetir_actividad' => __DIR__ . '/pages/vistas.php',
    'diagnostico' => __DIR__ . '/pages/vistas.php',
    'evaluacion_final' => __DIR__ . '/pages/vistas.php',
    'login' => __DIR__ . '/pages/login.php',
    'registro' => __DIR__ . '/pages/registro.php',
    'admin' => __DIR__ . '/admin/index.php',
];

// Rechaza nombres de página no permitidos antes de incluir cualquier vista.
$pagina = $_GET['page'] ?? 'inicio';
if (!is_string($pagina) || !isset($rutas[$pagina])) {
    http_response_code(404);
    require __DIR__ . '/pages/404.php';
    exit;
}

// Todas las mutaciones pasan por CSRF y por el despachador central de acciones.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        validarCsrf();
        $accion = $_POST['action'] ?? '';
        if (!is_string($accion)) {
            throw new InvalidArgumentException('La solicitud no es válida.');
        }
        $db = obtenerConexion();
        $usuario = new UsuarioController($db);
        $modulo = new ModuloController($db);
        $leccion = new LeccionController($db);
        $cuestionario = new CuestionarioController($db);
        $ejercicio = new EjercicioController($db);
        $progreso = new ProgresoController($db);
        $admin = new AdminController($db);

        // Instancia los controladores para dirigir la acción validada al componente responsable.
        switch ($accion) {
            case 'register': $usuario->registrar(); break;
            case 'login': $usuario->iniciarSesion(); break;
            case 'save_profile': $usuario->guardarPerfil(); break;
            case 'save_avatar': $usuario->guardarAvatar(); break;
            case 'change_password': $usuario->cambiarContrasena(); break;
            case 'session_heartbeat': $usuario->registrarActividad(); break;
            case 'logout': $usuario->cerrarSesion(); break;
            case 'save_module': $modulo->guardar(); break;
            case 'archive_module': $modulo->archivar(); break;
            case 'save_lesson': $leccion->guardar(); break;
            case 'complete_lesson': $leccion->completar(); break;
            case 'delete_lesson': $leccion->eliminar(); break;
            case 'save_question': $cuestionario->guardarPregunta(); break;
            case 'delete_question': $cuestionario->eliminarPregunta(); break;
            case 'save_quiz': $admin->guardarCuestionario(); break;
            case 'submit_quiz': $cuestionario->enviar(); break;
            case 'save_exercise': $ejercicio->guardar(); break;
            case 'save_exercise_item': $ejercicio->guardarItem(); break;
            case 'submit_exercise': $ejercicio->enviar(); break;
            case 'submit_evaluation': $progreso->evaluacion(); break;
            case 'save_evaluation_question': $progreso->guardarPreguntaEvaluacion(); break;
            case 'delete_evaluation_question': $progreso->eliminarPreguntaEvaluacion(); break;
            case 'repeat_activity': $progreso->repetir(); break;
            case 'set_activity_publication': $admin->publicarActividad(); break;
            case 'delete_activity': $admin->eliminarActividad(); break;
            case 'delete_exercise_item': $admin->eliminarItemEjercicio(); break;
            default: throw new InvalidArgumentException('No se reconoce la acción solicitada.');
        }
    } catch (Throwable $error) {
        // Registra detalles internos y muestra al usuario un mensaje controlado con retorno seguro.
        if (!$error instanceof InvalidArgumentException && !$error instanceof RuntimeException) {
            error_log('Error al procesar solicitud: ' . $error->getMessage());
            $mensaje = 'No se pudo completar la operación. Revisa la configuración y vuelve a intentarlo.';
        } else {
            $mensaje = $error->getMessage();
        }
        if (http_response_code() < 400) {
            http_response_code(400);
        }
        mensajeFlash('error', $mensaje);
        $volver = $_POST['return_page'] ?? $pagina;
        if (!is_string($volver) || !isset($rutas[$volver])) {
            $volver = 'inicio';
        }
        redirigir(rutaPagina($volver));
    }
}

// Aplica los permisos necesarios antes de renderizar cada página solicitada.
try {
    if (in_array($pagina, ['perfil', 'progreso', 'repetir_actividad', 'evaluacion_final'], true)) {
        requerirUsuario();
    }
    if ($pagina === 'admin') {
        requerirAdministrador();
    }
    $paginaActual = $pagina;
    require $rutas[$pagina];
} catch (Throwable $error) {
    // Evita exponer excepciones internas y presenta la vista de error adecuada.
    if (!$error instanceof InvalidArgumentException && !$error instanceof RuntimeException) {
        error_log('Error al renderizar página: ' . $error->getMessage());
        $mensaje = 'No se pudo cargar esta página. Verifica la conexión a la base de datos.';
        http_response_code(500);
    } else {
        $mensaje = $error->getMessage();
    }
    require __DIR__ . '/pages/error.php';
}
