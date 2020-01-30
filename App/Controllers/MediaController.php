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
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRIVATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PUBLIC FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    public function AddMedia(string $name, string $type): bool {
        $insertQuery = <<<EX
            INSERT INTO `{$this->tableName}`(`{$this->fieldType}`, `{$this->fieldName}`, `{$this->fieldCreation}`, `{$this->fieldModification}`)
            VALUES(:type, :name, :creation, :modification)
        EX;

        $creationDate = date('Y-m-d');

        try {
            $this::beginTransaction();

            $requestInsert = $this::getInstance()->prepare($insertQuery);
            $requestInsert->bindParam(':type', $type);
            $requestInsert->bindParam(':name', $name);
            $requestInsert->bindParam(':creation', $creationDate);
            $requestInsert->bindParam(':modification', $creationDate);
            $requestInsert->execute();

            $this::commit();
            return true;
        } catch (\PDOException $e) {
            $this::rollBack();

            return false;
        }
    }
}