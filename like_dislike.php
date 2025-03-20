<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);
    $publicacionId = $data['publicacionId'];
    $tipo = $data['tipo'];

    // Validar que el tipo sea 'like' o 'dislike'
    if (!in_array($tipo, ['like', 'dislike'])) {
        echo json_encode(['success' => false, 'error' => 'Tipo no válido']);
        exit();
    }

    // Actualizar el contador en la base de datos
    $campo = $tipo === 'like' ? 'likes' : 'dislikes';
    $query = $conexion->prepare("UPDATE publicaciones SET $campo = $campo + 1 WHERE id = ?");
    $query->bind_param('i', $publicacionId);
    $query->execute();

    // Obtener el nuevo contador
    $query = $conexion->prepare("SELECT $campo FROM publicaciones WHERE id = ?");
    $query->bind_param('i', $publicacionId);
    $query->execute();
    $resultado = $query->get_result();
    $fila = $resultado->fetch_assoc();

    // Devolver la respuesta en formato JSON
    echo json_encode(['success' => true, 'newCount' => $fila[$campo]]);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>