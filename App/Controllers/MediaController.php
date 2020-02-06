<?php

namespace App\Controllers;

class MediaController extends EDatabaseController {
    /**
     * Initialise tous les champs de la table `user`
     */
    function __construct() {
        $this->tableName = 'media';
        $this->fieldId = 'idMedia';
        $this->fieldType = 'typeMedia';
        $this->fieldName = 'nameMedia';
        $this->fieldCreation = 'creationDate';
        $this->fieldModification = 'modificationDate';

        $this->tableLinkName = 'own';
        $this->tableLinkIdPost = 'idPost';
        $this->tableLinkIdMedia = 'idMedia';

        $this->targetDir = '../../public/uploads/';
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRIVATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    private function LinkToPost(int $postId, int $mediaId) {
        $linkQuery = <<<EX
            INSERT INTO `{$this->tableLinkName}`(`{$this->tableLinkIdPost}`, `{$this->tableLinkIdMedia}`)
            VALUES(:idPost, :idMedia)
        EX;

        try {
            $this::beginTransaction();

            $requestLink = $this::getInstance()->prepare($linkQuery);
            $requestLink->bindParam(':idPost', $postId);
            $requestLink->bindParam(':idMedia', $mediaId);
            $requestLink->execute();

            $this::commit();
            return true;            
        } catch (\PDOException $e) {
            $this::rollBack();

            return false;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PUBLIC FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function Insert(int $postId, string $name, string $type, string $tmp_name, string $file_extension): bool {
        $insertQuery = <<<EX
            INSERT INTO `{$this->tableName}`(`{$this->fieldType}`, `{$this->fieldName}`, `{$this->fieldCreation}`, `{$this->fieldModification}`)
            VALUES(:type, :name, :creation, :modification)
        EX;

        $creationTimestamp = date("Y-m-d H:i:s");

        try {
            $this::beginTransaction();

            $requestInsert = $this::getInstance()->prepare($insertQuery);
            $requestInsert->bindParam(':type', $type);
            $requestInsert->bindParam(':name', $name);
            $requestInsert->bindParam(':creation', $creationTimestamp);
            $requestInsert->bindParam(':modification', $creationTimestamp);
            $requestInsert->execute();

            $lastInsertId = $this::getInstance()->lastInsertId();

            if (move_uploaded_file($tmp_name, $this->targetDir . $name)) {
                if (!$this->LinkToPost($postId, $lastInsertId)) {
                    $this::rollBack();
                    return false;
                }
            } else {
                $this::rollBack();
                return false;
            }

            $this::commit();
            
            return true;
        } catch (\PDOException $e) {
            $this::rollBack();

            return false;
        }
    }
}