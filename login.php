<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consulta para verificar credenciales
    $query = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
    $query->bind_param('ss', $usuario, $password);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 1) {
        $_SESSION['usuario'] = $usuario;
        header('Location: index.php'); // Cambiar la redirección a un dashboard
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form method="POST" action="">
        <label>Usuario:</label>
        <input type="text" name="usuario" required><br>
        <label>Contraseña:</label>
        <input type="password" name="password" required><br>
        <button type="submit" name="login">Iniciar Sesión</button>
    </form>
    <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
</body>
</html>
