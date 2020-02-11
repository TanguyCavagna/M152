<?php
/**
 * @filesource deletePost.php
 * @brief Permet de supprimer un poste
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-11
 * @version 1.0.0
 */

header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\PostController;

$id = filter_input(INPUT_POST, 'id');
$postController = new PostController();

if (empty($id)) {
    http_response_code(422);
    echo json_encode([
        'errors' => 'L\'id du post est vide.'
    ]);
    exit();
}

try {
    $id = intval($id);
} catch (\Exception $e) {
    http_response_code(415);
    echo json_encode([
        'errors' => 'L\'id doit strictement être un entier.'
    ]);
    exit();
}

if ($postController->Delete($id)) {
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