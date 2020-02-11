<?php
/**
 * @filesource PostController.php
 * @brief Controlleur pour mes posts
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-09
 * @version 1.0.0
 */

namespace App\Controllers;

// Models
use App\Models\Post;
use App\Models\Media;

// Controlleurs
use App\Controllers\MediaController;
use App\Controllers\RelationController;

class PostController extends EDatabaseController {
    public static $TOTAL_UPLOAD_MAX_SIZE = 70e6;
    public static $SINGLE_UPLOAD_MAX_SIZE = 3e6;
    public static $ALLOWED_TYPES = [
        'image/',
        'audio/',
        'video/',
    ];

    /**
     * Initialise tous les champs de la table `post`
     */
    function __construct() {
        $this->tableName = 'post';
        $this->fieldId = 'idPost';
        $this->fieldComment = 'commentary';
        $this->fieldCreation = 'creationDate';
        $this->fieldModification = 'modificationDate';

        $this->mediaController = new MediaController();
        $this->relationController = new RelationController();
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRIVATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
     * Est-ce-que le poste à 1 ou plusieurs médias
     *
     * @param integer $idPost
     * @return boolean
     */
    private function HasMedia(int $idPost): bool {
        return $this->relationController->PostOwnMedia($idPost);
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PUBLIC FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
     * Insère un nouveau poste
     *
     * @param string $comment Commentaire du poste
     * @param array $medias Liste des médias venant de la supervariables $_FILES
     * @return boolean
     */
    public function Insert(string $comment, array $medias = null): bool {
        $insertQuery = <<<EX
            INSERT INTO `{$this->tableName}`(`{$this->fieldComment}`, `{$this->fieldCreation}`, `{$this->fieldModification}`)
            VALUES(:comment, :creation, :modification)
        EX;

        $creationTimestamp = date("Y-m-d H:i:s");

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
                    
                    if (!$this->mediaController->Insert($lastInsertId, $final_file_name, $medias['type'][$i], $medias['tmp_name'][$i], $file_extension)) {
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

    /**
     * Récupère tout les postes
     *
     * @return array|null Liste de postes
     */
    public function GetAll(): ?array {
        $selectQuery = <<<EX
            (
                SELECT 	{$this->tableName}.{$this->fieldId},
                        {$this->tableName}.{$this->fieldComment},
                        {$this->tableName}.{$this->fieldCreation},
                        {$this->tableName}.{$this->fieldModification},
                        group_concat(media.nameMedia ORDER BY media.idMedia) AS medias,
                        group_concat(media.typeMedia ORDER BY media.idMedia) AS `types`
                FROM {$this->tableName}
                JOIN own ON own.idPost = {$this->tableName}.{$this->fieldId}
                JOIN media ON media.idMedia = own.idMedia
                GROUP BY {$this->fieldId}
            )
            UNION
            (
                SELECT 	{$this->tableName}.{$this->fieldId}, 
                        {$this->tableName}.{$this->fieldComment}, 
                        {$this->tableName}.{$this->fieldCreation}, 
                        {$this->tableName}.{$this->fieldModification}, 
                        null as medias, 
                        null as `types` 
                FROM {$this->tableName}
                WHERE {$this->tableName}.{$this->fieldId} NOT IN (
                    SELECT own.idPost
                    FROM own
                )
                GROUP BY {$this->fieldId}
            ) ORDER BY {$this->fieldId}
        EX;

        try {
            $results = [];

            $this::beginTransaction();

            $requestSelect = $this::getInstance()->prepare($selectQuery);
            $requestSelect->execute();
            $temp = $requestSelect->fetchAll(\PDO::FETCH_ASSOC);

            $this::commit();

            // Encaplusation des posts
            foreach ($temp as $post) {
                $medias = [];

                $mediaNames = explode(',', $post['medias']);
                $mediaTypes = explode(',', $post['types']);

                // Création de la liste des médias apparetenant au poste
                foreach ($mediaNames as $key => $mediaName) {
                    array_push($medias, new Media($mediaName, $mediaTypes[$key]));
                }

                // Création de la liste des postes
                array_push($results, new Post($post['idPost'], $post['commentary'], $post['creationDate'], $post['modificationDate'], $medias));
            }

            return $results;
        } catch (\PDOException $e) {
            $this::rollback();
            return null;
        }
    }

    /**
     * Supprime un poste
     *
     * @param integer $id
     * @return boolean
     */
    public function Delete(int $idPost): bool {
        $deleteQuery = <<<EX
            DELETE FROM {$this->tableName}
            WHERE {$this->tableName}.{$this->fieldId} = :id
        EX;

        try {
            $this::beginTransaction();

            if ($this->HasMedia($idPost)) {
                if (!$this->mediaController->Delete($idPost)) {
                    $this::rollback();
                    return false;
                }
            }

            $requestDelete = $this::getInstance()->prepare($deleteQuery);
            $requestDelete->bindParam(':id', $idPost);
            $requestDelete->execute();

            $this::commit();

            return true;
        } catch (\PDOExeption $e) {
            $this::rollback();
            return false;
        }
    }

    /**
     * Met à jour le commentaire d'un poste
     *
     * @param integer $idPost
     * @param string $comment
     * @return boolean
     */
    public function UpdateComment(int $idPost, string $comment): bool {
        $updateQuery = <<<EX
            UPDATE {$this->tableName}
            SET {$this->tableName}.{$this->fieldComment} = :comment
            WHERE {$this->tableName}.{$this->fieldId} = :idPost
        EX;
        
        try {
            $this::beginTransaction();

            $requestUpdate = $this::getInstance()->prepare($updateQuery);
            $requestUpdate->bindParam(':comment', $comment);
            $requestUpdate->bindParam(':idPost', $idPost);
            $requestUpdate->execute();

            $this::commit();

            return true;
        } catch (\Exception $e) {
            $this::rollback();
            return false;
        }
    }
}