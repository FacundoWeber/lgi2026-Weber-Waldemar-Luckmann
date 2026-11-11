<?php
require_once __DIR__ . '/../includes/auth.php';
exigirRol('administrador');
?>
<!DOCTYPE html>
<html lang="es"><head>
    <meta charset="UTF-8">
    <title>Administración</title><link rel="stylesheet" href="../css/estilos.css"></head>
    <body>
        <main class="panel"><h1>Panel de administración</h1>
            <p>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>.</p>
            <nav>
                <a href="usuarios.php">Gestionar vendedores</a>
                <a href="herramientas.php">Gestionar herramientas</a>
                <a href="../php/logout.php">Cerrar sesión</a>
            </nav>
            <p>Panel inicial para completar en equipo.</p>
        </main>
    </body>
</html>