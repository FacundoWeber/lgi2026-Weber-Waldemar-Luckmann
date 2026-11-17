<?php
<<<<<<< HEAD:alquiler_de_herramientas/config/database.php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); //en windows esto queda vacio '' en linux usa contrasena, voy a meter esto al gitignore
define('DB_NAME', 'alquiler_herramientas');

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('Error: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
=======
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
>>>>>>> origin/main:config/database.php
}
?>
