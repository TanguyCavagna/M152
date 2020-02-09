<?php
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