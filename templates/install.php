<?php
use Shotgunutc\Config;

?>
<div class="row">
<div class="col-md-12">
<form role="form" action="install" method="POST">
  <?php foreach(Config::$default as $item) : ?>
    <div class="form-group">
      <label for="<?php echo $item[0]; ?>"><?php echo $item[1]; ?></label>
      <input type="text" class="form-control" name="<?php echo $item[0]; ?>" value="<?php echo Config::get($item[0], ""); ?>" >
    </div>
  <? endforeach; ?>
  <button type="submit" class="btn btn-primary">Enregistrer</button> <a class="btn btn-default" href="installpayutc">Générer une application payutc</a>
</form>
<br />
</div>
</div>