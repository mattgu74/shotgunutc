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

require 'vendor/autoload.php';
use Shotgunutc\Desc;
use Shotgunutc\Choice;
use Shotgunutc\Option;
use Shotgunutc\Config;
use Shotgunutc\Cas;
use \Ginger\Client\GingerClient;
use \Payutc\Client\AutoJsonClient;
use \Payutc\Client\JsonException;


function checkRight($payutcClient, $user, $app, $fun_check, $fun_id) {
    if($fun_check and $fun_id == null) {
        if($payutcClient->isAdmin()) {
            return true;
        } else {
            //throw new JsonException("L'utilisateur n'est pas admin.", 403);
        }
    } else {
        $fundations = $payutcClient->getFundations(array("user"=> $user, "app" => $app));
        foreach($fundations as $fundation) {
            if($fundation->fun_id == $fun_id) {
                return true;
            }
        }
        throw new JsonException("L'utilisateur n'a pas les droits sur cette association.", 403);
    }
}

// Settings for cookies
$sessionPath = parse_url(Config::get('self_url', "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"), PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);
session_start();

try {
    Config::init();
} catch(\Exception $e) {
    Config::$conf = Array();
    // Set the only one forced config line, that we have to change manually.
    Config::set('payutc_server', "https://api.nemopay.net/services");
    Config::set('proxy', '');
    Config::set('self_url', "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    Config::set('title', "ShotgunUTC");
    Config::set('system_id', '');
}

// get payutcClient
function getPayutcClient($service) {
    return new AutoJsonClient(
        Config::get('payutc_server'),
        $service,
        array(CURLOPT_PROXY => Config::get('proxy')),
        "Payutc Json PHP Client",
        isset($_SESSION['payutc_cookie']) ? $_SESSION['payutc_cookie'] : "",
        Config::get('system_id', ''),
        Config::get('payutc_key', ''));
}
$payutcClient = getPayutcClient("WEBSALE");

$admin = false;

$app = new \Slim\Slim();

$app->hook('slim.before', function () use ($app) {
    // check that system is installed
    if(!Config::isInstalled()) {
        $app->flashNow('info', 'This application is not yet configured, please click <a href="install" >here</a> !');
    }
});

// Set default data for view
$app->view->setData(array(
    'title' => Config::get('title', 'ShotgunUTC')
));

/*
    PUBLIC ZONE
*/

// Welcome page, list all current Shotguns
$app->get('/', function() use($app) {
    $app->redirect('index');
});
$app->get('/index', function() use($app) {
    $app->render('header.php', array(
        "active" => "index"
        ));
    $app->render('index.php', array(
        "shotguns" => Desc::getAll()
        ));
    $app->render('footer.php');
});

// About page, list all current Shotguns
$app->get('/about', function() use($app) {
    $app->render('header.php', array(
        "active" => "about"
        ));
    $app->render('about.php', array());
    $app->render('footer.php');
});

// Show a specific shotgun page
$app->get('/shotgun', function() use($app) {
    $gingerClient = new GingerClient(Config::get('ginger_key'), Config::get('ginger_server'));
    if(!isset($_GET["id"])) {
        $app->redirect("index");
    } else {
        $id = $_GET["id"];
    } 
    $desc = new Desc();
    $desc->select($id);
    $app->render('header.php', array());
    $app->render('shotgun.php', array(
        "desc" => $desc,
        "username" => isset($_SESSION['username']) ? $_SESSION['username'] : null,
        "user" => isset($_SESSION['username']) ? $gingerClient->getUser($_SESSION["username"]) : null,
        "payutcClient" => getPayutcClient("WEBSALE")));
    $app->render('footer.php');
});

// Show a specific shotgun page
$app->get('/makeshotgun', function() use($app) {
    $gingerClient = new GingerClient(Config::get('ginger_key'), Config::get('ginger_server'));
    $payutcClient = getPayutcClient("WEBSALE");

    if(!isset($_GET["id"]) || !isset($_GET["choice_id"])) {
        $app->redirect("index");
    } else {
        $id = $_GET["id"];
        $choice_id = $_GET["choice_id"];
    } 
    $choice = new Choice();
    $choice->select($choice_id);
    if($choice->descId != $id) {
        $app->flash("info", "A quoi tu joues ?");
        $app->redirect("index");
    }
    try {
        $app->response->redirect($choice->shotgun($gingerClient->getUser($_SESSION["username"]), $payutcClient), 303);
        return;
    } catch (\Exception $e) {
        $app->flash("info", $e->getMessage());
    }
    $app->redirect("shotgun?id=".$id);
});

// REmove a choice
$app->get('/cancel', function() use($app) {
    $gingerClient = new GingerClient(Config::get('ginger_key'), Config::get('ginger_server'));
    $payutcClient = getPayutcClient("WEBSALE");

    if(!isset($_GET["id"])) {
        $app->redirect("index");
    } else {
        $id = $_GET["id"];
    } 
    $options = Option::getUser($_SESSION["username"], $id);
    if(count($options) > 0) {
        $option = $options[0];
        $option->status = 'A';
        $option->update();
    }
    $app->redirect("shotgun?id=".$id);
});


/*
    ADMIN ZONE
*/

$app->get('/shotgunform', function() use($app, $admin) {
    $payutcClient = getPayutcClient("GESARTICLE");
    if(!isset($_GET["fun_id"])) {
        $app->redirect("admin");
    } else {
        $fun_id = $_GET["fun_id"];
    }  
    try {
        // $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$fun_id));
        checkRight($payutcClient, true, false, true, $fun_id);
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }
    if(isset($_GET["desc_id"])) {
        $desc_id = $_GET["desc_id"];
        $desc = new Desc($desc_id);
        $form = $desc->getForm("Modification d'un shotgun", "shotgunform?fun_id=".$fun_id."&desc_id=".$desc_id, "Modifier");
    } else {
        $desc = new Desc();
        $form = $desc->getForm("Création d'un shotgun", "shotgunform?fun_id=".$fun_id, "Créer");
    }
    $app->render('header.php', array());
    $app->render('form.php', array(
        "form" => $form
    ));
    $app->render('footer.php');
});

$app->post('/shotgunform', function() use($app, $admin) {
    $payutcClient = getPayutcClient("GESARTICLE");
    if(!isset($_GET["fun_id"])) {
        $app->redirect("admin");
    } else {
        $fun_id = $_GET["fun_id"];
    }  
    try {
        // $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$fun_id));
        checkRight($payutcClient, true, false, true, $fun_id);
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }
    if(isset($_GET["desc_id"])) {
        $desc_id = $_GET["desc_id"];
        $desc = new Desc($desc_id);
        $form = $desc->getForm("Modification d'un shotgun", "shotgunform?fun_id=".$fun_id."&desc_id=".$desc_id, "Modifier");
        $form->load();
        $desc->update();
    } else {
        $desc = new Desc();
        $form = $desc->getForm("Création d'un shotgun", "createshotgun?fun_id=".$fun_id, "Créer");
        $form->load();
        try {
            // Création de la catégorie dans payutc (celle ou on rentrera les articles)
            $ret = $payutcClient->setCategory(array(
                "name" => $desc->titre, 
                "parent_id" => null, 
                "fun_id" => $fun_id));
            if(isset($ret->success)) {
                $desc->payutc_fun_id = $fun_id;
                $desc->payutc_cat_id = $ret->success;
            }
            $desc_id = $desc->insert();
        } catch (\Exception $e) {
            $app->flashNow('info', "Une erreur est survenu, la création du shotgun à échoué. => {$e->getMessage()}" . print_r($e, true));
            $app->render('header.php', array());
            $app->render('form.php', array(
                "form" => $form
            ));
            $app->render('footer.php');
            return;
        }
    }
    $app->redirect("adminshotgun?id=".$desc_id);
});

$app->get('/adminshotgun', function() use($app) {
    $payutcClient = getPayutcClient("GESARTICLE");
    if(!isset($_GET["id"])) {
        $app->redirect("admin");
    } else {
        $id = $_GET["id"];
    }
    $desc = new Desc();
    $desc->select($id);
    try {
        // $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$desc->payutc_fun_id));
        checkRight($payutcClient, true, false, true, $desc->payutc_fun_id);
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }

    $app->render('header.php', array());
    $app->render('adminshotgun.php', array(
        "shotgun" => $desc
    ));
    $app->render('footer.php');
});

$app->get('/export', function() use($app) {
    $payutcClient = getPayutcClient("GESARTICLE");
    if(!isset($_GET["id"])) {
        $app->redirect("admin");
    } else {
        $id = $_GET["id"];
    }
    $desc = new Desc();
    $desc->select($id);
    try {
        // $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$desc->payutc_fun_id));
        checkRight($payutcClient, true, false, true, $desc->payutc_fun_id);
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }

    $desc->exportCSV();
});

$app->get('/choiceform', function() use($app) {
    $payutcClient = getPayutcClient("GESARTICLE");
    if(!isset($_GET["id"])) {
        $app->redirect("admin");
    } else {
        $id = $_GET["id"];
    }

    $desc = new Desc();
    $desc->select($id);
    try {
        // $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$desc->payutc_fun_id));
        checkRight($payutcClient, true, false, true, $desc->payutc_fun_id);
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }

    if(isset($_GET["choice_id"])) {
        $choice_id = $_GET["choice_id"];
        $choice = new Choice($id);
        $choice->select($choice_id);
        $form = $choice->getForm("Modification d'un choix", "choiceform?id=".$id."&choice_id=".$choice_id, "Modifier");
    } else {
        $choice = new Choice($id);
        $form = $choice->getForm("Création d'un choix", "choiceform?id=".$id, "Ajouter");
    }
    $app->render('header.php', array());
    $app->render('form.php', array(
        "form" => $form
    ));
    $app->render('footer.php');
});

$app->post('/choiceform', function() use($app, $admin) {
    $payutcClient = getPayutcClient("GESARTICLE");
    if(!isset($_GET["id"])) {
        $app->redirect("admin");
    } else {
        $id = $_GET["id"];
    }  
    $desc = new Desc();
    $desc->select($id);
    try {
        // $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$desc->payutc_fun_id));
        checkRight($payutcClient, true, false, true, $desc->payutc_fun_id);
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }

    if(isset($_GET["choice_id"])) {
        $choice_id = $_GET["choice_id"];
        $choice = new Choice($id);
        $choice->select($choice_id);
        $form = $choice->getForm("Modification d'un choix", "choiceform?id=".$id."&choice_id=".$choice_id, "Modifier");
        $form->load();
        $payutcClient->setProduct(array(
                "obj_id" => $choice->payutc_art_id,
                "name" => $desc->titre." ".$choice->name, 
                "parent" =>  $desc->payutc_cat_id,
                "prix" => $choice->price,
                "stock" => $choice->stock,
                "alcool" => 0,
                "fun_id" => $desc->payutc_fun_id));
        $choice->update();
    } else {
        $choice = new Choice($id);
        $form = $choice->getForm("Création d'un choix", "addchoice?id=".$id, "Ajouter");
        $form->load();
        try {
            // Création de l'article dans payutc
            $ret = $payutcClient->setProduct(array(
                "name" => $desc->titre." ".$choice->name, 
                "parent" =>  $desc->payutc_cat_id,
                "prix" => $choice->price,
                "stock" => $choice->stock,
                "alcool" => 0,
                "fun_id" => $desc->payutc_fun_id));
            if(isset($ret->success)) {
                $choice->payutc_art_id = $ret->success;
            }
            $choice->insert();
        } catch (\Exception $e) {
            $app->flashNow('info', "Une erreur est survenu, la création du choix à échoué. => {$e->getMessage()}");
            $app->render('header.php', array());
            $app->render('form.php', array(
                "form" => $form
            ));
            $app->render('footer.php');
            return;
        }
    }
    $app->redirect("adminshotgun?id=".$id);
});

// Admin panel, welcome page
$app->get('/admin', function() use($app, $admin) {
    $payutcClient = getPayutcClient("GESARTICLE");
    try {
        $status = $payutcClient->getStatus();
    } catch(Exception $e) {
        $status = null;
    }
    if(!$status->user) {
        $app->redirect("loginpayutc?goto=admin");
    }
    $fundations = $payutcClient->getFundations();
    if(count($fundations) == 0) {
        $app->flash('info', 'Vous n\'avez pas de droits pour créer ou administrer un shotgun. Si vous souhaitez utiliser cet outil, contactez payutc@assos.utc.fr');
        $app->redirect("index");
    }

    $app->render('header.php', array());
    $app->render('admin.php', array(
        "fundations" => $fundations,
        "shotguns" => Desc::getAll(),
        ));
    $app->render('footer.php');
});



/*
    Login/Logout method
*/

// Connection standard (not payutc)
$app->get('/login', function() use($app, $payutcClient) {
    if(empty($_GET["ticket"])) {
        $service = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; 
        $_SESSION['service'] = $service;
        $casUrl = $payutcClient->getCasUrl()."login?service=".urlencode($service);
        $app->response->redirect($casUrl, 303);
    } else {
        $cas = new Cas($payutcClient->getCasUrl());
        $user = $cas->authenticate($_GET["ticket"], $_SESSION['service']);
        $_SESSION['payutc_cookie'] = $payutcClient->cookies;
        $_SESSION['username'] = $user;
        $app->response->redirect(isset($_GET['goto']) ? $_GET['goto'] : "index", 303);
    }
});

// Connection via payutc
$app->get('/loginpayutc', function() use($app, $payutcClient) {
    if(empty($_GET["ticket"])) {
        $service = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; 
        $_SESSION['service'] = $service;
        $casUrl = $payutcClient->getCasUrl()."login?service=".urlencode($service);
        $app->response->redirect($casUrl, 303);
    } else {
        $result = $payutcClient->loginCas2(array("ticket" => $_GET["ticket"], "service" => $_SESSION['service']));
        $_SESSION['sessionid'] = $result->sessionid;
        $_SESSION['username'] = $result->username;
	$_SESSION['payutc_cookie'] = $payutcClient->cookies;
        $app->response->redirect($_GET['goto'], 303);
    }
});

// Deconnexion
$app->get('/logout', function() use($app, $payutcClient) {
    $status = $payutcClient->getStatus();
    if($status->user) {
        $payutcClient->logout();
    }
    if(isset($_SESSION['username']) || $status->user) {
        $service = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; 
        $casUrl = $payutcClient->getCasUrl()."logout?url=".urlencode($service);
        session_destroy();
        $app->response->redirect($casUrl, 303);    
    } else {
        session_destroy();
        $app->response->redirect(isset($_GET['goto']) ? $_GET['goto'] : "index", 303);
    }
});

/*
    Installation/Configuration zone
*/

$app->get('/getsql', function() use($app, $payutcClient, $admin) {
    // Remove flash (we are on the good page to install/configure system)
    $app->flashNow('info', null);
    $app->render('header.php', array());
    if($admin) {
        $app->render('sql.php', array(
            "desc" => Desc::install(),
            "choice" => Choice::install(),
            "option" => Option::install()
            ));
    } else {
        $app->render('install_not_admin.php', array(
            "status" => null,
            "debug" => $payutcClient->cookies));
    }
    $app->render('footer.php');
});

// Install options
$app->get('/install', function() use($app, $payutcClient, $admin) {
    // Remove flash (we are on the good page to install/configure system)
    $app->flashNow('info', null);
    $app->render('header.php', array());
    if($admin) {
        $app->render('install.php', array());
    } else {
        $app->render('install_not_admin.php', array(
            "status" => null,
            "debug" => $payutcClient->cookies));
    }
    $app->render('footer.php');
});

// Install options
$app->post('/install', function() use($app, $payutcClient, $admin) {
    if($admin) {
        foreach(Config::$default as $item) {
            Config::set($item[0], $_POST[$item[0]]);
        }
    }
    $app->response->redirect('install');
});

// Declare payutc app
$app->get('/installpayutc', function() use($app, $payutcClient, $admin) {
    $payutcClient = getPayutcClient('KEY');
    if($admin) {
        $appli = $payutcClient->registerApplication(
            array(
                "app_url"  =>Config::get('self_url'), 
                "app_name" =>Config::get('title')." déclaré par {$_SESSION['username']}", 
                "app_desc" =>"Microbilletterie"));
        Config::set('payutc_key', $appli->app_key);
    }
    $app->response->redirect("install");
});


$app->get('/cron', function() use($app, $payutcClient, $admin) {
    $payutcClient = getPayutcClient("WEBSALE");
    $options = Option::getAll();
    foreach($options as $opt) {
        if($opt->status == 'W') {
            $desc = new Desc($opt->fk_desc_id);
            $funId = $desc->payutc_fun_id;
            $opt->checkStatus($payutcClient, $funId);
        }
    }
    $app->redirect('index');
});

$app->get('/callback', function() use($app, $payutcClient, $admin) {
    $payutcClient = getPayutcClient("WEBSALE");
    $options = Option::getAll();
    foreach($options as $opt) {
        $desc = new Desc($opt->fk_desc_id);
        $funId = $desc->payutc_fun_id;
        $opt->checkStatus($payutcClient, $funId);
    }
    $app->redirect('index');
});

$app->run();
