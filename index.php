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

$app = new \Slim\Slim();

// Set default data for view
$app->view->setData(array(
    'color' => 'red',
    'size' => 'medium'
));

// Welcome page, list all current Shotguns
$app->get('/', function() use($app) {
    $app->render('index.php', array());
});

// Show a specific shotgun page
$app->get('/shotgun/:id', function() use($app) {
    $app->render('shotgun.php', array());
});

// Admin panel, welcome page
$app->get('/admin', function() use($app) {
    $app->render('admin.php', array());
});

// Install process
$app->get('/install', function() use($app) {
    $app->render('install.php', array());
});

$app->run();