<?php
declare(strict_types=1);

// Bloquea el cierre de sesión por GET; la salida debe enviarse desde el formulario protegido.
http_response_code(405);
header('Allow: POST');
echo 'Cierra la sesión desde el formulario protegido de la aplicación.';
