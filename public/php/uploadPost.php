<?php
require __DIR__ . '/../../vendor/autoload.php';

use \App\Controllers\MediaController;

define('TARGET_DIR', 'uploads/');
define('TOTAL_MAX_SIZE', 70);
define('SINGLE_MAX_SIZE', 3);

$mediaController = new MediaController();

$files = $_FILES['files'];
$post_body = filter_input(INPUT_POST, 'post-body', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

$temp = file_get_contents('../json/articles.json');
$current_json_articles = json_decode($temp);

for ($i = 0; $i < count($files['tmp_name']); $i++) {
    $file_name = $files['name'][$i];
    $file_extension = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
    $final_file_name = time() . $file_extension;

    array_push($current_json_articles, [
        "title" => $post_body,
        "category" => "⛔ No category",
        "resume" => "Sugar plum icing I love croissant candy caramels marzipan I love.",
        "author" => "Tanguy Cavagna"
    ]);

    //$mediaController->AddMedia($final_file_name, $files['type'][$i]);    
    //move_uploaded_file($files['tmp_name'][$i], TARGET_DIR . time() . $file_extension);
}

$final_json_articles = json_encode($current_json_articles);
file_put_contents('../json/articles.json', $final_json_articles);

echo '';