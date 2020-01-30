<?php
header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use \App\Controllers\PostController;

define('TARGET_DIR', 'uploads/');
define('TOTAL_MAX_SIZE', 20000000); // byte
define('SINGLE_MAX_SIZE', 3000000); // byte
define('ALLOWED_TYPES', [
    'image/',
    'audio/',
    'video/',
]);

$postController = new PostController();

if (count($_FILES) > 0) {
    $files = $_FILES['files'];
} else {
    $files = null;
}
$post_body = filter_input(INPUT_POST, 'post-body', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$size_error = false;
$file_type_error = false;
$all_files_size = 0;

if (empty($post_body)) {
    http_response_code(422);
    echo json_encode([
        'errors' => 'Le corps du post doit impérativement être présent.'
    ]);
    exit();
}

if ($files !== null) {
    // Test de la taille maximum par fichier et total ainsi que le type de fichier
    for ($i = 0; $i < count($files['tmp_name']); $i++) {
        // Taille
        if ($files['size'][$i] > SINGLE_MAX_SIZE || $files['error'][$i] == 1) {
            $size_error = true;
        }
        $all_files_size += $files['size'][$i];

        // Type
        $type = explode('/', $files['type'][$i])[0] . '/';
        if (!in_array($type, ALLOWED_TYPES)) {
            $file_type_error = true;
        }
    }

    if ($all_files_size > TOTAL_MAX_SIZE) {
        $size_error = true;
    }

    if ($file_type_error === true) {
        http_response_code(415);
        echo json_encode([
            'errors' => 'Un des type de fichier n\'est pas pris en compte.'
        ]);
        exit();
    }

    if ($size_error === true) {
        http_response_code(413);
        echo json_encode([
            'errors' => 'Taille maximum de fichiers excédé.'
        ]);
        exit();
    }
}

if ($postController->Insert($post_body, $files)) {
    http_response_code(200);
    echo json_encode([
        'errors' => []
    ]);
    exit();
} else {
    http_response_code(200);
    echo json_encode([
        'errors' => [
            'Une erreur est survenue lors de l\'ajout du post.'
        ]
    ]);
    exit();
}