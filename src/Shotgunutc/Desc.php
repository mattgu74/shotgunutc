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
use \Shotgunutc\BoolField;
use \Shotgunutc\TextareaField;

/*
    This class represent a shotgun Description, all parameters needed for each event.
*/
class Desc {
    protected $table_name;

    public $id=null;
    public $titre;
    public $desc;
    public $is_public;
    public $open_non_cotisant;
    public $debut;
    public $fin;
    public $payutc_fun_id;
    public $payutc_cat_id;

    public function __construct($id=null) {
        if(!empty($id)) {
            $this->select($id);
        }
        $this->table_name = Config::get("db_pref", "shotgun_")."desc";
        // On préécrit des dates pour donner l'exemple du format de date.
        $this->debut = date("Y-m-d H:i:s");
        $this->fin = date("Y-m-d H:i:s");
    }

    public function getForm($title, $action, $submit) {
        $form = new Form();
        $form->title = $title;
        $form->action = $action;
        $form->submit = $submit;
        $form->addItem(new Field("Titre", "titre", $this->titre, "Titre du shotgun"));
        $form->addItem(new TextareaField("Description", "desc", $this->desc, "Description du shotgun"));
        $form->addItem(new BoolField("Evenement public", "is_public", $this->is_public, "Indique si l'événement est publiquement afficher sur le site de shotgunutc."));
        $form->addItem(new BoolField("Ouvert au non cotisant", "open_non_cotisant", $this->open_non_cotisant, "Est-ce que l'événement est ouvert au non cotisant ?"));
        $form->addItem(new Field("Debut", "debut", $this->debut, "Debut du shotgun", "datetime"));
        $form->addItem(new Field("Fin", "fin", $this->fin, "Fin du shotgun", "datetime"));
        return $form;
    }

    public function getChoices() {
        return Choice::getAll($this->id);
    }

    public function addChoice($name, $prix, $stock) {
        $choice = new Choice($this->id, $name, $prix, $stock);
        $choice->insert();
        return $choice;
    }

    /*
        Insert the current object into database.
    */
    public function insert() {
        if($this->id !== null) {
            throw new \Exception("Cannot insert this Desc, please use update() ! ({$this->id})");
        }
        $conn = Db::conn();
        $conn->insert($this->table_name,
            array(
                "desc_titre" => $this->titre,
                "desc_desc" => $this->desc,
                "desc_is_public" => $this->is_public,
                "desc_open_non_cotisant" => $this->open_non_cotisant,
                "desc_debut" => $this->debut,
                "desc_fin" => $this->fin,
                "payutc_fun_id" => $this->payutc_fun_id, 
                "payutc_cat_id" => $this->payutc_cat_id
            ));
        return $conn->lastInsertId();
    }

    /*
        Propagate modification into database.
        Some fields, like $creator, $payutc_fun_id and $payutc_cat_id are volunterely not updatable.
    */
    public function update() {

    }

    protected static function getQbBase() {
        $qb = Db::createQueryBuilder();
        $qb->select('*')
           ->from(Config::get("db_pref", "shotgun_")."desc", "d");
        return $qb;
    }

    /*
        Select a specific desc ID from database
    */
    public function select($id=null) {
        if($id===null) {
            $id=$this->id;
        }

        $qb = self::getQbBase();
        $qb->where('d.desc_id = :desc_id')
            ->setParameter('desc_id', $id);

        $data = $qb->execute()->fetch();
        $this->bind($data);
    }

    /*
        Return all the registered shotguns
    */
    public static function getAll($fun_id = null) {
        $qb = self::getQbBase();
        if($fun_id) {
            $qb->where('d.payutc_fun_id = :fun_id')
                ->setParameter('fun_id', $fun_id);
        }
        $ret = Array();
        foreach($qb->execute()->fetchAll() as $data) {
            $desc = new Desc();
            $desc->bind($data);
            $ret[] = $desc;
        }
        return $ret;
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
        $query = "CREATE TABLE IF NOT EXISTS `".Config::get("db_pref", "shotgun_")."desc` (
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
