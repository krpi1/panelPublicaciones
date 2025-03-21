<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $publicacionId = $data['publicacionId'];
    $tipo = $data['tipo'];
    if (!in_array($tipo, ['like', 'dislike'])) {
        echo json_encode(['success' => false, 'error' => 'Tipo no válido']);
        exit();
    }

    $campo = $tipo === 'like' ? 'likes' : 'dislikes';
    $query = $conexion->prepare("UPDATE publicaciones SET $campo = $campo + 1 WHERE id = ?");
    $query->bind_param('i', $publicacionId);
    $query->execute();

    $query = $conexion->prepare("SELECT $campo FROM publicaciones WHERE id = ?");
    $query->bind_param('i', $publicacionId);
    $query->execute();
    $resultado = $query->get_result();
    $fila = $resultado->fetch_assoc();
    echo json_encode(['success' => true, 'newCount' => $fila[$campo]]);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
