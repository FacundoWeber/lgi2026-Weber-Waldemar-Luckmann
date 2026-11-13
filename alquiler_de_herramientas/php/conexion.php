<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "alquiler_herramientas";

$conn = mysqli_connect($servername, $username, $password, $dbname);

// chequear conexion

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
?>