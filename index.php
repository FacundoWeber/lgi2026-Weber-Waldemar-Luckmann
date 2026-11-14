<?php
// Pantalla de login. Si ya hay sesión, va directo al panel.

require_once __DIR__ . '/includes/auth.php';

$usuario = usuarioActual();

if ($usuario !== null) {
    $destinos = [
        'administrador' => '/admin/index.php',
        'vendedor' => '/vendedor/index.php',
        'cliente' => '/cliente/index.php',
    ];

    header('Location: ' . url($destinos[$usuario['rol']] ?? '/index.php'));
    exit;
}

$huboError = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Alquiler de herramientas</title>
    <link rel="stylesheet" href="<?= url('/css/Style.css') ?>">
</head>
<body>
<main class="inicio-sesion">
    <h1>Alquiler de herramientas</h1>
    <h2>Iniciar sesión</h2>

    <form action="<?= url('/php/login.php') ?>" method="POST" id="formulario-inicio" autocomplete="off">
        <label for="usuario">Usuario</label>
        <input id="usuario" name="usuario" autocomplete="username" autofocus required>

        <label for="password">Contraseña</label>
        <input id="password" name="contrasena" type="password" autocomplete="current-password" required>

        <button type="submit">Ingresar</button>
        <p id="mensaje" class="mensaje" role="alert"><?= $huboError ? 'No pudimos iniciar sesión. Revisá el usuario y la contraseña.' : '' ?></p>
    </form>

    <p class="demo">Usuarios de prueba: <strong>admin / 1234</strong> · <strong>vendedor / 1234</strong></p>
</main>
<script src="<?= url('/js/login.js') ?>"></script>
</body>
</html>
