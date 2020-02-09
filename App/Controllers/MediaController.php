<?php
/**
 * @filesource MediaController.php
 * @brief Controlleur pour mes médias
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-09
 * @version 1.0.0
 */

namespace App\Controllers;

use App\Controllers\RelationController;

class MediaController extends EDatabaseController {
    /**
     * Initialise tous les champs de la table `media`
     */
    function __construct() {
        $this->tableName = 'media';
        $this->fieldId = 'idMedia';
        $this->fieldType = 'typeMedia';
        $this->fieldName = 'nameMedia';
        $this->fieldCreation = 'creationDate';
        $this->fieldModification = 'modificationDate';

        $this->relationController = new RelationController();

        $this->targetDir = '../../public/uploads/';
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRIVATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
     * Supprime le média du serveur
     *
     * @param integer $idMedia
     * @return boolean
     */
    private function MoveMediaToTrash(int $idMedia): bool {
        $selectFileName = <<<EX
            SELECT {$this->tableName}.{$this->fieldName}
            FROM {$this->tableName}
            WHERE {$this->tableName}.{$this->fieldId} = :idMedia
        EX;

        try {
            $this::beginTransaction();

            $requestSelect = $this::getInstance()->prepare($selectFileName);
            $requestSelect->bindParam(':idMedia', $idMedia);
            $requestSelect->execute();
            $fileName = $requestSelect->fetch(\PDO::FETCH_ASSOC);

            unlink($this->targetDir . $fileName['nameMedia']);

            $this::commit();

            return true;
        } catch (\PDOException $e) {
            $this::rollback();
            return false;
        }
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PUBLIC FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
     * Ajoute un nouveau média
     *
     * @param integer $postId Id du poste lié au média
     * @param string $name Nom du média
     * @param string $type Type du média
     * @param string $tmp_name Nom temporaire du média
     * @param string $file_extension Extension du média
     * @return boolean
     */
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
                if (!$this->ownController->Insert($postId, $lastInsertId)) {
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

    /**
     * Supprime un média
     *
     * @param integer $idPost Id du poste lié au média
     * @return boolean
     */
    public function Delete(int $idPost): bool {
        $deleteQuery = <<<EX
            DELETE FROM {$this->tableName}
            WHERE {$this->tableName}.{$this->fieldId} = :idMedia
        EX;

        try {
            $this::beginTransaction();

            $mediaToDelete = $this->relationController->Delete($idPost);

            if ($mediaToDelete !== null) {
                foreach ($mediaToDelete as $media) {
                    $idMedia = $media['idMedia'];
                    
                    $this::beginTransaction();

                    if ($this->MoveMediaToTrash($idMedia)) {
                        $requestDelete = $this::getInstance()->prepare($deleteQuery);
                        $requestDelete->bindParam(':idMedia', $idMedia);
                        $requestDelete->execute();
                    } else {
                        $this::rollback();
                        return false;
                    }

                    $this::commit();
                }
            } else {
                $this::rollback();
                return false;
            }

            $this::commit();

            return true;
        } catch (\PDOException $e) {
            $this::rollback();
            return false;
        }
    }
}