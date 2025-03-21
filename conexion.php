<?php
$host = 'localhost'; 
$dbname = 'panel';  
$user = 'root';     
$pass = '';          

$conexion = new mysqli($host, $user, $pass, $dbname);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>