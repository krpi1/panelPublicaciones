<?php
session_start();
require 'conexion.php';
require 'vendor/autoload.php'; 
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$queryUsuario = $conexion->prepare("SELECT imagen FROM usuarios WHERE usuario = ?");
$queryUsuario->bind_param('s', $usuario);
$queryUsuario->execute();
$resultadoUsuario = $queryUsuario->get_result();
$usuarioData = $resultadoUsuario->fetch_assoc();
$imagenUsuario = $usuarioData['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($usuarioData['imagen']) : 'default.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenido'])) {
    $contenido = $_POST['contenido'];
    $query = $conexion->prepare("INSERT INTO publicaciones (usuario, contenido) VALUES (?, ?)");
    $query->bind_param('ss', $usuario, $contenido);
    $query->execute();
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
    font-family: 'Arial', sans-serif;
    background-image: url('img/tres.png'); /* Ruta de la imagen */
    background-size: cover; /* Hace que la imagen cubra todo el fondo */
    background-position: center center; /* Centra la imagen */
    background-attachment: fixed;
    margin: 0;
    padding: 20px;
}
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            color: #495057;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin-bottom: 10px;
            resize: vertical;
        }
        button {
            padding: 10px 20px;
            background-color:rgb(227, 184, 223);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color:rgb(177, 145, 164);
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .publicacion {
            display: flex;
            align-items: flex-start;
        }
        .publicacion img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .publicacion-content {
            flex-grow: 1;
        }
        .publicacion-content p {
            margin: 0;
            color: #212529;
        }
        .publicacion-content small {
            color: #6c757d;
        }
        .botones {
            margin-top: 10px;
        }
        .botones button {
            margin-right: 5px;
            padding: 5px 10px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .botones button:hover {
            background-color: #5a6268;
        }
        .botones button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bienvenido, <?php echo htmlspecialchars($usuario); ?>!</h2>
        <form method="POST" action="">
            <textarea name="contenido" rows="4" placeholder="Escribe una publicación..." required></textarea><br>
            <button type="submit">Publicar</button>
        </form>

        <h3>Publicaciones:</h3>
        <ul>
            <?php while ($publicacion = $resultado->fetch_assoc()) : ?>
                <li>
                    <div class="publicacion">
                        <img src="<?php echo 'data:image/jpeg;base64,' . base64_encode($publicacion['imagen_usuario']); ?>" alt="Imagen de usuario">
                        <div class="publicacion-content">
                            <p><strong><?php echo htmlspecialchars($publicacion['nombre_usuario']); ?>:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($publicacion['contenido'])); ?></p>
                            <small>Publicado el: <?php echo $publicacion['fecha']; ?></small>
                            <div class="botones">
                                <button type="button" class="like-button" data-publicacionid="<?php echo $publicacion['id']; ?>" data-tipo="like">Like (<?php echo $publicacion['likes']; ?>)</button>
                                <button type="button" class="dislike-button" data-publicacionid="<?php echo $publicacion['id']; ?>" data-tipo="dislike">Dislike (<?php echo $publicacion['dislikes']; ?>)</button>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>

        <p><a href="login.php">Cerrar sesión</a></p>
    </div>
    <script>
        const socket = new WebSocket('ws://localhost:8080');

        socket.onmessage = function(event) {
            const data = JSON.parse(event.data);

            if (data.action === 'new_post') {
                const publicacionesList = document.querySelector('ul');
                const nuevaPublicacion = document.createElement('li');
                nuevaPublicacion.innerHTML = `
                    <div class="publicacion">
                        <img src="default.jpg" alt="Imagen de usuario">
                        <div class="publicacion-content">
                            <p><strong>${data.usuario}:</strong></p>
                            <p>${data.contenido}</p>
                            <small>Publicado hace unos segundos</small>
                            <div class="botones">
                                <button type="button" class="like-button" data-publicacionid="0" data-tipo="like">Like (0)</button>
                                <button type="button" class="dislike-button" data-publicacionid="0" data-tipo="dislike">Dislike (0)</button>
                            </div>
                        </div>
                    </div>
                `;
                publicacionesList.prepend(nuevaPublicacion); 
            }
        };

        socket.onerror = function(error) {
            console.error('Error en la conexión WebSocket:', error);
        };
        socket.onclose = function() {
            console.log('Conexión WebSocket cerrada.');
        };
        document.addEventListener('click', function(e) {
            if (e.target && (e.target.classList.contains('like-button') || e.target.classList.contains('dislike-button'))) {
                const publicacionId = e.target.getAttribute('data-publicacionid');
                const tipo = e.target.getAttribute('data-tipo'); 
                const likeButton = e.target.closest('.botones').querySelector('.like-button');
                const dislikeButton = e.target.closest('.botones').querySelector('.dislike-button');

                fetch('like_dislike.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ publicacionId, tipo })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        e.target.textContent = `${tipo === 'like' ? 'Like' : 'Dislike'} (${data.newCount})`;

                        if (tipo === 'like') {
                            dislikeButton.disabled = true;
                            likeButton.disabled = false;
                        } else {
                            likeButton.disabled = true;
                            dislikeButton.disabled = false;
                        }
                    }
                })
                .catch(error => console.error('Error al procesar la solicitud:', error));
            }
        });
    </script>
</body>
</html>