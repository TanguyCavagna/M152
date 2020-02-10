<?php

namespace App\Models;

class Media {

    function __construct(string $name, string $type) {
        $this->name = $name;
        $this->type = $type;
    }

}