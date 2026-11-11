<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php'); exit;
}
$usuario = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

try {
    $pdo = obtenerConexion();
    $consulta = $pdo->prepare('SELECT id,nombre,usuario,contrasena,rol FROM usuarios WHERE usuario=:usuario AND activo=1');
    $consulta->execute(['usuario' => $usuario]);
    $datos = $consulta->fetch();

    if ($datos && password_verify($contrasena, $datos['contrasena'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => $datos['id'], 'nombre' => $datos['nombre'],
            'usuario' => $datos['usuario'], 'rol' => $datos['rol']
        ];
        if ($datos['rol'] === 'administrador') header('Location: ../admin/index.php');
        elseif ($datos['rol'] === 'vendedor') header('Location: ../vendedor/index.php');
        else header('Location: ../cliente/index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
header('Location: ../index.php?error=login'); exit;
