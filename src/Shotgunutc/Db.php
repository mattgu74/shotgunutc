<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <matthieu@guffroy.com> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Matthieu Guffroy
 * ----------------------------------------------------------------------------
 */

 /*
 * ----------------------------------------------------------------------------
 * "LICENCE BEERWARE" (Révision 42):
 * <matthieu@guffroy.com> a créé ce fichier. Tant que vous conservez cet avertissement,
 * vous pouvez faire ce que vous voulez de ce truc. Si on se rencontre un jour et
 * que vous pensez que ce truc vaut le coup, vous pouvez me payer une bière en
 * retour. Matthieu Guffroy
 * ----------------------------------------------------------------------------
 */

namespace Shotgunutc;
use \Shotgunutc\Config;

class Db
{
    private static $config = null;
    private static $conn = null;

    public static function createQueryBuilder()
    {
        return static::conn()->createQueryBuilder();
    }
    
    public static function conn()
    {
        if (static::$conn === null) {
            static::$config = new \Doctrine\DBAL\Configuration();
            $connectionParams = array(
                'host' 	   => Config::get('db_host'),
                'driver'   => 'pdo_mysql',
                'user'     => Config::get('db_login'),
                'password' => Config::get('db_password'),
                'dbname'   => Config::get('db_name'),
                'charset'  => 'utf8'
            );
            static::$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, static::$config);
            static::$conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        }
        return static::$conn;
    }
    
    public static function beginTransaction()
    {
        static::$conn->beginTransaction();
    }
    
    public static function commit()
    {
        static::conn()->commit();
    }
    
    public static function rollback()
    {
        static::conn()->rollback();
    }
}
