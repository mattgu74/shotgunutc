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
use Shotgunutc\Config;
use Shotgunutc\Cas;
use \Ginger\Client\GingerClient;
use \Payutc\Client\AutoJsonClient;
use \Payutc\Client\JsonException;

// Settings for cookies
$sessionPath = parse_url(Config::get('self_url', "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"), PHP_URL_PATH);
session_set_cookie_params(0, $sessionPath);
session_start();

try {
    Config::init();
} catch(\Exception $e) {
    Config::$conf = Array();
    // Set the only one forced config line, that we have to change manually.
    Config::set('payutc_server', "https://assos.utc.fr/payutc_dev/server");
    Config::set('self_url', "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
    Config::set('title', "ShotgunUTC");
}

// get payutcClient
$payutcClient = new AutoJsonClient(Config::get('payutc_server'), "WEBSALE", array(), "Payutc Json PHP Client", isset($_SESSION['payutc_cookie']) ? $_SESSION['payutc_cookie'] : "");

$status = $payutcClient->getStatus();
$admin = false;
if($status->user) {
    try {
        $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>null));
        $admin = true;
    } catch(JsonException $e) {
        $admin = false;
    }
}

if(!$status->application && Config::get('payutc_key', false)) {
    $payutcClient->loginApp(array("key" => Config::get('payutc_key')));
} 

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
$app->get('/shotgun/:id', function() use($app) {
    $app->render('shotgun.php', array());
});

/*
    ADMIN ZONE
*/

$app->get('/createshotgun', function() use($app, $admin, $status) {
    $payutcClient = new AutoJsonClient(Config::get('payutc_server'), "GESARTICLE", array(), "Payutc Json PHP Client", isset($_SESSION['payutc_cookie']) ? $_SESSION['payutc_cookie'] : "");
    if(!isset($_GET["fun_id"])) {
        $app->redirect("admin");
    } else {
        $fun_id = $_GET["fun_id"];
    }  
    try {
        $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$fun_id));
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }
    $desc = new Desc();
    $form = $desc->getForm("Création d'un shotgun", "createshotgun?fun_id=".$fun_id, "Créer");
    $app->render('header.php', array());
    $app->render('form.php', array(
        "form" => $form
    ));
    $app->render('footer.php');
});

$app->post('/createshotgun', function() use($app, $admin, $status) {
    $payutcClient = new AutoJsonClient(Config::get('payutc_server'), "GESARTICLE", array(), "Payutc Json PHP Client", isset($_SESSION['payutc_cookie']) ? $_SESSION['payutc_cookie'] : "");
    if(!isset($_GET["fun_id"])) {
        $app->redirect("admin");
    } else {
        $fun_id = $_GET["fun_id"];
    }  
    try {
        $payutcClient->checkRight(array("user">true, "app"=>false, "fun_check"=>true, "fun_id"=>$fun_id));
    } catch(JsonException $e) {
        $app->flash('info', 'Vous n\'avez pas les droits suffisants.');
        $app->redirect("admin");
    }
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
        $id = $desc->insert();
    } catch (\Exception $e) {
        $app->flashNow('info', "Une erreur est survenu, la création du shotgun à échoué. => {$e->getMessage()}");
        $app->render('header.php', array());
        $app->render('form.php', array(
            "form" => $form
        ));
        $app->render('footer.php');
        return;
    }
    $app->redirect("adminshotgun?id=".$id);
});

// Admin panel, welcome page
$app->get('/admin', function() use($app, $admin, $status) {
    $payutcClient = new AutoJsonClient(Config::get('payutc_server'), "GESARTICLE", array(), "Payutc Json PHP Client", isset($_SESSION['payutc_cookie']) ? $_SESSION['payutc_cookie'] : "");
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
        $_SESSION['payutc_cookie'] = $payutcClient->cookie;
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
        $user = $payutcClient->loginCas(array("ticket" => $_GET["ticket"], "service" => $_SESSION['service']));
        $_SESSION['payutc_cookie'] = $payutcClient->cookie;
        $_SESSION['username'] = $user;
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
        $app->response->redirect(isset($_GET['goto']) ? $_GET['goto'] : "index", 303);
    }
});

/*
    Installation/Configuration zone
*/

// Install options
$app->get('/install', function() use($app, $payutcClient, $admin, $status) {
    // Remove flash (we are on the good page to install/configure system)
    $app->flashNow('info', null);
    $app->render('header.php', array());
    if($admin) {
        $app->render('install.php', array());
    } else {
        $app->render('install_not_admin.php', array(
            "status" => $status,
            "debug" => $payutcClient->cookie));
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
    $app->redirect('install');
});

// Declare payutc app
$app->get('/installpayutc', function() use($app, $payutcClient, $admin) {
    $payutcClient = new AutoJsonClient(Config::get('payutc_server'), "KEY", array(), "Payutc Json PHP Client", isset($_SESSION['payutc_cookie']) ? $_SESSION['payutc_cookie'] : "");
    if($admin) {
        $app = $payutcClient->registerApplication(
            array(
                "app_url"  =>Config::get('self_url'), 
                "app_name" =>Config::get('title')." déclaré par {$_SESSION['username']}", 
                "app_desc" =>"Microbilletterie"));
        Config::set('payutc_key', $app->app_key);
    }
    $app->response->redirect("install", 303);
});

$app->run();