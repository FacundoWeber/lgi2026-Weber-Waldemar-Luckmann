<?php
// Sesión y URL base del proyecto.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se descuentan las subcarpetas para obtener la raíz, así funciona en
// cualquier carpeta donde se instale el proyecto.
const SUBCARPETAS_APP = ['/api', '/admin', '/vendedor', '/cliente', '/paginas', '/php', '/includes', '/config'];

if (!defined('URL_BASE')) {
    $directorio = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

    foreach (SUBCARPETAS_APP as $subcarpeta) {
        if (str_ends_with($directorio, $subcarpeta)) {
            $directorio = substr($directorio, 0, -strlen($subcarpeta));
            break;
        }
    }

    define('URL_BASE', $directorio);
}

// Arma una URL dentro del proyecto: url('/admin/index.php').
function url(string $ruta = ''): string
{
    return URL_BASE . '/' . ltrim($ruta, '/');
}
