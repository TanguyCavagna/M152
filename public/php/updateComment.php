<?php
header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\PostController;

$id = filter_input(INPUT_POST, 'id');
$comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$mediaController = new PostController();

if (empty($comment)) {
    http_response_code(422);
    echo json_encode([
        'errors' => 'Le commentaire du poste est vide.'
    ]);
    exit();
}

if ($mediaController->UpdateComment($id, $comment)) {
    http_response_code(200);
    echo json_encode([
        'errors' => [],
    ]);
    exit();
} else {
    http_response_code(200);
    echo json_encode([
        'errors' => [
            'Une erreur est survenue lors de la suppression du post.'
        ]
    ]);
    exit();
}