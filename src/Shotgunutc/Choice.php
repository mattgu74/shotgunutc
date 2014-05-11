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
use \Shotgunutc\Desc;

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

    public function getForm($title, $action, $submit) {
        $form = new Form();
        $form->title = $title;
        $form->action = $action;
        $form->submit = $submit;
        $form->addItem(new Field("Nom", "name", $this->name, "Nom du choix"));
        $form->addItem(new Field("Prix", "price", $this->price, "Prix du choix", "euro"));
        $form->addItem(new Field("Stock", "stock", $this->stock, "Nombre de place", "number"));
        return $form;
    }

    public function getNbPlace($t) {
        switch($t) {
            case 'A':
                return "TODO";
                break;
            case 'V':
                return "TODO";
                break;
            case 'W':
                return "TODO";
                break;
            case 'T':
                return $this->stock;
                break;
            default:
                return $this->stock;
                break;
        }
    }

    public function isAvailable() {
        return True;    
    }

    public function insert() {
        if($this->id !== null) {
            throw new \Exception("Cannot insert this Desc, please use update() ! ({$this->id})");
        }
        $conn = Db::conn();
        $conn->insert($this->table_name,
            array(
                "choice_name" => $this->name,
                "choice_price" => $this->price,
                "choice_stock" => $this->stock,
                "fk_desc_id" => $this->descId,
                "payutc_art_id" => $this->payutc_art_id
            ));
        return $conn->lastInsertId();
    }

    protected static function getQbBase() {
        $qb = Db::createQueryBuilder();
        $qb->select('*')
           ->from(Config::get("db_pref", "shotgun_")."choice", "c");
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
        $qb->where('c.choice_id = :choice_id')
            ->setParameter('choice_id', $id);

        $data = $qb->execute()->fetch();
        $this->bind($data);
    }

    /*
        Return all the registered shotguns
    */
    public static function getAll($desc_id = null) {
        $qb = self::getQbBase();
        if($desc_id) {
            $qb->where('c.fk_desc_id = :desc_id')
                ->setParameter('desc_id', $desc_id);
        }
        $ret = Array();
        foreach($qb->execute()->fetchAll() as $data) {
            $choice = new Choice();
            $choice->bind($data);
            $ret[] = $choice;
        }
        return $ret;
    }

    public function shotgun($user, $payutcClient) {
        $desc = new Desc();
        $desc->select($this->descId);
        // Check Cotisation !
        if($desc->open_non_cotisant == 0 && $user->is_cotisant != 1) {
            throw new \Exception("Tu n'es pas cotisant BDE-UTC !");
        }

        // Check not yet shotguned
        //if(Option::getUser($user->login))

        // Check available
        if(!$this->isAvailable()) {
            throw new \Exception("Tu as malheureusement cliqué trop lentement, ce choix n'est plus disponible !");
        }

        // If price == 0 : Shotgun

        // If price > 0 : Shotgun + goto payutc

        throw new \Exception("Not yet implemented !");
    }

    /*
        Fill local attributes with data from query
    */
    protected function bind($data) {
        $this->id = $data["choice_id"];
        $this->name = $data["choice_name"];
        $this->price = $data["choice_price"];
        $this->stock = $data["choice_stock"];
        $this->descId = $data["fk_desc_id"];
        $this->payutc_art_id = $data["payutc_art_id"];
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
              `fk_desc_id` int(4) NOT NULL,
              `payutc_art_id` int(4) NOT NULL,
              PRIMARY KEY (`choice_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
        return $query;
    }

}
