<?php
// Conexión a la base. Todos los archivos la piden desde acá.

const SERVIDOR_BD = '127.0.0.1';
const NOMBRE_BD = 'alquiler_herramientas';
const USUARIO_BD = 'root';
const CLAVE_BD = '';

function obtenerConexion(): PDO
{
    $dsn = 'mysql:host=' . SERVIDOR_BD . ';dbname=' . NOMBRE_BD . ';charset=utf8mb4';

    return new PDO($dsn, USUARIO_BD, CLAVE_BD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
