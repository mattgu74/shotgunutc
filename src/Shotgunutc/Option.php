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
use \Shotgunutc\Desc;
use \Shotgunutc\Db;
use \Shotgunutc\Config;

/*
    This class represent a shotgun 
*/
class Option {
    protected $table_name;

    public $id=null;
    public $user_login;
    public $user_prenom;
    public $user_nom;
    public $user_mail;
    public $fk_desc_id;
    public $fk_choice_id;
    public $payutc_tra_id;
    public $payutc_tra_url;
    public $date_creation;
    public $date_paiement = null;
    public $status = 'W';

    public function __construct($id=null) {
        if(!empty($id)) {
            $this->select($id);
        }
        $this->table_name = Config::get("db_pref", "shotgun_")."option";
        // On préécrit des dates pour donner l'exemple du format de date.
        $this->creation = date("Y-m-d H:i:s");
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
                "user_login" => $this->user_login,
                "user_prenom" => $this->user_prenom,
                "user_nom" => $this->user_nom,
                "user_mail" => $this->user_mail,
                "fk_desc_id" => $this->fk_desc_id,
                "fk_choice_id" => $this->fk_choice_id,
                "payutc_tra_id" => $this->payutc_tra_id, 
                "payutc_tra_url" => $this->payutc_tra_url,
                "option_date_creation" => $this->date_creation,
                "option_date_paiement" => $this->date_paiement,
                "option_status" => $this->status
            ));
        return $conn->lastInsertId();
    }

    /*
        Propagate modification into database.
        Some fields, like $creator, $payutc_fun_id and $payutc_cat_id are volunterely not updatable.
    */
    public function update() {
        $qb = Db::createQueryBuilder();
        $qb->update(Config::get("db_pref", "shotgun_")."option", 'opt')
            ->set('opt.option_status', ':status')
            ->set('opt.option_date_paiement', ':paiement')
            ->where('option_id = :option_id')
            ->setParameter('option_id', $this->id, "integer")
            ->setParameter('paiement', $this->date_paiement)
            ->setParameter('status', $this->status);
        $qb->execute();
    }

    protected static function getQbBase() {
        $qb = Db::createQueryBuilder();
        $qb->select('*')
           ->from(Config::get("db_pref", "shotgun_")."option", "o");
        return $qb;
    }

    public function checkStatus($payutcClient, $funId) {
        $transaction = $payutcClient->getTransactionInfo(array("fun_id" => $funId, "tra_id" => $this->payutc_tra_id));
        if($transaction->status != $this->status) {
            $this->date_paiement = date("Y-m-d H:i:s");
            $this->status = $transaction->status;
            $this->update();
            if($this->status == 'V') {
                $desc = new Desc($this->fk_desc_id);
                $choice = new Choice();
                $choice->select($this->fk_choice_id);
                // send
                $to = $this->user_mail;
                $subject = "[ShotgunUTC] - Confirmation d'achat";
                $message = "Bonjour {$this->user_prenom} {$this->user_nom},<br />
                <br />
                Ce mail vient confirmer que tu as bien acheté une place pour :</br>
                {$desc->titre} - {$choice->name}<br />
                <br />
                Normalement les organisateurs te recontacteront prochaine pour te donner plus d'informations.<br />
                Si ce n'est pas le cas, contacte les ;) <br />
                <br />
                En cas de problème, n'essaie pas de contacter shotgun@assos.utc.fr (personne ne reçoit l'adresse)<br />
                Pour les problèmes 'techniques' tu peux contacter simde@assos.utc.fr<br />
                ";
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                $headers .= 'From: Shotgunutc <shotgun@assos.utc.fr>' . "\r\n";
                mail($to, $subject, $message, $headers);
            }
        }
        return $this->status;
    }

    /*
        Select a specific desc ID from database
    */
    public function select($id=null) {
        if($id===null) {
            $id=$this->id;
        }

        $qb = self::getQbBase();
        $qb->where('o.option_id = :opt_id')
            ->setParameter('opt_id', $id);

        $data = $qb->execute()->fetch();
        $this->bind($data);
    }

    public static function getUser($login, $descId) {
        $qb = self::getQbBase();
        $qb->where('o.fk_desc_id = :desc_id')
                ->setParameter('desc_id', $descId);
        $qb->andWhere('o.user_login = :login')
                ->setParameter('login', $login);
        $qb->andWhere('o.option_status != :st')
                ->setParameter('st', 'A');

        $ret = Array();
        foreach($qb->execute()->fetchAll() as $data) {
            $opt = new Option();
            $opt->bind($data);
            $ret[] = $opt;
        }
        return $ret;
    }

    /*
        Return all
    */
    public static function getAll($desc_id = null, $choice_id = null, $tra_id = null) {
        $qb = self::getQbBase();
        if($desc_id) {
            $qb->where('o.fk_desc_id = :desc_id')
                ->setParameter('desc_id', $desc_id);
        }
        if($choice_id) {
            $qb->andWhere('o.fk_choice_id = :choice_id')
                ->setParameter('choice_id', $choice_id);
        }
        if($tra_id) {
            $qb->andWhere('o.payutc_tra_id = :tra_id')
                ->setParameter('tra_id', $tra_id);
        }
        $ret = Array();
        foreach($qb->execute()->fetchAll() as $data) {
            $opt = new Option();
            $opt->bind($data);
            $ret[] = $opt;
        }
        return $ret;
    }

    /*
        Fill local attributes with data from query
    */
    protected function bind($data) {
        $this->id = $data["option_id"];
        $this->user_login = $data["user_login"];
        $this->user_prenom = $data["user_prenom"];
        $this->user_nom = $data["user_nom"];
        $this->user_mail = $data["user_mail"];
        $this->fk_desc_id = $data["fk_desc_id"];
        $this->fk_choice_id = $data["fk_choice_id"];
        $this->payutc_tra_id = $data["payutc_tra_id"];
        $this->payutc_tra_url = $data["payutc_tra_url"];
        $this->date_creation = $data["option_date_creation"];
        $this->date_paiement = $data["option_date_paiement"];
        $this->status = $data["option_status"];
    }

    /*
        Return create query
    */
    public static function install() {
        $query = "CREATE TABLE IF NOT EXISTS `".Config::get("db_pref", "shotgun_")."option` (
              `option_id` int(6) NOT NULL AUTO_INCREMENT,
              `user_login` varchar(10) NOT NULL,
              `user_prenom` varchar(50) NOT NULL,
              `user_nom` varchar(50) NOT NULL,
              `user_mail` varchar(125) NOT NULL,
              `fk_desc_id` int(5) NOT NULL,
              `fk_choice_id` int(5) NOT NULL,
              `payutc_tra_id` int(9) NOT NULL,
              `payutc_tra_url` varchar(150) NOT NULL,
              `option_date_creation` datetime NOT NULL,
              `option_date_paiement` datetime DEFAULT NULL,
              `option_status` varchar(1) NOT NULL,
              PRIMARY KEY (`option_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
        return $query;
    }
}
