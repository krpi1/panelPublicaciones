<?php
require 'conexion.php'; // Incluir la conexión a la base de datos

// Procesar el registro del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    // Procesar la imagen del usuario
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']); // Guardar imagen en formato binario
    }

    // Verificar si el usuario ya existe
    $query_verificacion = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $query_verificacion->bind_param('s', $usuario);
    $query_verificacion->execute();
    $resultado_verificacion = $query_verificacion->get_result();

    if ($resultado_verificacion->num_rows > 0) {
        $error = "El nombre de usuario ya está registrado. Elige otro.";
    } else {
        // Insertar nuevo usuario
        $query = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, usuario, password, imagen) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param('sssss', $nombre, $apellido, $usuario, $password, $imagen);
        
        if ($query->execute()) {
            // Redirigir al login después del registro exitoso
            header('Location: login.php');
            exit();
        } else {
            $error = "Error al registrar el usuario: " . $query->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        <?php if (isset($error)) : ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="registro.php" enctype="multipart/form-data">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" required>
            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" required>
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" required>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" required>
            <label for="imagen">Imagen de Perfil:</label>
            <input type="file" name="imagen" accept="image/*">
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>
</body>
</html>
