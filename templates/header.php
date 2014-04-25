<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="shortcut icon" href="static/favicon.ico">

        <title><?php echo $title; ?></title>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

        <!-- Optional theme -->
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

        <!-- Custom styles for this template -->
        <link href="static/css.css" rel="stylesheet">

        <!-- Just for debugging purposes. Don't actually copy this line! -->
        <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>

    <div class="container">
    <div class="header">
    <ul class="nav nav-pills pull-right">
      <li <?php if (isset($active) && $active == "index") { ?> class="active" <?php } ?>><a href="index">Home</a></li>
      <li <?php if (isset($active) && $active == "about") { ?> class="active" <?php } ?>><a href="about">A propos</a></li>
      <?php if(isset($_SESSION['username'])): ?>
        <li class="alert-danger"><a href="logout">Deconnexion</a></li>
      <?php endif; ?>
    </ul>
    <h3 class="text-muted"><?php echo $title; ?></h3>
    </div>

    <?php
        // Handle Flash message
        if(isset($flash['info'])) {
            echo '<div class="alert alert-info">'.$flash['info'].'</div>';
        }
    ?>