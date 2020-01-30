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

        $creationTimestamp = date("Y-m-d H:i:s");
        $mediaController = new MediaController();

        try {
            $this::beginTransaction();

            $requestInsert = $this::getInstance()->prepare($insertQuery);
            $requestInsert->bindParam(':comment', $comment);
            $requestInsert->bindParam(':creation', $creationTimestamp);
            $requestInsert->bindParam(':modification', $creationTimestamp);
            $requestInsert->execute();

            $lastInsertId = $this::getInstance()->lastInsertId();

            if ($medias !== null) {
                for ($i = 0; $i < count($medias['tmp_name']); $i++) {
                    $file_name = $medias['name'][$i];
                    $file_extension = '.' . pathinfo($file_name, PATHINFO_EXTENSION);
                    $final_file_name = uniqid() . $file_extension;
                
                    if ($mediaController->Insert($lastInsertId, $final_file_name, $medias['type'][$i], $medias['tmp_name'][$i], $file_extension)) {
                        $this::rollBack();
                        return false;
                    }
                }
            }
            
            $this::commit();
            return true;
        } catch (\PDOException $e) {
            $this::rollBack();

            return false;
        }
    }
}