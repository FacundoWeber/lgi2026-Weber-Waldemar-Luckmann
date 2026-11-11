<?php
require_once __DIR__ . '/../includes/auth.php';
exigirRol('vendedor');
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Vendedor</title><link rel="stylesheet" href="../css/style.css"></head>
<body><main class="panel"><h1>Panel de vendedor</h1>
<p>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?>.</p>
<nav><a href="alquileres.php">Registrar alquiler</a><a href="clientes.php">Clientes</a><a href="../php/logout.php">Cerrar sesión</a></nav>
<p>Panel inicial para completar en equipo.</p></main></body></html>
