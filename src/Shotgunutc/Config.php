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

/*
    This class allow access to configuration, and permit installation and edition of the file
*/
class Config {
	public static $conf = null;
	public static $default = array(
		// [0]: key, [1]: label
		array("title","Titre du site"),
		array("self_url","URL de ce site (utilisé pour les cookies)"),
		array("db_host","DB host"),
		array("db_login","DB login"),
		array("db_password","DB password"),
		array("db_name","DB name"),
		array("db_pref","DB prefix"),
		array("payutc_server","URL payutc server"),
		array("payutc_key","clef d'application payutc"),
		array("ginger_server","URL ginger server"),
		array("ginger_key","clef d'application ginger"),
		array("namespace","Session namespace"),
		);

	public static function init() {
		if(self::$conf !== null) { return; }
		if(!file_exists("config.inc.php")) {
			throw new \Exception("System not installed ! Please visit /install. ");
		}

		self::$conf = json_decode(file_get_contents("config.inc.php"), true);
	}

	public static function get($key, $default=null) {
		if(self::$conf === null) { 
			try {
				self::init();
			} catch(\Exception $e) {
				if($default !== null) {
					return $default;
				} else {
					throw new \Exception("Try to get conf[".$key."] but this is not yet configured !");
				}
			} 
		}
		if(array_key_exists($key, self::$conf)) {
			return self::$conf[$key];
		} else if($default !== null) {
			return $default;
		} else {
			throw new \Exception("Try to get conf[".$key."] but this is not yet configured !");
		}
	}

	public static function isInstalled() {
		return self::get("payutc_key", "") != "";
	}

	public static function set($key, $value) {
		self::$conf[$key] = $value;
		file_put_contents("config.inc.php", json_encode(self::$conf));
	}
}