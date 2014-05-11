<div class="jumbotron">
<h1><?php echo $title; ?></h1>
<p class="lead">Bienvenue sur la microbilletterie utcéenne.</p>
<p>Vous trouverez ci-dessous la liste des shotguns à venir.</p>
</div>

<div class="row marketing">
<div class="col-lg-12">
  <?php $i = 0; foreach($shotguns as $shotgun): 
  	if($shotgun->is_public != 1) { continue; }
   ?>
  <h4><?php echo $shotgun->titre; ?></h4>
  <?php echo $shotgun->desc; ?>
  <a href="shotgun?id=<?php echo $shotgun->id; ?>" class="btn btn-primary pull-right">Accéder à l'événement</a><br /><br />
  <?php
  	$debut = new DateTime($shotgun->debut);
  	$fin = new DateTime($shotgun->fin);
  	$now = new DateTime("NOW");
  	$diff = $now->diff($debut);
  	if($diff->invert) {
  		if($fin > $now) {
  			echo "Vente terminé.";
  		} else {
  			echo "Vente en cours !";
  		}
  	} else {
  		$i+=1;
  		echo "Ouverture dans : ";
  		echo '<div id="Countdown'.$i.'"></div>';
  		echo '<script> var c'.$i.' = '. (((($diff->d * 24 + $diff->h) * 60) + $diff->i) * 60 + $diff->s) . '; </script>';
  	}
  ?><br />


  <?php endforeach; ?>
  <?php if(count($shotguns) == 0): ?>
    <h4>Il n'y a aucun shotgun à afficher, reviens plus tard... </h4>
  <?php endif; ?>
</div>
</div>