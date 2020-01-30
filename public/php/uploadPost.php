<?php
require __DIR__ . '/../../vendor/autoload.php';

use \App\Controllers\MediaController;

define('TARGET_DIR', 'uploads/');
define('TOTAL_MAX_SIZE', 70);
define('SINGLE_MAX_SIZE', 3);

$mediaController = new MediaController();

$files = $_FILES['files'];

for ($i = 0; $i < count($files['tmp_name']); $i++) {
    $file_name = $files['name'][$i];
    $file_extension = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
    $final_file_name = time() . $file_extension;

    $mediaController->AddMedia($final_file_name, $files['type'][$i]);    
    //move_uploaded_file($files['tmp_name'][$i], TARGET_DIR . time() . $file_extension);
}

echo '';