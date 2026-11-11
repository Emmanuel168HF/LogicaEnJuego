<?php
declare(strict_types=1);

// Proporciona el inicio común del documento y permite definir el título desde cada página.
$titulo = $titulo ?? 'Lógica en juego';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar($titulo) ?> | Lógica en juego</title>
</head>
<body>
