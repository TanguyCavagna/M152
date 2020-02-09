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
                    
                    if (!$mediaController->Insert($lastInsertId, $final_file_name, $medias['type'][$i], $medias['tmp_name'][$i], $file_extension)) {
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

    public function GetAll(): ?array {
        $selectQuery = <<<EX
            SELECT 	{$this->tableName}.{$this->fieldId},
                    {$this->tableName}.{$this->fieldComment},
                    {$this->tableName}.{$this->fieldCreation},
                    group_concat(media.nameMedia ORDER BY media.idMedia) AS medias,
                    group_concat(media.typeMedia ORDER BY media.idMedia) AS `types`
            FROM post
            JOIN own ON own.idPost = post.idPost
            JOIN media ON media.idMedia = own.idMedia
            WHERE own.idPost = post.idPost
            GROUP BY post.idPost
        EX;

        try {
            $results = [];

            $this::beginTransaction();

            $requestSelect = $this::getInstance()->prepare($selectQuery);
            $requestSelect->execute();
            $results = $requestSelect->fetchAll(\PDO::FETCH_ASSOC);

            $this::commit();

            return $results;
        } catch (\PDOException $e) {
            $this::rollback();
            return null;
        }
    }

    /**
     * Delete a post with is id
     *
     * @param integer $id
     * @return boolean
     */
    public function DeletePost(int $id): bool {
        $deleteQuery = <<<EX
            DELETE FROM {$this->tableName}
            WHERE {$this->tableName}.{$this->fieldId} = :id
        EX;

        try {
            $this::beginTransaction();

            $requestDelete = $this::getInstance()->prepare($deleteQuery);
            $requestDelete->bindParam(':id', $id);
            $requestDelete->execute();

            $this::commit();

            return true;
        } catch (\PDOExeption $e) {
            $this::rollback();
            return false;
        }
    }
}