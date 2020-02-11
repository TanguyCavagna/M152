<?php
/**
 * @filesource updateMediasOfPost.php
 * @brief Permet d'ajouter des médias dans un poste
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-11
 * @version 1.0.0
 */

header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use \App\Controllers\PostController;
use \App\Controllers\MediaController;

define('TARGET_DIR', 'uploads/');

$mediaController = new MediaController();

$size_error = false;
$file_type_error = false;
$all_files_size = 0;

$idPost = filter_input(INPUT_POST, 'idPost');

if ($idPost === "null") {
    $idPost = null;
}

if (count($_FILES) > 0) {
    $files = $_FILES['files'];
} else {
    $files = null;
}

if ($files !== null) {
    // Test de la taille maximum par fichier et total ainsi que le type de fichier
    for ($i = 0; $i < count($files['tmp_name']); $i++) {
        // Taille
        if ($files['size'][$i] > PostController::$SINGLE_UPLOAD_MAX_SIZE || $files['error'][$i] == 1) {
            $size_error = true;
        }
        $all_files_size += $files['size'][$i];

        // Type
        $type = explode('/', $files['type'][$i])[0] . '/';
        if (!in_array($type, PostController::$ALLOWED_TYPES)) {
            $file_type_error = true;
        }
    }

    if ($all_files_size > PostController::$TOTAL_UPLOAD_MAX_SIZE) {
        $size_error = true;
    }

    if ($size_error === true) {
        http_response_code(413);
        echo json_encode([
            'errors' => 'Taille maximum de fichiers excédé.'
        ]);
        exit();
    }

    if ($file_type_error === true) {
        http_response_code(415);
        echo json_encode([
            'errors' => 'Un des type de fichier n\'est pas pris en compte.'
        ]);
        exit();
    }
}

if (!$size_error && !$file_type_error) {
    for ($i = 0; $i < count($files['tmp_name']); $i++) {
        $file_name = $files['name'][$i];
        $file_extension = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $final_file_name = uniqid() . $file_extension;
        
        if (!$mediaController->Insert($idPost, $final_file_name, $files['type'][$i], $files['tmp_name'][$i], $file_extension)) {
            http_response_code(200);
            echo json_encode([
                'errors' => `Une erreur est survenue lors de l\'ajout du media ${file_name}. Il n'a donc pas été ajouter.`
            ]);
            exit();
        }
    }

    http_response_code(200);
    echo json_encode([
        'errors' => []
    ]);
    exit();
}