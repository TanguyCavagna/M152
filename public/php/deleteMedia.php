<?php
header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\MediaController;

$mediaName = filter_input(INPUT_POST, 'mediaName', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$mediaController = new MediaController();

if (empty($mediaName)) {
    http_response_code(422);
    echo json_encode([
        'errors' => 'Le nom du media est vide.'
    ]);
    exit();
}

if ($mediaController->DeleteByName($mediaName)) {
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