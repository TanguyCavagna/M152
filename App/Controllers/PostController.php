<?php

namespace App\Controllers;

use App\Controllers\MediaController;

class PostController extends EDatabaseController {
    /**
     * Initialise tous les champs de la table `user`
     */
    function __construct() {
        $this->tableName = 'post';
        $this->fieldId = 'idPost';
        $this->fieldComment = 'commentary';
        $this->fieldCreation = 'creationDate';
        $this->fieldModification = 'modificationDate';
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRIVATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PUBLIC FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function Insert(string $comment, array $medias = null): bool {
        $insertQuery = <<<EX
            INSERT INTO `{$this->tableName}`(`{$this->fieldComment}`, `{$this->fieldCreation}`, `{$this->fieldModification}`)
            VALUES(:comment, :creation, :modification)
        EX;

        $creationDate = date('Y-m-d');
        $mediaController = new MediaController();

        try {
            $this::beginTransaction();

            $requestInsert = $this::getInstance()->prepare($insertQuery);
            $requestInsert->bindParam(':comment', $comment);
            $requestInsert->bindParam(':creation', $creationDate);
            $requestInsert->bindParam(':modification', $creationDate);
            $requestInsert->execute();

            $lastInsertId = $this::getInstance()->lastInsertId();

            $this::commit();

            if ($medias !== null) {
                for ($i = 0; $i < count($medias['tmp_name']); $i++) {
                    $file_name = $medias['name'][$i];
                    $file_extension = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
                    $final_file_name = uniqid() . $file_extension;
                
                    //* Inutile pour le moment mais à garder si jamais
                    /*array_push($current_json_articles, [
                        "title" => $post_body,
                        "category" => "⛔ No category",
                        "resume" => "Sugar plum icing I love croissant candy caramels marzipan I love.",
                        "author" => "Tanguy Cavagna"
                    ]);*/
                
                    if (!$mediaController->Insert($lastInsertId, $final_file_name, $medias['type'][$i], $medias['tmp_name'][$i], $file_extension)) {
                        return false;
                    }
                }
            }
            
            return true;
        } catch (\PDOException $e) {
            $this::rollBack();

            return false;
        }
    }
}