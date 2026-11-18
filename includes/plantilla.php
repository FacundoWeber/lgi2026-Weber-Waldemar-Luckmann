<?php
// Encabezado, menú y pie que comparten todas las páginas.

require_once __DIR__ . '/auth.php';

// $hojas son hojas de estilo extra, por ejemplo /css/herramientas.css.
function mostrarEncabezado(string $titulo, array $hojas = []): void
{
    $usuario = usuarioActual();
    $esAdmin = $usuario !== null && $usuario['rol'] === 'administrador';
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($titulo) ?> · Alquiler de herramientas</title>
        <link rel="stylesheet" href="<?= url('/css/Style.css') ?>">
        <?php foreach ($hojas as $hoja): ?>
            <link rel="stylesheet" href="<?= url($hoja) ?>">
        <?php endforeach; ?>
    </head>
    <body>
    <header class="barra-superior">
        <a class="marca" href="<?= url('/index.php') ?>">Alquiler de herramientas</a>
        <?php if ($usuario !== null): ?>
            <nav class="menu">
                <?php if ($usuario['rol'] === 'cliente'): ?>
                    <a href="<?= url('/cliente/index.php') ?>">Mis alquileres</a>
                <?php else: ?>
                    <a href="<?= url($esAdmin ? '/admin/index.php' : '/vendedor/index.php') ?>">Panel</a>

                    <?php if ($esAdmin): ?>
                        <a href="<?= url('/admin/herramientas.php') ?>">Herramientas</a>
                        <a href="<?= url('/admin/gestionar_alquileres.php') ?>">Alquileres</a>
                        <a href="<?= url('/admin/informes.php') ?>">Informes</a>
                    <?php else: ?>
                        <a href="<?= url('/vendedor/alquileres.php') ?>">Alquileres</a>
                    <?php endif; ?>

                    <a href="<?= url('/vendedor/clientes.php') ?>">Clientes</a>
                <?php endif; ?>

                <span class="usuario">
                    <?= htmlspecialchars($usuario['nombre']) ?>
                    <small>(<?= htmlspecialchars($usuario['rol']) ?>)</small>
                </span>
                <a class="salir" href="<?= url('/php/logout.php') ?>">Cerrar sesión</a>
            </nav>
        <?php endif; ?>
    </header>
    <main class="contenido">
    <?php
}

// $scripts son archivos JS que se cargan al final, por ejemplo /js/herramientas.js.
function mostrarPie(array $scripts = []): void
{
    ?>
    </main>
    <?php foreach ($scripts as $script): ?>
        <script src="<?= url($script) ?>"></script>
    <?php endforeach; ?>
    </body>
    </html>
    <?php
}
