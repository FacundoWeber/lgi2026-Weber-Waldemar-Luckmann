<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

function responder(bool $correcto, string $mensaje, array $datos = [], int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode([
        'correcto' => $correcto,
        'mensaje' => $mensaje,
        'datos' => $datos
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    responder(false, 'No tenés permiso para realizar esta operación.', [], 403);
}

try {
    $conexion = obtenerConexion();
    $accion = $_GET['accion'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar') {
        $buscar = trim($_GET['buscar'] ?? '');

        $consulta = $conexion->prepare(
            "SELECT h.id, h.nombre, h.categoria_id, c.nombre AS categoria,
                    h.precio_dia, h.stock, h.stock_minimo, h.activo
             FROM herramientas h
             LEFT JOIN categorias c ON c.id = h.categoria_id
             WHERE h.nombre LIKE :buscar
                OR c.nombre LIKE :buscar
             ORDER BY h.nombre"
        );
        $consulta->execute(['buscar' => '%' . $buscar . '%']);
        $herramientas = $consulta->fetchAll();

        $categorias = $conexion->query(
            "SELECT id, nombre FROM categorias ORDER BY nombre"
        )->fetchAll();

        responder(true, 'Listado obtenido.', [
            'herramientas' => $herramientas,
            'categorias' => $categorias
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!is_array($datos)) {
            responder(false, 'Los datos enviados no son válidos.', [], 400);
        }

        if ($accion === 'guardar') {
            $id = (int)($datos['id'] ?? 0);
            $nombre = trim($datos['nombre'] ?? '');
            $categoria = ($datos['categoria_id'] ?? '') === '' ? null : (int)$datos['categoria_id'];
            $precio = filter_var($datos['precio_dia'] ?? null, FILTER_VALIDATE_FLOAT);
            $stock = filter_var($datos['stock'] ?? null, FILTER_VALIDATE_INT);
            $stockMinimo = filter_var($datos['stock_minimo'] ?? null, FILTER_VALIDATE_INT);

            if ($nombre === '' || mb_strlen($nombre) > 100) {
                responder(false, 'Ingresá un nombre de hasta 100 caracteres.', [], 422);
            }
            if ($precio === false || $precio < 0) {
                responder(false, 'El precio debe ser un número igual o mayor que cero.', [], 422);
            }
            if ($stock === false || $stock < 0 || $stockMinimo === false || $stockMinimo < 0) {
                responder(false, 'El stock y el stock mínimo deben ser números enteros iguales o mayores que cero.', [], 422);
            }
            if ($categoria !== null) {
                $comprobarCategoria = $conexion->prepare("SELECT id FROM categorias WHERE id = ?");
                $comprobarCategoria->execute([$categoria]);
                if (!$comprobarCategoria->fetch()) {
                    responder(false, 'La categoría seleccionada no existe.', [], 422);
                }
            }

            if ($id > 0) {
                $consulta = $conexion->prepare(
                    "UPDATE herramientas
                     SET nombre = ?, categoria_id = ?, precio_dia = ?, stock = ?, stock_minimo = ?
                     WHERE id = ?"
                );
                $consulta->execute([$nombre, $categoria, $precio, $stock, $stockMinimo, $id]);
                responder(true, 'Herramienta modificada correctamente.');
            } else {
                $consulta = $conexion->prepare(
                    "INSERT INTO herramientas (nombre, categoria_id, precio_dia, stock, stock_minimo)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $consulta->execute([$nombre, $categoria, $precio, $stock, $stockMinimo]);
                responder(true, 'Herramienta agregada correctamente.');
            }
        }

        if ($accion === 'cambiar_estado') {
            $id = (int)($datos['id'] ?? 0);
            $activo = (int)($datos['activo'] ?? -1);

            if ($id <= 0 || !in_array($activo, [0, 1], true)) {
                responder(false, 'Los datos para cambiar el estado no son válidos.', [], 422);
            }

            $consulta = $conexion->prepare("UPDATE herramientas SET activo = ? WHERE id = ?");
            $consulta->execute([$activo, $id]);

            if ($consulta->rowCount() === 0) {
                $comprobar = $conexion->prepare("SELECT id FROM herramientas WHERE id = ?");
                $comprobar->execute([$id]);
                if (!$comprobar->fetch()) {
                    responder(false, 'No se encontró la herramienta.', [], 404);
                }
            }

            responder(true, $activo === 1 ? 'Herramienta activada.' : 'Herramienta desactivada.');
        }
    }

    responder(false, 'La acción solicitada no existe.', [], 404);

} catch (PDOException $error) {
    responder(false, 'Ocurrió un error al consultar la base de datos.', [], 500);
} catch (Throwable $error) {
    responder(false, 'No se pudo procesar la solicitud.', [], 500);
}
