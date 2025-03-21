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
<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $query = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
    $query->bind_param('ss', $usuario, $password);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 1) {
        $_SESSION['usuario'] = $usuario;
        header('Location: index.php'); 
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
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }
        .container {
            display: flex;
            width: 100%;
        }
        .left-panel {
            background-color: #f7f7f7;
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .left-panel h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .left-panel p {
            font-size: 14px;
            color: #555;
        }
        .left-panel form {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 300px;
        }
        .left-panel input {
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-bottom: 2px solid #ccc;
            background-color: transparent;
            font-size: 16px;
            outline: none;
        }
        .left-panel input::placeholder {
            color: #aaa;
        }
        .left-panel input:focus {
            border-bottom: 2px solid black;
        }
        .left-panel button {
            padding: 10px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .left-panel a {
            color: #007BFF;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
        }
        .left-panel a:hover {
            text-decoration: underline;
        }
        .right-panel {
            flex: 1;
            background-image: url(img/glow.jpg);
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .right-panel .info-box {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border-radius: 10px;
            max-width: 80%;
        }
        .right-panel .info-box h4 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .right-panel .info-box p {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h2>Login</h2>
            <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
            <form method="POST" action="">
                <input type="text" name="usuario" placeholder="Usuario" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login">Iniciar Sesión</button>
            </form>
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
        <div class="right-panel">
        </div>
    </div>
</body>
</html>