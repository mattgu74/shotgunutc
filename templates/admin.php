<div class="jumbotron">
    <h1>Administration</h1>
</div>

<div class="row marketing">
    <?php foreach($fundations as $fun): ?>
        <div class="col-lg-6">
        <?php if($fun->fun_id == null): ?>
            <h4>Super administration</h4>
            <ul>
                <li><a href="install">Configuration du système</a></li>
            </ul>
            <br /><br />
        <?php else: ?>
            <a href="createshotgun?fun_id=<?php echo $fun->fun_id; ?>" class="btn btn-primary pull-right">Créer un shotgun</a>
            <h4><?php echo $fun->name; ?></h4>
            <ul>
            <?php $c = 0; foreach($shotguns as $shotgun) { 
                if($shotgun->payutc_fun_id == $fun->fun_id) { 
                    $c += 1; ?>
                <li><a href="adminshotgun?id=<?php echo $shotgun->id; ?>"><?php echo $shotgun->titre; ?></a></li>
            <?php } } if($c==0) { ?>
                <li>Aucun shotgun crée pour l'instant !</li>
            <?php } ?>
            </ul>
            <br /><br />
        <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
