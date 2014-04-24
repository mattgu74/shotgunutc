<?php
namespace Shotgunutc;
use \Shotgunutc\Db;
use \Shotgunutc\Config;

/*
    This class represent a shotgun Description, all parameters needed for each event.
*/
class Desc {
    protected $id=null;
    protected $titre;
    protected $desc;
    protected $is_public;
    protected $open_non_cotisant;
    protected $debut;
    protected $fin;
    protected $payutc_fun_id;
    protected $payutc_cat_id;

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
        $query = Db::prepare("SELECT * FROM ".Config::get("db_prefix", "shotgun_")."desc where desc_id = :id");
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
	$query->execute();

        if($query->rowCount() != 1) {
            throw new \Exception("Select Desc ($id) doesn't return 1 element.");
        }

        // bind data
        $data = $query->fetch();
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
        Create the database
    */
    public static function install() {

    }
}
