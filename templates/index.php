<div class="jumbotron">
<h1><?php echo $title; ?></h1>
<p class="lead">Bienvenue sur la microbilletterie utcéenne.</p>
<p>Vous trouverez ci-dessous la liste des shotguns à venir.</p>
</div>

<div class="row marketing">
<div class="col-lg-12">
  <?php foreach($shotguns as $shotgun): ?>
  <h4><?php echo $shotgun->titre; ?></h4>
  Ouverture des ventes dans: XX minutes XX secondes. <br />
  <?php echo $shotgun->desc; ?>

  <?php endforeach; ?>
  <?php if(count($shotguns) == 0): ?>
    <h4>Il n'y a aucun shotgun à afficher, reviens plus tard... </h4>
  <?php endif; ?>
</div>
</div>