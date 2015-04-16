<?php
use \Shotgunutc\Option;
?>
<div class="jumbotron">
<h1><?php echo $desc->titre; ?></h1>
<p class="lead"><?php echo $desc->desc; ?></p>
</div>

<div class="row marketing">
<div class="col-lg-12">

<?php
    $has_place = false;
    if(!$username) {
        echo '<div class="alert alert-info">Avant toute chose tu dois te connecter ! <a class="btn btn-primary" href="login">Connexion</a></div>';
    } else {
        $opt = Option::getUser($user->login, $desc->id);
        if(count($opt) > 0) {
            $has_place = true;
            $o = $opt[0];
            if($o->status == 'W') {
                $o->checkStatus($payutcClient, $desc->payutc_fun_id);
            }
            if($o->status == 'V') {
                echo '<div class="alert alert-info">Félicitation '.$user->prenom.' '.$user->nom.', Tu as une place pour cet événement. Les organisateurs te contacteront par mail très prochainement.</div>';
            } else {
                echo '<div class="alert alert-info">'.$user->prenom.' '.$user->nom.', Une place est en cours de réservation avec tes identifiants.
                Tu peux <a class="btn btn-primary" href="'.$o->payutc_tra_url.'">retourner sur payutc</a> pour terminer le paiement ou <a class="btn btn-danger" href="cancel?id='.$desc->id.'">annuler ta commande</a> (Dans tous les cas si tu ne paies pas dans les 15 minutes à venir ta commande sera automatiquement annulée).</div>';
            }
        }
    }
    $debut = new DateTime($desc->debut);
    $fin = new DateTime($desc->fin);
    $now = new DateTime("NOW");
    $diff = $now->diff($debut);
    if($diff->invert) {
        if($now->diff($fin)->invert) {
            echo "Vente terminée.";
        } else {
            ?>
                <h2>Choisis ta place !</h2>
                <p>
                Attention ! Il est possible qu'il apparaisse à un moment donné qu'il n'y a plus de place disponible puis que dans la minute d'après une place apparaisse.
                C'est tout simplement lié au fait que des personnes peuvent sélectionner une place, puis finalement annuler au moment du paiement.
                </p>
                <table class="table">
                <thead>
                    <th>Nom du choix</th>
                    <th>Prix</th>
                    <th></th>
                </thead>
                <?php foreach($desc->getChoices() as $choice) { ?>
                    <tr>
                        <td><?php echo $choice->name; ?></td>
                        <td><?php echo $choice->price/100; ?> €</td>
                        <td>
                        <?php if($has_place) { ?>
                            <a href="" class="btn btn-danger disabled">Tu as déjà une place !</a>
                        <?php } else if($choice->isAvailable()) { ?>
                            <a href="makeshotgun?id=<?php echo $desc->id; ?>&choice_id=<?php echo $choice->id; ?>" class="btn btn-primary">Shotgun !</a>
                        <?php } else { ?>
                            <a href="" class="btn btn-danger disabled">Complet !</a>
                        <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
            <?php
        }
    } else {
        echo "Ouverture dans : ";
        echo '<div id="Countdown1"></div>';
        echo '<script> var c1 = '. (((($diff->d * 24 + $diff->h) * 60) + $diff->i) * 60 + $diff->s) . '; </script>';
    }
?>



</div>
</div>