<?php
/**
 * @filesource Model.php
 * @brief Model de données pour mes medias
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-11
 * @version 1.0.0
 */

namespace App\Models;

/**
 * Model de données
 */
class Media {

    /**
     * Consctructeur de base
     *
     * @param string $name Nom du média
     * @param string $type Type du média
     */
    function __construct(string $name, string $type) {
        $this->name = $name;
        $this->type = $type;
    }

}