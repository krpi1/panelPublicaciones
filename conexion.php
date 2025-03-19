<?php
$host = 'localhost'; // Host de la base de datos
$dbname = 'panel';   // Nombre de la base de datos
$user = 'root';      // Usuario de la base de datos
$pass = '';          // Contraseña de la base de datos

$conexion = new mysqli($host, $user, $pass, $dbname);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>