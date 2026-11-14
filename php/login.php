<?php
// Revisa el usuario y la contraseña, y guarda la sesión.

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/index.php'));
    exit;
}

// trim() porque al copiar y pegar suelen quedar espacios.
$usuario = trim((string) ($_POST['usuario'] ?? ''));
$contrasena = trim((string) ($_POST['contrasena'] ?? ''));

if ($usuario === '' || $contrasena === '') {
    header('Location: ' . url('/index.php?error=login'));
    exit;
}

try {
    $conexion = obtenerConexion();
    $consulta = $conexion->prepare(
        'SELECT id, nombre, usuario, contrasena, rol FROM usuarios WHERE usuario = ? AND activo = 1'
    );
    $consulta->execute([$usuario]);
    $datos = $consulta->fetch();

    if ($datos && password_verify($contrasena, $datos['contrasena'])) {
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id' => (int) $datos['id'],
            'nombre' => $datos['nombre'],
            'usuario' => $datos['usuario'],
            'rol' => $datos['rol'],
        ];

        $destinos = [
            'administrador' => '/admin/index.php',
            'vendedor' => '/vendedor/index.php',
            'cliente' => '/cliente/index.php',
        ];

        header('Location: ' . url($destinos[$datos['rol']] ?? '/index.php'));
        exit;
    }
} catch (PDOException $error) {
    error_log('Login: ' . $error->getMessage());
}

header('Location: ' . url('/index.php?error=login'));
exit;
