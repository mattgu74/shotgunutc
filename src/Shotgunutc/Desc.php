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

/*
    This class represent a shotgun Description, all parameters needed for each event.
*/
class Desc {
    static protected $table_name = Config::get("db_prefix", "shotgun_")."desc";

    protected $id=null;
    protected $titre;
    protected $desc;
    protected $is_public;
    protected $open_non_cotisant;
    protected $debut;
    protected $fin;
    protected $payutc_fun_id;
    protected $payutc_cat_id;
    protected $creator;

    public function __construct($id=null) {
        if(!empty($id)) {
            $this->select($id);
        }
    }

    /*
        Insert the current object into database.
    */
    public function insert() {
        if($this->id === null) {
            throw new \Exception("Cannot insert this Desc, please use update() !");
        }
    }

    /*
        Propagate modification into database.
        Some fields, like $creator, $payutc_fun_id and $payutc_cat_id are volunterely not updatable.
    */
    public function update() {

    }

    /*
        Select a specific desc ID from database
    */
    public function select($id=null) {
        if($id===null) {
            $id=$this->id;
        }

        // Query database 
        $query = Db::prepare("SELECT * FROM ".self::$table_name." where desc_id = :id");
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
	$query->execute();

        if($query->rowCount() != 1) {
            throw new \Exception("Select Desc ($id) doesn't return 1 element.");
        }

        // bind data
        $data = $query->fetch();
        $this->bind($data);
    }

    /*
        Return all the registered shotguns
    */
    public static function getAll() {
        return Array();
    }

    /*
        Fill local attributes with data from query
    */
    protected function bind($data) {
        $this->id = $data["desc_id"];
        $this->titre = $data["desc_titre"];
        $this->desc = $data["desc_desc"];
        $this->is_public = $data["desc_is_public"];
        $this->open_non_cotisant = $data["desc_open_non_cotisant"];
        $this->debut = $data["desc_debut"];
        $this->fin = $data["desc_fin"];
        $this->payutc_fun_id = $data["payutc_fun_id"];
        $this->payutc_cat_id = $data["payutc_cat_id"];
    }

    /*
        Return create query
    */
    public static function install() {
        $query = "CREATE TABLE IF NOT EXISTS `".self::$table_name."` (
              `desc_id` int(4) NOT NULL AUTO_INCREMENT,
              `desc_titre` varchar(50) NOT NULL,
              `desc_desc` varchar(250) NOT NULL,
              `desc_is_public` int(1) NOT NULL,
              `desc_open_non_cotisant` int(1) NOT NULL,
              `desc_debut` datetime NOT NULL,
              `desc_fin` datetime NOT NULL,
              `payutc_fun_id` int(4) NOT NULL,
              `payutc_cat_id` int(4) NOT NULL,
              PRIMARY KEY (`desc_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        return $query;
    }
}
