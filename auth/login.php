<?php
declare(strict_types=1);

// Punto de entrada heredado: redirige al formulario de inicio de sesión central.
header('Location: ../index.php?page=login', true, 303);
exit;
