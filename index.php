<?php
session_start();
if (isset($_SESSION['usuario'])) {
    $rol = $_SESSION['usuario']['rol'];
    if ($rol === 'administrador') header('Location: admin/index.php');
    elseif ($rol === 'vendedor') header('Location: vendedor/index.php');
    else header('Location: cliente/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Alquiler de herramientas</title><link rel="stylesheet" href="css/style.css">
</head><body>
<main class="inicio-sesion">
<h1>Alquiler de herramientas</h1><h2>Iniciar sesión</h2>
<form action="php/login.php" method="POST" id="formulario-inicio">
<label for="usuario">Usuario</label><input id="usuario" name="usuario" required>
<label for="password">Contraseña</label><input id="password" name="contrasena" type="password" required>
<button type="submit">Ingresar</button><p id="mensaje" class="mensaje"></p>
</form>
<p class="demo">Prueba: admin / 1234 — vendedor / 1234</p>
</main><script src="js/login.js"></script></body></html>
