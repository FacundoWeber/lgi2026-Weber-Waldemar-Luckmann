<?php
// Control de acceso por sesión y por rol.

require_once __DIR__ . '/config.php';

function usuarioActual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigirSesion(): array
{
    $usuario = usuarioActual();

    if ($usuario === null) {
        header('Location: ' . url('/index.php'));
        exit;
    }

    return $usuario;
}

// El administrador puede entrar a cualquier pantalla.
function exigirRol(string $rol): array
{
    $usuario = exigirSesion();

    if ($usuario['rol'] === 'administrador' || $usuario['rol'] === $rol) {
        return $usuario;
    }

    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Sin permiso</title>
        <link rel="stylesheet" href="<?= url('/css/Style.css') ?>">
    </head>
    <body>
        <main class="contenido">
            <section class="panel">
                <h1>No tenés permiso</h1>
                <p>Tu rol (<?= htmlspecialchars($usuario['rol']) ?>) no puede abrir esta página.</p>
                <p><a class="boton" href="<?= url('/index.php') ?>">Volver al inicio</a></p>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}
