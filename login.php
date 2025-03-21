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
    <title>Panel de Publicaciones</title>
    <style>
        /* Tus estilos CSS originales */
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
                                <button type="button" class="like-button" data-publicacion-id="<?php echo $publicacion['id']; ?>" data-tipo="like">Like (<?php echo $publicacion['likes']; ?>)</button>
                                <button type="button" class="dislike-button" data-publicacion-id="<?php echo $publicacion['id']; ?>" data-tipo="dislike">Dislike (<?php echo $publicacion['dislikes']; ?>)</button>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>

        <p><a href="login.php">Cerrar sesión</a></p>
    </div>

    <!-- Script para manejar actualizaciones en tiempo real y los botones -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para manejar los clicks en los botones de like/dislike
            document.querySelectorAll('.like-button, .dislike-button').forEach(button => {
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
                            this.textContent = `${tipo === 'like' ? 'Like' : 'Dislike'} (${data.newCount})`;
                        }
                    })
                    .catch(error => console.error('Error al procesar el like/dislike:', error));
                });
            });
        });
    </script>
</body>
</html>
