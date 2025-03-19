<?php
session_start();
require 'conexion.php';
require 'vendor/autoload.php'; // Cargar la librería WebSocket

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];

// Obtener la imagen del usuario actual
$queryUsuario = $conexion->prepare("SELECT imagen FROM usuarios WHERE usuario = ?");
$queryUsuario->bind_param('s', $usuario);
$queryUsuario->execute();
$resultadoUsuario = $queryUsuario->get_result();
$usuarioData = $resultadoUsuario->fetch_assoc();
$imagenUsuario = $usuarioData['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($usuarioData['imagen']) : 'default.jpg';

// Procesar la creación de publicaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenido'])) {
    $contenido = $_POST['contenido'];
    
    // Insertar la nueva publicación en la base de datos
    $query = $conexion->prepare("INSERT INTO publicaciones (usuario, contenido) VALUES (?, ?)");
    $query->bind_param('ss', $usuario, $contenido);
    $query->execute();

    // Enviar notificación al servidor WebSocket
    try {
        $client = new WebSocket\Client("ws://localhost:8080");
        $client->send(json_encode([
            'action' => 'new_post',
            'usuario' => $usuario,
            'contenido' => $contenido
        ]));
        $client->close();
    } catch (Exception $e) {
        echo "Error al enviar la notificación al servidor WebSocket: " . $e->getMessage();
    }
}

// Consultar todas las publicaciones con el nombre del usuario y su imagen
$query = $conexion->prepare("
    SELECT publicaciones.*, usuarios.usuario as nombre_usuario, usuarios.imagen as imagen_usuario 
    FROM publicaciones 
    JOIN usuarios ON publicaciones.usuario = usuarios.usuario 
    ORDER BY publicaciones.fecha DESC
");
$query->execute();
$resultado = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Publicaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        small {
            color: #666;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .publicacion {
            display: flex;
            align-items: center;
        }
        .publicacion img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .botones {
            margin-top: 10px;
        }
        .botones button {
            margin-right: 5px;
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .botones button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo htmlspecialchars($usuario); ?>!</h2>

        <!-- Formulario para crear una publicación -->
        <form method="POST" action="">
            <textarea name="contenido" rows="4" cols="50" placeholder="Escribe una publicación..." required></textarea><br>
            <button type="submit">Publicar</button>
        </form>

        <h3>Publicaciones:</h3>
        <ul>
            <?php while ($publicacion = $resultado->fetch_assoc()) : ?>
                <li>
                    <div class="publicacion">
                        <img src="<?php echo 'data:image/jpeg;base64,' . base64_encode($publicacion['imagen_usuario']); ?>" alt="Imagen de usuario">
                        <div>
                            <p><strong><?php echo htmlspecialchars($publicacion['nombre_usuario']); ?>:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($publicacion['contenido'])); ?></p>
                            <small>Publicado el: <?php echo $publicacion['fecha']; ?></small>
                            <div class="botones">
                                <button type="button" data-publicacionId="<?php echo $publicacion['id']; ?>" data-tipo="like">Like (<?php echo $publicacion['likes']; ?>)</button>
                                <button type="button" data-publicacionId="<?php echo $publicacion['id']; ?>" data-tipo="dislike">Dislike (<?php echo $publicacion['dislikes']; ?>)</button>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>

        <p><a href="login.php">Cerrar sesión</a></p>
    </div>

    <!-- Script para manejar actualizaciones en tiempo real -->
    <script>
        // Conectar al servidor WebSocket
        const socket = new WebSocket('ws://localhost:8080');

        socket.onmessage = function(event) {
            const data = JSON.parse(event.data);

            if (data.action === 'new_post') {
                // Crear un nuevo elemento para la publicación
                const publicacionesList = document.querySelector('ul');
                const nuevaPublicacion = document.createElement('li');
                nuevaPublicacion.innerHTML = `
                    <div class="publicacion">
                        <img src="default.jpg" alt="Imagen de usuario">
                        <div>
                            <p><strong>${data.usuario}:</strong></p>
                            <p>${data.contenido}</p>
                            <small>Publicado hace unos segundos</small>
                            <div class="botones">
                                <button type="button" data-publicacionId="0" data-tipo="like">Like (0)</button>
                                <button type="button" data-publicacionId="0" data-tipo="dislike">Dislike (0)</button>
                            </div>
                        </div>
                    </div>
                `;
                publicacionesList.prepend(nuevaPublicacion); // Agregar la publicación al inicio de la lista
            }
        };

        // Manejar errores de conexión
        socket.onerror = function(error) {
            console.error('Error en la conexión WebSocket:', error);
        };

        // Mostrar un mensaje cuando la conexión se cierre
        socket.onclose = function() {
            console.log('Conexión WebSocket cerrada.');
        };
    </script>
</body>
</html>