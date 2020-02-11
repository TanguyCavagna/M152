<?php
/**
 * @filesource RelationController.php
 * @brief Controlleur pour mes relations
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-09
 * @version 1.0.0
 */

namespace App\Controllers;

class RelationController extends EDatabaseController {
    /**
     * Initialise tous les champs de la table `own`
     */
    function __construct() {
        $this->tableName = 'own';
        $this->fieldIdPost = 'idPost';
        $this->fieldIdMedia = 'idMedia';
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PRIVATE FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ PUBLIC FUNCTIONS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    /**
     * Ajoute une nouvelle relation entre poste et média
     *
     * @param integer $idPost
     * @param integer $idMedia
     * @return boolean
     */
    public function Insert(int $idPost, int $idMedia): bool {
        $insertQuery = <<<EX
            INSERT INTO `{$this->tableName}`(`{$this->fieldIdPost}`, `{$this->fieldIdMedia}`)
            VALUES(:idPost, :idMedia)
        EX;

        try {
            $this::beginTransaction();

            $requestInsert = $this::getInstance()->prepare($insertQuery);
            $requestInsert->bindParam(':idPost', $idPost);
            $requestInsert->bindParam(':idMedia', $idMedia);
            $requestInsert->execute();

            $this::commit();
            return true;            
        } catch (\PDOException $e) {
            $this::rollBack();

            return false;
        }
    }

    /**
     * Supprime une relation entre poste et média et retourne un tableau contenant les ids des médias supprimer
     *
     * @param integer $idPost
     * @return array|null
     */
    public function Delete(int $idPost): ?array {
        $mediaToDeleteQuery = <<<EX
            SELECT idMedia
            FROM own
            WHERE idPost = :idPost
        EX;
        $deleteQuery = <<<EX
            DELETE FROM own
            WHERE idPost = :idPost
        EX;

        try {
            $medias = [];

            $this::beginTransaction();

            $requestMediaToDelete = $this::getInstance()->prepare($mediaToDeleteQuery);
            $requestMediaToDelete->bindParam(':idPost', $idPost);
            $requestMediaToDelete->execute();
            $medias = $requestMediaToDelete->fetchAll(\PDO::FETCH_ASSOC);

            if (count($medias) > 0) {
                $requestDelete = $this::getInstance()->prepare($deleteQuery);
                $requestDelete->bindParam(':idPost', $idPost);
                $requestDelete->execute();
            }

            $this::commit();

            return $medias;
        } catch (\PDOException $e) {
            $this::rollback();
            return null;
        }
    }

    /**
     * Supprime une relation avec l'id du media
     *
     * @param integer $idMedia
     * @return boolean
     */
    public function DeleteMedia(int $idMedia): bool {
        $deleteQuery = <<<EX
            DELETE FROM own
            WHERE idMedia = :idMedia
        EX;

        try {
            $this::beginTransaction();

            $requestDelete = $this::getInstance()->prepare($deleteQuery);
            $requestDelete->bindParam(':idMedia', $idMedia);
            $requestDelete->execute();

            $this::commit();

            return true;
        } catch (\PDOException $e) {
            $this::rollback();
            return false;
        }
    }

    /**
     * Est-ce qu'un poste à des médias
     *
     * @param integer $idPost
     * @return boolean|null Renvoie null si une erreur se produit
     */
    public function PostOwnMedia(int $idPost): ?bool {
        $selectQuery = <<<EX
            SELECT idMedia
            FROM own
            WHERE idPost = :idPost
        EX;

        try {
            $result = false;

            $this::beginTransaction();

            $requestSelect = $this::getInstance()->prepare($selectQuery);
            $requestSelect->bindParam(':idPost', $idPost);
            $requestSelect->execute();
            $result = $requestSelect->rowCount() > 0;

            $this::commit();

            return $result;
        } catch (\PDOException $e) {
            $this::rollback();
            return null;
        }
    }
}