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
        $this->fieldSize = 'sizeMedia';
        $this->fieldName = 'nameMedia';
        $this->fieldCreation = 'creationDate';
        $this->fieldModification = 'modificationDate';

        $this->relationController = new RelationController();

        $this->targetDir = '../../public/uploads/';
        $this->maxImageWidth = 1000;
        $this->maxImageHeight = $this->maxImageWidth / (16 / 9);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRIVATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
     * Récupère l'id d'un média avec son nom
     *
     * @param string $nameMedia
     * @return integer|null
     */
    private function GetIdByName(string $nameMedia): ?int {
        $selectQuery = <<<EX
            SELECT {$this->tableName}.{$this->fieldId}
            FROM {$this->tableName}
            WHERE {$this->tableName}.{$this->fieldName} = :nameMedia
        EX;

        try {
            $result = -1;
            $this::beginTransaction();

            $requestSelect = $this::getInstance()->prepare($selectQuery);
            $requestSelect->bindParam(':nameMedia', $nameMedia);
            $requestSelect->execute();
            $result = $requestSelect->fetch(\PDO::FETCH_ASSOC)['idMedia'];

            $this::commit();

            return $result;
        } catch (\PDOException $e) {
            $this::rollback();
            return null;
        }
    }
    
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

    /**
     * Redéfini la taille de l'image
     *
     * @param  mixed $traget Image cible
     * @param  mixed $new Nom de la nouvelle image
     * @param  mixed $w Largeur maximale
     * @param  mixed $h Hauteur maximal
     * @param  mixed $ext Extension de l'image
     *
     * @return bool
     */
    private function resizeImage(string $traget, string $new, string $type) : bool {
        list($origWidth, $origHeight) = getimagesize($traget); // Défini les variables $origWidth et $origHeight au valeur correspondante au index du tableau à droite de l'égalité

        if ($origWidth <= $this->maxImageWidth)
            $w = $origWidth;
        else
            $w = $this->maxImageWidth;

        if ($origHeight <= $this->maxImageHeight)
            $h = $origHeight;
        else
            $h = $this->maxImageHeight;

        $img = "";
        $type = strtolower($type);

        switch ($type) {
            case 'image/gif':
                $img = imagecreatefromgif($traget);
                break;
            
            case 'image/png':
                $img = imagecreatefrompng($traget);
                break;

            default:
                $img = imagecreatefromjpeg($traget);
                break;
        }

        $trueColorImage = imagecreatetruecolor($w, $h);

        $returnState = false;
        if (imagecopyresampled($trueColorImage, $img, 0, 0, 0, 0, $w, $h, $origWidth, $origHeight))
        {
            if (imagejpeg($trueColorImage, $new, 80))
                return true;
        }

        return $returnState;
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
    public function Insert(int $postId, string $name, string $type, string $tmp_name, string $file_extension, int $size): bool {
        $insertQuery = <<<EX
            INSERT INTO `{$this->tableName}`(`{$this->fieldType}`, `{$this->fieldSize}`, `{$this->fieldName}`, `{$this->fieldCreation}`, `{$this->fieldModification}`)
            VALUES(:type, :size, :name, :creation, :modification)
        EX;

        $creationTimestamp = date("Y-m-d H:i:s");

        try {
            $this::beginTransaction();

            $requestInsert = $this::getInstance()->prepare($insertQuery);
            $requestInsert->bindParam(':type', $type);
            $requestInsert->bindParam(':size', $size);
            $requestInsert->bindParam(':name', $name);
            $requestInsert->bindParam(':creation', $creationTimestamp);
            $requestInsert->bindParam(':modification', $creationTimestamp);
            $requestInsert->execute();

            $lastInsertId = $this::getInstance()->lastInsertId();

            if (move_uploaded_file($tmp_name, $this->targetDir . $name)) {
                if (strpos($type, 'image') !== false)
                    $this->resizeImage($this->targetDir . $name, $this->targetDir . $name, $type);

                if (!$this->relationController->Insert($postId, $lastInsertId)) {
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

    /**
     * Suppression d'un média avec son nom
     *
     * @param string $nameMedia
     * @return boolean
     */
    public function DeleteByName(string $nameMedia): bool {
        $deleteQuery = <<<EX
            DELETE FROM {$this->tableName}
            WHERE {$this->tableName}.{$this->fieldName} = :nameMedia
        EX;

        try {
            $this::beginTransaction();

            $idMedia = $this->GetIdByName($nameMedia);
            try {
                $idMedia = intval($idMedia);
            } catch (\Exception $e) {
                $this::rollback();
                return false;
            }

            if($this->relationController->DeleteMedia($idMedia)) {
                $this::beginTransaction();

                if ($this->MoveMediaToTrash($idMedia)) {
                    $requestDelete = $this::getInstance()->prepare($deleteQuery);
                    $requestDelete->bindParam(':nameMedia', $nameMedia);
                    $requestDelete->execute();
                } else {
                    $this::rollback();
                    return false;
                }

                $this::commit();
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