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
use \Shotgunutc\Option;
use \Shotgunutc\Field;
use \Shotgunutc\Desc;

class Choice {
    protected $table_name;
    public $id = null;
    public $descId;
    public $name;
    public $priceC;
    public $priceNC;
    public $stock;

    public function __construct($descId=null, $name=null, $price=null, $stock=null) {
        $this->table_name = Config::get("db_pref", "shotgun_")."choice";
        $this->descId = $descId;
        $this->name = $name;
        $this->priceC;
        $this->priceNC;
        $this->stock;
    }

    public function getForm($title, $action, $submit) {
        $form = new Form();
        $form->title = $title;
        $form->action = $action;
        $form->submit = $submit;
        $form->addItem(new Field("Nom", "name", $this->name, "Nom du choix"));
        $form->addItem(new Field("Prix cotisant", "priceC", $this->priceC, "Prix du choix pour les cotisants BDE", "euro"));
        $form->addItem(new Field("Prix non cotisant", "priceNC", $this->priceNC, "Prix du choix pour les non cotisants BDE", "euro"));
        $form->addItem(new Field("Stock", "stock", $this->stock, "Nombre de place", "number"));
        return $form;
    }

    public function getNbPlace($t) {
        $qb = Db::createQueryBuilder();
        $qb->select('count(*) as total')
           ->from(Config::get("db_pref", "shotgun_")."option", "o")
           ->where('fk_desc_id = :desc_id')
           ->andWhere('fk_choice_id = :choice_id')
           ->setParameter('choice_id', $this->id)
           ->setParameter('desc_id', $this->descId);
        switch($t) {
            case 'A':
                $qb->andWhere("option_status = 'V' or option_status = 'W' or option_status = 'A'");
                $r = $qb->execute()->fetch();
                $descr = new Desc($this->descId);
                $qbr = Db::createQueryBuilder();
                $qbr->select('count(*) as total')
                   ->from(Config::get("db_pref", "shotgun_")."option", "o")
                   ->where('fk_desc_id = :desc_id')
                   ->setParameter('desc_id', $this->descId);
                $rr = $qbr->execute()->fetch();
                return min($this->stock - $r["total"], $descr->quota - $rr["total"]);
                break;
            case 'V':
                $qb->andWhere("option_status = 'V'");
                $r = $qb->execute()->fetch();
                return $r["total"];
                break;
            case 'W':
                $qb->andWhere("option_status = 'W'");
                $r = $qb->execute()->fetch();
                return $r["total"];
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
        return ($this->getNbPlace('A') > 0);
    }

    public function insert() {
        if($this->id !== null) {
            throw new \Exception("Cannot insert this Desc, please use update() ! ({$this->id})");
        }
        $conn = Db::conn();
        $conn->insert($this->table_name,
            array(
                "choice_name" => $this->name,
                "choice_priceC" => $this->priceC,
                "choice_priceNC" => $this->priceNC,
                "choice_stock" => $this->stock,
                "fk_desc_id" => $this->descId,
                "payutc_art_idC" => $this->payutc_art_idC,
                "payutc_art_idNC" => $this->payutc_art_idNC,
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

    public function update() {
        $qb = Db::createQueryBuilder();
        $qb->update(Config::get("db_pref", "shotgun_")."choice", 'c')
            ->set('c.choice_name', ':name')
            ->setParameter('name', $this->name)
            ->set('c.choice_priceC', ':priceC')
            ->setParameter('priceC', $this->priceC)
            ->set('c.choice_priceNC', ':priceNC')
            ->setParameter('priceNC', $this->priceNC)
            ->set('c.choice_stock', ':stock')
            ->setParameter('stock', $this->stock);

        $qb->where('c.choice_id = :choice_id')
            ->setParameter('choice_id', $this->id);
        $qb->execute();
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

        // Check not yet shotguned
        $opt = Option::getUser($user->login, $this->descId);
        if(count($opt) > 0) {
            $o = $opt[0];
            if($o->status == 'W') {
                $o->checkStatus($payutcClient, $desc->payutc_fun_id);
            }
            if($o->status == 'V') {
                throw new \Exception("Tu as déjà une place pour cet événement.");
            } else {
                return $o->payutc_tra_url;
            }
        }

        // Check available
        if(!$this->isAvailable()) {
            throw new \Exception("Tu as malheureusement cliqué trop lentement, ce choix n'est plus disponible !");
        }

        // Check always open
        $debut = new \DateTime($desc->debut);
        $fin = new \DateTime($desc->fin);
        $now = new \DateTime("NOW");
        $diff = $now->diff($debut);
        if($diff->invert) {
            if($now->diff($fin)->invert) {
                throw new \Exception("Désolé la vente est terminé...");
            }
        } else {
            throw new \Exception("La vente n'est pas encore ouvert ! Qu'est ce que tu fais la ?");
        }

        // Let's play !
        // cotisation
        if($user->is_cotisant != 1) {
            $art_id = $this->payutc_art_idNC;
        } else {
            $art_id = $this->payutc_art_idC;
        }

        $vente = $payutcClient->createTransaction(array(
            "items" => json_encode(array(array($art_id, 1))),
            "fun_id" => $desc->payutc_fun_id,
            "mail" => $user->mail,
            "return_url" => Config::get("self_url")."shotgun?id=".$desc->id,
            "callback_url" => Config::get("self_url")."callback"
        ));

        $opt = new Option();
        $opt->user_login = $user->login;
        $opt->user_prenom = $user->prenom;
        $opt->user_nom = $user->nom;
        $opt->user_mail = $user->mail;
        $opt->user_cotisant = $user->is_cotisant ? 1 : 0;
        $opt->fk_desc_id = $desc->id;
        $opt->fk_choice_id = $this->id;
        $opt->payutc_tra_id = $vente->tra_id;
        $opt->payutc_tra_url = $vente->url;
        $opt->date_creation = date("Y-m-d H:i:s");
        $opt->status = 'W';
        $opt->insert();

        return $vente->url;
    }

    /*
        Fill local attributes with data from query
    */
    protected function bind($data) {
        $this->id = $data["choice_id"];
        $this->name = $data["choice_name"];
        $this->priceC = $data["choice_priceC"];
        $this->priceNC = $data["choice_priceNC"];
        $this->stock = $data["choice_stock"];
        $this->descId = $data["fk_desc_id"];
        $this->payutc_art_idC = $data["payutc_art_idC"];
        $this->payutc_art_idNC = $data["payutc_art_idNC"];
    }

    /*
        Return create query
    */
    public static function install() {
        $query = "CREATE TABLE IF NOT EXISTS `".Config::get("db_pref", "shotgun_")."choice` (
              `choice_id` int(4) NOT NULL AUTO_INCREMENT,
              `choice_name` varchar(50) NOT NULL,
              `choice_priceC` int(5) NOT NULL,
              `choice_priceNC` int(5) NOT NULL,
              `choice_stock` int(5) NOT NULL,
              `fk_desc_id` int(4) NOT NULL,
              `payutc_art_idC` int(11) NOT NULL,
              `payutc_art_idNC` int(11) NOT NULL,
              PRIMARY KEY (`choice_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
        return $query;
    }

}
