<?php

namespace App\Models;

class Post {

    /**
     * Constructeur pour un poste
     *
     * @param integer $id
     * @param string $comment
     * @param string $creationDate Format Y-m-d H:i:s
     * @param string $modificationDate Format Y-m-d H:i:s
     * @param array|null $medias
     */
    function __construct(int $id, string $comment, string $creationDate, string $modificationDate, ?array $medias) {
        $this->id = $id;
        $this->comment = $comment;
        $this->creationDate = date_create_from_format('Y-m-d H:i:s', $creationDate);
        $this->modificationDate = date_create_from_format('Y-m-d H:i:s', $modificationDate);
        $this->medias = $medias;
    }

}