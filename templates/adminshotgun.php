<div class="jumbotron">
<h1><?php echo $shotgun->titre; ?></h1>
<p class="lead">Gestion du shotgun.</p>
</div>

<div class="row marketing">
<div class="col-lg-12">
    <a class="btn btn-primary pull-right" href="editshotgun?id=<?php echo $shotgun->id; ?>" >Modifier</a>
    <h2>Paramètres globaux</h2>
    <strong>Titre du shotgun : </strong><?php echo $shotgun->titre; ?><br />
    <strong>Description du shotgun : </strong><br /><?php echo $shotgun->desc; ?><br />
    <strong>Shotgun public : </strong><?php echo $shotgun->is_public ? "oui" : "non"; ?><br />
    <strong>Shotgun ouvert au non cotisant : </strong><?php echo $shotgun->open_non_cotisant ? "oui" : "non"; ?><br />
    <strong>Ouverture des ventes : </strong><?php echo $shotgun->debut; ?><br />
    <strong>Fermeture des ventes : </strong><?php echo $shotgun->fin; ?><br />

    <a class="btn btn-primary pull-right" href="addchoice?id=<?php echo $shotgun->id; ?>" >Ajouter</a>
    <h2>Choix</h2>
    <table class="table">
        <thead>
            <th>Nom du choix</th>
            <th>Prix</th>
            <th>Nombre de place Shotguné</th>
            <th>Nombre de place en cours de shotgun</th>
            <th>Nombre de place Dispo</th>
            <th>Nombre de place Total</th>
            <th>Outils</th>
        </thead>
        <?php foreach($shotgun->getChoices() as $choice) { ?>
            <tr>
                <td><?php echo $choice->getName(); ?></td>
                <td><?php echo $choice->getPrice(); ?></td>
                <td><?php echo $choice->getNbPlace('V'); ?></td>
                <td><?php echo $choice->getNbPlace('W'); ?></td>
                <td><?php echo $choice->getNbPlace('A'); ?></td>
                <td><?php echo $choice->getNbPlace('T'); ?></td>
                <td>TODO</td>
            </tr>
        <?php } ?>
    </table>
    <h2>Outils</h2>
    En construction [au programme: Export CSV, Envoi de mail aux inscrits].
</div>
</div>