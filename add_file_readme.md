// controllers/ok_add.php
$titulo = (isset($_POST["titulo"]) && $_POST["titulo"] != "") ? clean($_POST["titulo"]) : false;

// controllers/ok_edit.php
$titulo = (isset($_POST["titulo"]) && $_POST["titulo"] != "") ? clean($_POST["titulo"]) : false;

// models/Test.php
public $_titulo;

// views/form_add.php
<?php # titulo ?>
<div class="form-group">
    <label class="control-label col-sm-3" for="titulo"><?php _t("Titulo"); ?></label>
    <div class="col-sm-8">
        <input type="text"   name="titulo" class="form-control" id="titulo" placeholder="titulo" value="" >
    </div>	
</div>
<?php # /titulo ?>

// views/form_add.php
    <?php # titulo ?>
<div class="form-group">
    <label class="control-label col-sm-3" for="titulo"><?php _t("Titulo"); ?></label>
    <div class="col-sm-8">
        <input type="text"   name="titulo" class="form-control" id="titulo" placeholder="titulo" value="" >
    </div>	
</div>
    <?php # /titulo ?>


// views/form_delete.php
    <?php # titulo ?>
<div class="form-group">
    <label class="control-label col-sm-3" for="titulo"><?php _t("Titulo"); ?></label>
    <div class="col-sm-8">
        <input type="text"   name="titulo" class="form-control" id="titulo" placeholder="titulo" value="<?php echo $test['titulo']; ?>"  disabled="" >
    </div>	
</div>
    <?php # /titulo ?>

// views/form_details.php
    <?php # titulo ?>
<div class="form-group">
    <label class="control-label col-sm-3" for="titulo"><?php _t("Titulo"); ?></label>
    <div class="col-sm-8">
        <input type="text"   name="titulo" class="form-control" id="titulo" placeholder="titulo" value="<?php echo $test['titulo']; ?>"  disabled="" >
    </div>	
</div>
    <?php # /titulo ?>


// views/form_edit.php
<?php # titulo ?>
<div class="form-group">
    <label class="control-label col-sm-3" for="titulo"><?php _t("Titulo"); ?></label>
    <div class="col-sm-8">
        <input type="text"   name="titulo" class="form-control" id="titulo" placeholder="titulo" value="<?php echo $test['titulo']; ?>" >
    </div>	
</div>
<?php # /titulo ?>

// function.php
line 65
. "titulo=:titulo , "
line 72
"titulo" => $titulo,
line 78
 `titulo` ,
 line 79
 :titulo ,
 line 83
"titulo" => $titulo,
line 99
OR titulo like :txt
