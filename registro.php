<?php
require 'conexion.php'; 

$error = '';
$usuarioGenerado = '';
$passwordGenerado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    
    $usuarioGenerado = strtolower($nombre . substr($apellido, 0, 1) . rand(1, 9));
    $passwordGenerado = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']); 
    }

    $query_verificacion = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $query_verificacion->bind_param('s', $usuarioGenerado);
    $query_verificacion->execute();
    $resultado_verificacion = $query_verificacion->get_result();

    if ($resultado_verificacion->num_rows > 0) {
        $error = "El nombre de usuario ya está registrado. Elige otro.";
    } else {
        $query = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, usuario, password, imagen) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param('sssss', $nombre, $apellido, $usuarioGenerado, $passwordGenerado, $imagen);
        
        if ($query->execute()) {
            // Si se registra correctamente, no redirigir, solo mostrar los datos generados
            // header('Location: login.php');
            // exit();
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
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4;
        }
        .container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: 90vh;
            border-radius: 20px;
            overflow: hidden; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .left-panel {
            flex: 1;
            background-image: url('img/uno.jpg');
            background-size: cover;
            background-position: center;
        }
        .right-panel {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
            text-align: left;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
        form {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 400px;
        }
        label {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="file"] {
            padding: 10px;
            font-size: 16px;
            border: none;
            border-bottom: 2px solid #ddd;
            background-color: transparent;
            margin-bottom: 20px;
            outline: none;
        }
        input[type="text"]:focus,
        input[type="file"]:focus {
            border-bottom: 2px solid #000;
        }
        button {
            padding: 12px;
            font-size: 16px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #333;
        }
        .form-footer {
            margin-top: 20px;
            text-align: left;
        }
        a {
            color: #6a50b6;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel"></div>
        <div class="right-panel">
            <h2>Registro</h2>
            <?php if (isset($error)) : ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" placeholder="Ingresa tu nombre" required>
                
                <label for="apellido">Apellido</label>
                <input type="text" name="apellido" placeholder="Ingresa tu apellido" required>
                
                <label for="imagen">Imagen de Perfil</label>
                <input type="file" name="imagen" accept="image/*">
                
                <button type="submit">Registrarse</button>
            </form>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
                <div class="form-footer">
                    <p><strong>Tu usuario:</strong> <?php echo $usuarioGenerado; ?></p>
                    <p><strong>Tu contraseña:</strong> <?php echo $passwordGenerado; ?></p>
                    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
