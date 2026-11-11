<?php
declare(strict_types=1);
?>
<!-- Enlaces comunes para navegar por las áreas principales del sitio. -->
<nav aria-label="Navegación principal">
    <a href="<?= escapar(rutaPagina('inicio')) ?>">Inicio</a>
    <a href="<?= escapar(rutaPagina('menu')) ?>">Menú</a>
    <a href="<?= escapar(rutaPagina('modulos')) ?>">Módulos</a>
    <a href="<?= escapar(rutaPagina('progreso')) ?>">Progreso</a>
</nav>
