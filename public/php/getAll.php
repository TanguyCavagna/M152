<?php
/**
 * @filesource getAll.php
 * @brief Permet de récupérer tout les postes
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-11
 * @version 1.0.0
 */

header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use \App\Controllers\PostController;

$postController = new PostController();

$posts = $postController->GetAll();

http_response_code(200);
echo json_encode([
    'errors' => [],
    'posts' => $posts
]);
exit();