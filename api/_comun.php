<?php
// Funciones comunes de la API. Todas las respuestas tienen el mismo formato:
// { "correcto": true/false, "mensaje": "...", "datos": ... }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

function responder(bool $correcto, string $mensaje, array $datos = [], int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode([
        'correcto' => $correcto,
        'mensaje' => $mensaje,
        'datos' => $datos,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function leerCuerpo(): array
{
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos)) {
        responder(false, 'Los datos enviados no son válidos.', [], 400);
    }

    return $datos;
}

function usuarioApi(): array
{
    $usuario = usuarioActual();

    if ($usuario === null) {
        responder(false, 'Tu sesión expiró. Volvé a iniciar sesión.', [], 401);
    }

    return $usuario;
}

// El administrador puede usar cualquier endpoint.
function exigirRolApi(string $rol): array
{
    $usuario = usuarioApi();

    if ($usuario['rol'] === 'administrador' || $usuario['rol'] === $rol) {
        return $usuario;
    }

    responder(false, 'No tenés permiso para realizar esta operación.', [], 403);
}
