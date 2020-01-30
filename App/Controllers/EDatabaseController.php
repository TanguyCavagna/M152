<?php

namespace App\Controllers;

use App\Controllers\ExtendedPDO;

/**
 * @author 	dominique.aigroz@edu.ge.ch
 */
require_once __DIR__ . '/../../config/conparam.php';

/**
 * @brief	Helper class encapsulating
 * 			the PDO object
 * @author 	dominique.aigroz@kadeo.net
 * @remark	
 */
class EDatabaseController {

    private static $pdoInstance;

    /**
     * @brief	Class Constructor - Create a new database connection if one doesn't exist
       * 			Set to private so no-one can create a new instance via ' = new KDatabase();'
        */
    private function __construct() {
        
    }

    /**
     * @brief	Like the constructor, we make __clone private so nobody can clone the instance
        */
    private function __clone() {
        
    }

    /**
     * @brief	Returns DB instance or create initial connection
     * @return $objInstance;
     */
    public static function getInstance() {
        if (!self::$pdoInstance) {
            try {

                $dsn = EDB_DBTYPE . ':host=' . EDB_HOST . ';port=' . EDB_PORT . ';dbname=' . EDB_DBNAME . ';charset=utf8';
                self::$pdoInstance = new ExtendedPDO($dsn, EDB_USER, EDB_PASS);
                self::$pdoInstance->setAttribute(ExtendedPDO::ATTR_ERRMODE, ExtendedPDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                echo "KDatabase Error: " . $e->getMessage();
            }
        }
        return self::$pdoInstance;
    }

# end method
    /**
     * @brief	Passes on any static calls to this class onto the singleton PDO instance
     * @param 	$chrMethod		The method to call
     * @param 	$arrArguments	The method's parameters
     * @return 	$mix			The method's return value
     */

    final public static function __callStatic($chrMethod, $arrArguments) {
        $pdo = self::getInstance();
        return call_user_func_array(array($pdo, $chrMethod), $arrArguments);
    }

# end method
}