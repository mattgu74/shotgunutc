<div class="jumbotron">
<h1><?php echo $desc->titre; ?></h1>
<p class="lead"><?php echo $desc->desc; ?></p>
</div>

<?php
    if(!$username) {
        echo '<div class="alert alert-info">Avant toute chose tu dois te connecter ! <a class="btn btn-primary" href="login">Connexion</a></div>';
    } else if($desc->open_non_cotisant == 0 && $user->is_cotisant != 1) {
        echo '<div class="alert alert-info">Cet événement n\'est pas ouvert au non-cotisant. 
        Si tu veux prendre ta place, tu dois d\'abord aller cotiser 
        <a class="btn btn-primary" href="https://assos.utc.fr/bde/bdecotiz/">BDE COTIZ</a></div>';
    } else {
        $debut = new DateTime($desc->debut);
        $fin = new DateTime($desc->fin);
        $now = new DateTime("NOW");
        $diff = $now->diff($debut);
        if($diff->invert) {
            if($fin > $now) {
                echo "Vente terminé.";
            } else {
                echo "Vente en cours !";
            }
        } else {
            echo "Ouverture dans : ";
            echo '<div id="Countdown1"></div>';
            echo '<script> var c1 = '. (((($diff->d * 24 + $diff->h) * 60) + $diff->i) * 60 + $diff->s) . '; </script>';
        }


    }
?>

<div class="row marketing">
<div class="col-lg-12">

</div>
</div>