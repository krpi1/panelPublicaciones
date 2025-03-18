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
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .container {
            width: 80%;
            margin: 20px auto;
        }
        .publicar-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .post {
            background-color: white;
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .post-header {
            display: flex;
            align-items: center;
        }
        .post-header img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }
        .post-title {
            font-weight: bold;
            font-size: 18px;
        }
        .post-content {
            margin: 10px 0;
        }
        .post-footer {
            text-align: right;
        }
        .like-btn, .dislike-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            margin-left: 5px;
        }
        .dislike-btn {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Botón para publicar -->
    <button class="publicar-btn">Publicar</button>

    <!-- Publicaciones -->
    <?php
    // Simulamos datos de publicaciones
    $publicaciones = [
        [
            'titulo' => 'Mi primer post',
            'contenido' => 'Este es el contenido de mi primera publicación.',
            'usuario' => 'Juan Pérez',
            'foto' => 'https://via.placeholder.com/50'
        ],
        [
            'titulo' => 'Mi segundo post',
            'contenido' => 'Aquí comparto algo más interesante.',
            'usuario' => 'Ana Gómez',
            'foto' => 'https://via.placeholder.com/50'
        ],
        [
            'titulo' => 'Una publicación divertida',
            'contenido' => 'Este post está lleno de humor y alegría.',
            'usuario' => 'Carlos López',
            'foto' => 'https://via.placeholder.com/50'
        ]
    ];

    // Mostrar publicaciones
    foreach ($publicaciones as $post) {
        echo '<div class="post">';
        echo '<div class="post-header">';
        echo '<img src="' . $post['foto'] . '" alt="Foto de usuario">';
        echo '<div><span class="post-title">' . $post['titulo'] . '</span><br>';
        echo '<span>Usuario: ' . $post['usuario'] . '</span></div>';
        echo '</div>';
        echo '<div class="post-content">' . $post['contenido'] . '</div>';
        echo '<div class="post-footer">';
        echo '<button class="like-btn">Me gusta</button>';
        echo '<button class="dislike-btn">No me gusta</button>';
        echo '</div>';
        echo '</div>';
    }
    ?>
</div>

</body>
</html>