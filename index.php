<?php
session_start();
require 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];

// Obtener la imagen del usuario
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
}

// Consultar las publicaciones del usuario
$query = $conexion->prepare("SELECT * FROM publicaciones WHERE usuario = ? ORDER BY fecha DESC");
$query->bind_param('s', $usuario);
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

        <h3>Mis publicaciones:</h3>
        <?php if ($resultado->num_rows > 0) : ?>
            <ul>
                <?php while ($publicacion = $resultado->fetch_assoc()) : ?>
                    <li>
                        <div class="publicacion">
                            <img src="<?php echo $imagenUsuario; ?>" alt="Imagen de usuario">
                            <div>
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
        <?php else : ?>
            <p>No has realizado publicaciones aún.</p>
        <?php endif; ?>

        <p><a href="login.php">Cerrar sesión</a></p>
    </div>

    <!-- Script para manejar likes y dislikes -->
    <script>
        document.querySelectorAll('.botones button').forEach(button => {
            button.addEventListener('click', function() {
                const publicacionId = this.dataset.publicacionId;
                const tipo = this.dataset.tipo; // 'like' o 'dislike'

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
                        // Actualizar el contador en la interfaz
                        this.textContent = `${tipo === 'like' ? 'Like' : 'Dislike'} (${data.newCount})`;
                    }
                });
            });
        });
    </script>
</body>
</html>