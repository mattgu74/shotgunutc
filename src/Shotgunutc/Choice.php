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
use \Shotgunutc\Db;
use \Shotgunutc\Config;
use \Shotgunutc\Form;
use \Shotgunutc\Field;

class Choice {
    protected $table_name;
    public $id = null;
    public $descId;
    public $name;
    public $price;
    public $stock;

    public function __construct($descId=null, $name=null, $price=null, $stock=null) {
        $this->table_name = Config::get("db_pref", "shotgun_")."choice";
        $this->descId = $descId;
        $this->name = $name;
        $this->price;
        $this->stock;
    }

    public function insert($payutcClient) {
        if($this->id !== null) {
            throw new \Exception("Cannot insert this Desc, please use update() ! ({$this->id})");
        }
        $conn = Db::conn();
        $conn->insert($this->table_name,
            array(
                "choice_name" => $this->name,
                "choice_price" => $this->price,
                "choice_stock" => $this->stock,
                "payutc_art_id" => $this->payutc_art_id
            ));
        return $conn->lastInsertId();
    }

    /*
        Return create query
    */
    public static function install() {
        $query = "CREATE TABLE IF NOT EXISTS `".Config::get("db_pref", "shotgun_")."choice` (
              `choice_id` int(4) NOT NULL AUTO_INCREMENT,
              `choice_name` varchar(50) NOT NULL,
              `choice_price` int(5) NOT NULL,
              `choice_stock` int(5) NOT NULL,
              `payutc_art_id` int(4) NOT NULL,
              PRIMARY KEY (`choice_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        return $query;
    }

}
