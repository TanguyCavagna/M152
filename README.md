# M152

Utilisation de composer pour l'autoload et phpunit

Commandes à faire avant de lancer l'application
```$ composer install```
```$ composer dump-autoload```

## Dépendences
MySQL 8.0+
composer

## Upload de fichiers
Si un soucis de droits de lecture arrive sur les fichier mis en ligne, il faut modifier la ligne 833 du fichier php.ini. Dé-commentez la ligne et mettez le chemin absolue d'un dossier sur lequel vous avez tous les droits.

## MySQL
define('EDB_DBTYPE', 'mysql');
define('EDB_PORT', 3306);
define('EDB_HOST', 'localhost');
define('EDB_DBNAME', 'm152');
define('EDB_USER', 'admin');
define('EDB_PASS', 'Super');