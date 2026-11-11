<?php
declare(strict_types=1);
// Conserva la ruta administrativa heredada y la redirige al enrutador actual.
$_GET['page'] = 'admin';
require dirname(__DIR__, 2) . '/index.php';
