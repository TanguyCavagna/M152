<?php
require __DIR__ . '/../../vendor/autoload.php';

use \App\Controllers\PostController;

define('TARGET_DIR', 'uploads/');
define('TOTAL_MAX_SIZE', 70);
define('SINGLE_MAX_SIZE', 3);

$postController = new PostController();

$files = $_FILES['files'];
$post_body = filter_input(INPUT_POST, 'post-body', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

//* Inutile pour le moment mais à garder si jamais
//$temp = file_get_contents('../json/articles.json');
//$current_json_articles = json_decode($temp);

$postController->Insert($post_body, $files);

//* Inutile pour le moment mais à garder si jamais
//$final_json_articles = json_encode($current_json_articles);
// TODO: Uncomment this bellow line to update the articles.json (use for research)
// file_put_contents('../json/articles.json', $final_json_articles);

echo '';