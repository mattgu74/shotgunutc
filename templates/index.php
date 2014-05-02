<div class="jumbotron">
<h1><?php echo $title; ?></h1>
<p class="lead">Bienvenue sur la microbilletterie utcéenne.</p>
<p>Vous trouverez ci-dessous la liste des shotguns à venir.</p>
</div>

<div class="row marketing">
<div class="col-lg-12">
  <?php foreach($shotguns as $shotgun): ?>
  <h4>Navette Gala UTT</h4>
  <p>Le BDE organise 150 places pour aller au gala UTT. Premier arrivée, premier servi !</p>

  <?php endforeach; ?>
  <?php if(count($shotguns) == 0): ?>
    <h4>Il n'y a aucun shotgun à afficher, reviens plus tard... </h4>
  <?php endif; ?>
</div>
</div>