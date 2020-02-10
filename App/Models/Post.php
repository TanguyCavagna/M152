<?php

namespace App\Models;

class Post {

    function __construct(int $id, string $comment, string $creationDate, string $modificationDate, ?array $medias) {
        $this->id = $id;
        $this->comment = $comment;
        $this->creationDate = $creationDate;
        $this->modificationDate = $modificationDate;
        $this->medias = $medias;
    }

}