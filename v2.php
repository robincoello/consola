<?php

function bdd_referencias($tabla, $columna) {

    global $db;
    $data = null;
    $req = $db->prepare("            
             SELECT 
             REFERENCED_TABLE_NAME, 
             REFERENCED_COLUMN_NAME 
             FROM 
             INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE Table_name = :tabla AND COLUMN_NAME = :columna
            ");
    $req->execute(array(
        "tabla" => $tabla,
        "columna" => $columna
    ));
    $data = $req->fetch();
    return $data;
}

function bdd_columnas_segun_tabla($tabla) {
    global $db;
    $data = null;
    $req = $db->prepare("            
             SHOW COLUMNS FROM $tabla
            ");
    $req->execute(array($tabla));
    $data = $req->fetchAll();
    return $data;
    //return array("id", "contact_id" );
}

function bdd_total_columnas_segun_tabla($tabla) {
    global $db;
    $data = null;
    $req = $db->prepare("            
             SELECT * FROM $tabla
            ");
    $req->execute();

    $data = $req->columnCount();

    return $data;
}

function bdd_add_controllers($plugin) {
    global $db;

    $req = $db->prepare("            
             INSERT INTO `controllers` (`id`, `controller`, `title`, `description`) VALUES (null, :plugin , '', ''); 
            ");
    $req->execute(array(
        "plugin" => "$plugin"
    ));

    //echo $db;

    return $db->lastInsertId();
    return $plugin;
}

function bdd_add_permissions($plugin, $rol, $permiso) {
    global $db;

    $req = $db->prepare("            
             INSERT INTO `permissions` (`id`, `rol`, `controller`, `crud`) VALUES (NULL, :rol, :plugin, :permiso);
            ");
    $req->execute(array(
        "rol" => $rol,
        "plugin" => "$plugin",
        "permiso" => $permiso
    ));
}

function bdd_add_en_menu($location, $father, $label, $url, $icon, $order_by) {
    global $db;

    $req = $db->prepare("            
             INSERT INTO `_menus` (`id`, `location`, `father`, `label`, `url`, `icon`, `order_by`) 
                            VALUES (:id, :location, :father, :label, :url, :icon, :order_by);
            ");
    $req->execute(array(
        "id" => null,
        "location" => $location,
        "father" => $father,
        "label" => $label,
        "url" => $url,
        "icon" => $icon,
        "order_by" => $order_by
    ));
    return $db->lastInsertId();
}

function bdd_add_en_magia($base_datos, $tabla, $campo, $accion, $label, $tipo, $tabla_externa, $columna_externa, $clase, $nombre, $identificador, $marca_agua, $valor, $solo_lectura = null, $obligatorio = null, $seleccionado = null, $desactivado = null, $orden = null, $estatus = null) {
    global $db;

    $req = $db->prepare("
           
            INSERT INTO `magia` (`id`, `base_datos`, `tabla`, `campo`, `accion`, `label`, `tipo`, `tabla_externa`, `columna_externa`, `clase`, `nombre`, `identificador`, `marca_agua`, `valor`, `solo_lectura`, `obligatorio`, `seleccionado`, `desactivado`, `orden`, `estatus`)                         
                         VALUES (:id,  :base_datos,  :tabla,  :campo,  :accion,  :label,  :tipo,  :tabla_externa,  :columna_externa,  :clase, :nombre, :identificador, :marca_agua, :valor, :solo_lectura, :obligatorio, :seleccionado, :desactivado, :orden, :estatus);
           

           ");
    $req->execute(array(
        "id" => null,
        "tabla" => $tabla,
        "base_datos" => $base_datos,
        "campo" => $campo,
        "accion" => $accion,
        "label" => $label,
        "tipo" => $tipo,
        "tabla_externa" => $tabla_externa,
        "columna_externa" => $columna_externa,
        "clase" => $clase,
        "nombre" => $nombre,
        "identificador" => $identificador,
        "marca_agua" => $marca_agua,
        "valor" => $valor,
        "solo_lectura" => $solo_lectura,
        "obligatorio" => $obligatorio,
        "seleccionado" => $seleccionado,
        "desactivado" => $desactivado,
        "orden" => $orden,
        "estatus" => $estatus
    ));
}

function contenido_controllers($plugin, $archivo) {

    switch ($archivo) {
        ## add.php
        case "add.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "create")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}


include view("' . $plugin . '", "add");                 
';

            break;


        ## ok_add.php
        ## ok_add.php
        ## ok_add.php
        ## ok_add.php
        ## ok_add.php
        ## ok_add.php
        case "ok_add.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "create")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                if ($columna['Field'] != 'id') {
                    $contenido .= '$' . $columna['Field'] . ' = (isset($_POST["' . $columna['Field'] . '"]) && $_POST["' . $columna['Field'] . '"] !="") ? clean($_POST["' . $columna['Field'] . '"]) : false;' . "\n";
                }
            }
            $contenido .= '  
$error = array();
################################################################################
# REQUERIDO
################################################################################
';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                if ($columna['Field'] != 'id') {
                    //$contenido .= '$text = (isset($_POST["'.$columna['Field'].'"])) ? clean($_POST["'.$columna['Field'].'"]) : false;';                                                                         
                    $contenido .= 'if (!$' . $columna['Field'] . ') {
    array_push($error, \'$' . $columna['Field'] . ' not send\');
}' . "\n";
                }
            }
            $contenido .= '
###############################################################################
# FORMAT
################################################################################
//
###############################################################################
# CONDICIONAL
################################################################################
';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                if ($columna['Key'] == 'UNI') {
                    $contenido .= '
if( ' . $plugin . '_search_by_unique("id","' . $columna['Field'] . '", $' . $columna['Field'] . ')){
    array_push($error, \'' . $columna['Field'] . ' already exists in data base\');
}
' . "\n";
                }
            }
            $contenido .= '  
if( ' . $plugin . '_search($' . $columna['Field'] . ')){
    //array_push($error, "That text with that context the database already exists");
}
################################################################################
################################################################################
################################################################################
################################################################################
if (!$error) {
    $lastInsertId = ' . $plugin . '_add(
        ';
            $i = 0;
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

                $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
                if ($columna['Field'] != 'id') {
                    $contenido .= '$' . $columna['Field'] . ' ' . $coma . '  ';
                }

                $i++;
            }
            $contenido .= ' 
    );
              
    header("Location: index.php?c=' . $plugin . '&a=details&id=$lastInsertId");

    
} else {

    include view("home", "pageError");
}


';
            break;



        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        case "delete.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "delete")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$id    = (isset($_GET["id"]) && $_GET["id"] !="" )         ? clean($_GET["id"]) : false;


$error = array();

###############################################################################
# REQUERIDO
################################################################################
if (! $id) {
    array_push($error, "ID Don\'t send");
}
###############################################################################
# FORMAT
################################################################################
if (! ' . $plugin . '_is_id($id)) {
    array_push($error, \'ID format error\');
}
###############################################################################
# CONDICIONAL
################################################################################
if (! ' . $plugin . '_field_id("id", $id)) {
    array_push($error, "id not exist");
}
################################################################################
$' . $plugin . ' = ' . $plugin . '_details($id);


include view("' . $plugin . '", "delete");  
';
            break;



        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        case "ok_delete.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "delete")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$id    = (isset($_POST["id"]) && $_POST["id"] !="" )         ? clean($_POST["id"]) : false;


$error = array();
###############################################################################
# REQUERIDO
################################################################################
if (! $id) {
    array_push($error, "ID Don\'t send");
}
###############################################################################
# FORMAT
################################################################################
if (! ' . $plugin . '_is_id($id)) {
    array_push($error, \'Id format error\');
}
###############################################################################
# CONDICIONAL
################################################################################



################################################################################
################################################################################
################################################################################

if ( !$error) {
    
        ' . $plugin . '_delete(
                $id
        );

        header("Location: index.php?c=' . $plugin . '");
         
}

include view("' . $plugin . '", "delete");  
';

            break;



        ## details.php
        ## details.php
        ## details.php
        ## details.php
        ## details.php
        ## details.php
        ## details.php
        ## details.php
        case "details.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$id    = (isset($_REQUEST["id"]) && $_REQUEST["id"] !="" )      ? clean($_REQUEST["id"]) : false;

$error = array();

###############################################################################
# REQUERIDO
################################################################################
if (! $id) {
    array_push($error, "ID Don\'t send");
}
###############################################################################
# FORMAT
################################################################################
if (! ' . $plugin . '_is_id($id)) {
    array_push($error, \'ID format error\');
}
###############################################################################
# CONDICIONAL
################################################################################
if (! ' . $plugin . '_field_id("id", $id)) {
    array_push($error, "id not exist");
}
################################################################################
################################################################################
################################################################################
################################################################################
if (!$error) {
    $' . $plugin . ' = ' . $plugin . '_details($id);    
    include view("' . $plugin . '", "details");      
} else {   
     include view("home", "pageError");      
}

';
            break;


        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        case "edit.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "update")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$id = (isset($_REQUEST["id"]) && $_REQUEST["id"] !="" ) ? clean($_REQUEST["id"]) : false;

$error = array();


###############################################################################
# REQUERIDO
################################################################################
if (! $id) {
    array_push($error, "ID Don\'t send");
}
###############################################################################
# FORMAT
################################################################################
if (! ' . $plugin . '_is_id($id)) {
    array_push($error, \'ID format error\');
}
###############################################################################
# CONDICIONAL
################################################################################
if (! ' . $plugin . '_field_id("id", $id)) {
    array_push($error, "id not exist");
}
################################################################################
################################################################################
################################################################################
if (!$error) {
    $' . $plugin . ' = ' . $plugin . '_details($id);
    
    include view("' . $plugin . '", "edit");      
} else {
    
     include view("home", "pageError");      
}

';
            break;



        ## ok_edit.php
        ## ok_edit.php
        ## ok_edit.php
        ## ok_edit.php
        ## ok_edit.php
        ## ok_edit.php
        case "ok_edit.php":
            $contenido = '<?php
if (!permissions_has_permission($u_rol, $c, "update")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}
// Recolection vars


';
            $i = 0;
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $coma = ($i < bdd_total_columnas_segun_tabla($plugin) ) ? "," : "";

                //$contenido .= '$'.$columna['Field'].' '.$coma.'  ';                                                                         
                $contenido .= '$' . $columna['Field'] . ' = (isset($_POST["' . $columna['Field'] . '"]) && $_POST["' . $columna['Field'] . '"] !="" ) ? clean($_POST["' . $columna['Field'] . '"]) : false;' . "\n";

                $i++;
            }
            $contenido .= ' 



$error = array();

###############################################################################
# REQUERIDO
################################################################################
if (! $id) {
    array_push($error, "ID Don\'t send");
}
###############################################################################
# FORMAT
################################################################################
if (! ' . $plugin . '_is_id($id)) {
    array_push($error, \'ID format error\');
}
###############################################################################
# CONDICIONAL
################################################################################
if (! ' . $plugin . '_field_id("id", $id)) {
    array_push($error, "id not exist");
}
################################################################################
if (! $error ) {
    
    ' . $plugin . '_edit(
        
';
            $i = 1;
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $coma = ($i < bdd_total_columnas_segun_tabla($plugin) ) ? "," : "";


                $contenido .= '$' . $columna['Field'] . ' ' . $coma . '  ';

                $i++;
            }
            $contenido .= ' 



                
        );
        header("Location: index.php?c=' . $plugin . '&a=details&id=$id");
          
}

$' . $plugin . ' = ' . $plugin . '_details($id);
    
include view("' . $plugin . '", "index");  
';
            break;


        ## export_json.php
        ## export_json.php
        ## export_json.php
        ## export_json.php
        ## export_json.php
        ## export_json.php
        ## export_json.php
        case "export_json.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}


$error = array();
$' . $plugin . ' = null;
$' . $plugin . ' = ' . $plugin . '_list();
//
include view("' . $plugin . '", "export_json");  
    
if ($debug) {
    include "www/' . $plugin . '/views/debug.php";
}';
            break;

        ## export_pdf.php
        ## export_pdf.php
        ## export_pdf.php
        ## export_pdf.php
        ## export_pdf.php
        case "export_pdf.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}
$error = array();
$' . $plugin . ' = null;
$' . $plugin . ' = ' . $plugin . '_list();
//
include view("' . $plugin . '", "export_pdf");      
if ($debug) {
    include "www/' . $plugin . '/views/debug.php";
}';
            break;







        ## index.php
        ## index.php
        ## index.php
        ## index.php
        case "index.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$error = array();
################################################################################
$' . $plugin . ' = null;
$' . $plugin . ' = ' . $plugin . '_list(10, 5);
    

include view("' . $plugin . '", "index");  
if ($debug) {
    include "www/' . $plugin . '/views/debug.php";
}';
            break;


        ## search.php
        ## search.php
        ## search.php
        ## search.php
        ## search.php
        case "search.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}
$' . $plugin . ' = null;
$w = (isset($_GET["w"]) && $_GET["w"] !="") ? clean($_GET["w"]) : false;
$error = array();

################################################################################
################################################################################
switch ($w) {
    case "id":
        $txt = (isset($_GET["txt"]) && $_GET["txt"] !="" ) ? clean($_GET["txt"]) : false;        
        $' . $plugin . ' = ' . $plugin . '_search_by_id($txt);
        break;

    default:
        $txt = (isset($_GET["txt"]) && $_GET["txt"] !="" ) ? clean($_GET["txt"]) : false;
        $' . $plugin . ' = ' . $plugin . '_search($txt);
        break;
}


include view("' . $plugin . '", "index");      
';
            break;



        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        case "search_advanced.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}


include view("' . $plugin . '", "search_advanced");      
';
            break;

        default:
            $contenido = "------------";
            break;
    }

    return "$contenido";
}

function contenido_clase($plugin, $archivo) {

    $contenido = '<?php
 // ' . $plugin . '
 // Date: ' . date('Y-m-d') . '    
################################################################################

class ' . ucfirst($plugin) . ' {

';

    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        // if ($columna['Field'] != 'id') {

        $contenido .= "    /** \n";
        $contenido .= "    * " . $columna['Field'] . "\n";
        $contenido .= "    */ \n";
        $contenido .= "    public \$_" . $columna['Field'] . ";\n";

        //  }
    }


    $contenido .= '   

    function __construct() {
        //' . "\n";

    $contenido .= '}

################################################################################
';
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        // if ($columna['Field'] != 'id') {
        //$contenido .= '$' . $columna['Field'] . ' = (isset($_POST["' . $columna['Field'] . '"])) ? clean($_POST["' . $columna['Field'] . '"]) : false;' . "\n" ;
        $contenido .= "    function get" . ucfirst($columna['Field']) . " () {" . "\n";
        $contenido .= "        return \$this->_$columna[Field]; " . "\n";
        $contenido .= '    }' . "\n";
        //  }
    }
    $contenido .= '
################################################################################
';
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        // if ($columna['Field'] != 'id') {
        //
        $contenido .= "    function set" . ucfirst($columna['Field']) . " ($" . ($columna['Field']) . ") {" . "\n";
        $contenido .= "        \$this->_$columna[Field] = $" . ($columna['Field']) . "; " . "\n";
        $contenido .= '    }' . "\n";
        //  }
    }



    $contenido .= '   

    function set' . ucfirst($plugin) . '($id) {
        $' . $plugin . ' = ' . $plugin . '_details($id);
        //' . "\n";

    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $contenido .= '        $this->_' . $columna['Field'] . ' = $' . $plugin . '["' . $columna['Field'] . '"];' . "\n";
    }


    $contenido .= '}';

    $contenido .= '

}
################################################################################
';

    return "$contenido";
}

/**
 * El contenido de la carpeta views
 * 
 * @global type $config_destino
 * @param type $plugin
 * @param type $archivo
 * @return type
 */
function contenido_views($plugin, $archivo) {
    global $config_destino;
    switch ($archivo) {
        ## add.php
        ## add.php
        ## add.php
        ## add.php
        case "add.php":
            $contenido = '<?php include view("home", "header"); ?>  

<div class="row">
    <div class="col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php //include view("' . $plugin . '", "izq_add"); ?>' :
                    '<?php //include "izq.php"; ?>';
            $contenido .= '</div>

    <div class="col-sm-12 col-md-10 col-lg-10">

        <h1>
            <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
            <?php _t("Add ' . $plugin . '"); ?>
        </h1>

        ';

            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "form_add"); ?>' :
                    '<?php include "form_add.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-2 col-lg-2">
    
            ';
            $contenido .= ($config_destino == "www") ?
                    '<?php // include view("' . $plugin . '", "der_add"); ?>' :
                    '<?php // include "der.php"; ?>';


            $contenido .= '
        
    </div>
</div>


 
<?php include view("home", "footer"); ?>

';
            break;

        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        ## delete.php
        case "delete.php":
            $contenido = '<?php include view("home", "header"); ?>

<div class="row">
    <div class="col-sm-12 col-md-2 col-lg-2">
            ';
            $contenido .= ($config_destino == "www") ?
                    '<?php //include view("' . $plugin . '", "izq_delete"); ?>' :
                    '<?php //include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-10 col-lg-10">

        <h1>
            <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
           <?php _t("' . $plugin . ' details"); ?>
        </h1>
        <hr>
        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>

 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "form_delete"); ?>' :
                    '<?php include "form_delete.php"; ?>';
            $contenido .= '

    </div>

    <div class="col-sm-12 col-md-2 col-lg-2">

 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php //include view("' . $plugin . '", "der_delete"); ?>' :
                    '<?php //include "der.php"; ?>';
            $contenido .= '
    </div>
</div>



<?php include view("home", "footer"); ?>

';
            break;

        ## details.php
        ## details.php
        ## details.php
        ## details.php
        ## details.php
        case "details.php":
            $contenido = '<?php  include view("home", "header"); ?> 

<div class="row">
    <div class="col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php // include view("' . $plugin . '", "izq_details"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-10 col-lg-10">

        <h1>
            <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
           <?php _t("' . $plugin . ' details"); ?>
        </h1>
        <hr>
        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>

            

 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "form_details"); ?>' :
                    '<?php include "form_details.php"; ?>';
            $contenido .= '
                


    </div>

    <div class="col-sm-12 col-md-2 col-lg-2">

 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php // include view("' . $plugin . '", "der_details"); ?>' :
                    '<?php // include "der.php"; ?>';
            $contenido .= '
    </div>
</div>



<?php include view("home", "footer"); ?>

';
            break;

        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        ## edit.php
        case "edit.php":
            $contenido = '
<?php include view("home", "header"); ?>                

<div class="row">
    <div class="col-sm-12 col-md-2 col-lg-2">       
         ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-10 col-lg-10">

        <h1>
            <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
            <?php _t("' . ucfirst($plugin) . ' edit"); ?>
        </h1>
        <hr>
        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>
            
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "form_edit"); ?>' :
                    '<?php include "form_edit.php"; ?>';
            $contenido .= '

    </div>

    <div class="col-sm-12 col-md-2 col-lg-2">

        
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php // include view("' . $plugin . '", "der"); ?>' :
                    '<?php // include "der.php"; ?>';
            $contenido .= '
            
    </div>
</div>

<?php include view("home", "footer"); ?>

';
            break;

        ## export_json.php
        ## export_json.php
        ## export_json.php
        ## export_json.php
        ## export_json.php
        case "export_json.php":
            $contenido = '<?php include view("home", "header"); ?>  

<div class="row">
    <div class="col-lg-0">
        
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-lg-12">

        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>

        <pre>
            <?php
            echo json_encode($' . $plugin . ');
            ?>
        </pre>

    </div>
</div>

<?php include view("home", "footer"); ?>

';
            break;

        ## export_pdf.php
        ## export_pdf.php
        ## export_pdf.php
        ## export_pdf.php
        ## export_pdf.php
        case "export_pdf.php":
            $contenido = '<?php
require("includes/fpdf/fpdf.php");

$pdf = new FPDF();
$pdf->AddPage("L");
$pdf->SetFont("Arial","B",16);
$pdf->Cell(40,10,"Hello World !");
$pdf->Output();
';
            break;

        ## form_add.php
        ## form_add.php
        ## form_add.php
        ## form_add.php
        ## form_add.php
        case "form_add.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="post" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_add">
    <input type="hidden" name="redi" value="1">

';

            /*

              foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

              if ($columna['Field'] != 'id') {

              $contenido .= '<?php # '.$columna['Field'].' ?>' . "\n";
              $contenido .= '     <div class="form-group">
              <label class="control-label col-sm-2" for="'.$columna['Field'].'"><?php _t("' . ucfirst($columna['Field']) . '"); ?></label>
              <div class="col-sm-8">'."\n";
              // esto es la creacion del campo en si
              ///
              ///
              ///
              $contenido .= (bdd_referencias($plugin, $columna['Field'])) ? "         " . bdd_campo("select", $columna['Field']) : "          " . bdd_campo($columna['Type'], $columna['Field']);

              $contenido .= "\n       </div>
              </div>" . "\n";
              $contenido .= '<?php # /'.$columna['Field'].' ?>' . "\n\n";
              echo "\n\n";

              }
              }

             */

            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

                //REFERENCED_TABLE_NAME, 
                //REFERENCED_COLUMN_NAME 

                $bdd_referencias = bdd_referencias($plugin, $columna['Field']);

                $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
                $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;

                echo ($bdd_ref_tabla_externa) ? $bdd_ref_tabla_externa . '\n' : false;
                echo ($bdd_col_externa) ? $bdd_col_externa . '\n' : false;



                $campo_select = campos_crear_campo("select", $columna['Field'], $columna['Field']);
                $campo = campos_crear_campo(campos_tipo($columna['Field']), $columna['Field'], $columna['Field']);

                if ($columna['Field'] != 'id') {

                    $contenido .= '    <?php # ' . $columna['Field'] . ' ?>' . "\n";
                    $contenido .= '    <div class="form-group">
        <label class="control-label col-sm-2" for="' . $columna['Field'] . '"><?php _t("' . ucfirst($columna['Field']) . '"); ?></label>
        <div class="col-sm-8">' . "\n";
                    $contenido .= ( $bdd_ref_tabla_externa ) ? "             " . $campo_select : "          " . $campo;
                    $contenido .= "\n       </div>	
    </div>" . "\n";
                    $contenido .= '    <?php # /' . $columna['Field'] . ' ?>' . "\n\n";
                    echo "\n\n";
                }
            }







            $contenido .= '  
    <div class="form-group">
        <label class="control-label col-sm-2" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Save"); ?>">
        </div>      							
    </div>      							

</form>
';
            break;

        ## form_details.php
        ## form_details.php
        ## form_details.php
        ## form_details.php
        ## form_details.php
        case "form_details.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="post" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="edit">
    <input type="hidden" name="id" value="<?php echo "$id"; ?>">
    <input type="hidden" name="redi" value="1">
    


    ';

            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                // $contenido .= 'echo "<td>$' . $plugin . '[' . $columna['Field'] . ']</td>";' . "\n";
                $contenido .= '<?php # ' . $columna['Field'] . ' ?>' . "\n";
                $contenido .= '<div class="form-group">
        <label class="control-label col-sm-2" for="contact_id"><?php _t("' . ucfirst($columna['Field']) . '"); ?></label>
        <div class="col-sm-8">                    
            <input type="' . $columna['Field'] . '" name="' . $columna['Field'] . '" class="form-control"  id="' . $columna['Field'] . '" placeholder="' . $columna['Field'] . '" value="<?php echo "$' . $plugin . '[' . $columna['Field'] . ']"; ?>" disabled="" >
        </div>	
    </div>' . "\n";
                $contenido .= '<?php # ' . $columna['Field'] . ' ?>' . "\n\n";
            }

            $contenido .= '



    <div class="form-group">
        <label class="control-label col-sm-2" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Edit"); ?>">
        </div>      							
    </div>      							

</form>

';
            break;

        ## form_edit.php
        ## form_edit.php
        ## form_edit.php
        ## form_edit.php
        ## form_edit.php
        ## form_edit.php
        case "form_edit.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="post" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_edit">
    <input type="hidden" name="id" value="<?php echo "$id"; ?>">
    <input type="hidden" name="redi" value="1">
    


    ';

            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {


                if ($columna['Field'] != 'id') {
                    // $contenido .= 'echo "<td>$' . $plugin . '[' . $columna['Field'] . ']</td>";' . "\n";
                    $contenido .= '<?php # ' . $columna['Field'] . ' ?>' . "\n";
                    $contenido .= '<div class="form-group">
        <label class="control-label col-sm-2" for="' . $columna['Field'] . '"><?php _t("' . ucfirst($columna['Field']) . '"); ?></label>
        <div class="col-sm-8">                    
            <input type="text" name="' . $columna['Field'] . '" class="form-control"  id="' . $columna['Field'] . '" placeholder="' . $columna['Field'] . '" value="<?php echo "$' . $plugin . '[' . $columna['Field'] . ']"; ?>" >
        </div>	
    </div>' . "\n";
                    $contenido .= '<?php # /' . $columna['Field'] . ' ?>' . "\n\n";
                }
            }

            $contenido .= '



    <div class="form-group">
        <label class="control-label col-sm-2" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Edit"); ?>">
        </div>      							
    </div>      							

</form>

';
            break;

        ## form_delete.php
        ## form_delete.php
        ## form_delete.php
        ## form_delete.php
        ## form_delete.php
        case "form_delete.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="post" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_delete">
    <input type="hidden" name="id" value="<?php echo "$id"; ?>">
    <input type="hidden" name="redi" value="1">
    


    ';

            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                // $contenido .= 'echo "<td>$' . $plugin . '[' . $columna['Field'] . ']</td>";' . "\n";
                $contenido .= '<div class="form-group">
        <label class="control-label col-sm-2" for="contact_id"><?php _t("' . ucfirst($columna['Field']) . '"); ?></label>
        <div class="col-sm-8">                    
            <input type="' . $columna['Field'] . '" name="' . $columna['Field'] . '" class="form-control"  id="' . $columna['Field'] . '" placeholder="' . $columna['Field'] . '" value="<?php echo "$' . $plugin . '[' . $columna['Field'] . ']"; ?>" disabled="" >
        </div>	
    </div>' . "\n";
            }

            $contenido .= '



    <div class="form-group">
        <label class="control-label col-sm-2" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Delete"); ?>">
        </div>      							
    </div>      							

</form>

';
            break;

        ## form_search_advanced.php.php
        ## form_search_advanced.php.php
        ## form_search_advanced.php.php
        ## form_search_advanced.php.php
        ## form_search_advanced.php.php
        case "form_search_advanced.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="get" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="search_advancedOk">
    <input type="hidden" name="redi" value="1">
    
    
   

    ';

            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                // $contenido .= 'echo "<td>$' . $plugin . '[' . $columna['Field'] . ']</td>";' . "\n";


                if ($columna['Field'] != 'id') {
                    $contenido .= '<?php # ' . $columna['Field'] . ' ?>' . "\n";
                    $contenido .= '<div class="form-group">
        <label class="control-label col-sm-2" for="contact_id"><?php _t("' . ucfirst($columna['Field']) . '"); ?></label>
        <div class="col-sm-8">                    
            <input type="text" name="' . $columna['Field'] . '" class="form-control"  id="' . $columna['Field'] . '" placeholder="' . $columna['Field'] . '" value="">
        </div>	
    </div>' . "\n";
                    $contenido .= '<?php # ' . $columna['Field'] . ' ?>' . "\n\n";
                }
            }

            $contenido .= '




    <div class="form-group">
        <label class="control-label col-sm-2" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Save"); ?>">
        </div>      							
    </div>      							

</form>
';
            break;

        ## form_add.php
        ## form_add.php
        ## form_add.php
        ## form_add.php
        ## form_add.php
        case "form_add.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="get" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="search_advanced">
    <input type="hidden" name="redi" value="1">

';

            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

                if ($columna['Field'] != 'id') {

                    $contenido .= '<?php # ' . $columna['Field'] . ' ?>' . "\n";
                    $contenido .= '     <div class="form-group">
        <label class="control-label col-sm-2" for="' . $columna['Field'] . '"><?php _t("' . ucfirst($columna['Field']) . '"); ?></label>
        <div class="col-sm-8">' . "\n";
                    // esto es la creacion del campo en si 
                    ///
                    ///
                    ///
                    $contenido .= (bdd_referencias($plugin, $columna['Field'])) ? "         " . campos_crear_campo("select", $columna['Field']) : "          " . campos_crear_campo($columna['Type'], $columna['Field']);

                    $contenido .= "\n       </div>	
    </div>" . "\n";
                    $contenido .= '<?php # /' . $columna['Field'] . ' ?>' . "\n\n";
                    echo "\n\n";
                }
            }




            $contenido .= '  
    <div class="form-group">
        <label class="control-label col-sm-2" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Search"); ?>">
        </div>      							
    </div>      							

</form>
';
            break;

        ## search.php
        ## search.php
        ## search.php
        ## search.php
        ## search.php
        case "search.php":
            $contenido = '<?php include view("home", "header"); ?> 

<div class="row">
    <div class="col-lg-3">
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>


    <div class="col-lg-9">
        <h1><?php _t("' . $plugin . '"); ?></h1>
        
        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>


        <form class="form-inline" action="index.php" method="get">
            <input type="hidden" name="c" value="' . $plugin . '"> 
            <input type="hidden" name="a" value="search"> 
            <input type="hidden" name="redi" value="1"> 

            <div class="form-group">
                <label class="sr-only" for="txt">Client</label>
                <input class="form-control" type="text" name="txt" id="txt" placeholder="Search">
            </div>
            <button type="submit" class="btn btn-default"><?php _t("Search"); ?></button>
        </form>


        
    </div>
</div>


<?php include view("home", "footer"); ?>


';
            break;

        ## index.php
        ## index.php
        ## index.php
        ## index.php
        ## index.php
        case "index.php":
            $contenido = '<?php include view("home", "header"); ?>  

<div class="row">
    <div class="col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-10 col-lg-10">
        ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "nav"); ?>' :
                    '<?php include "nav.php"; ?>';
            $contenido .= '


        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>



<?php 
// https://api.jquery.com/prop/
?>';


            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "table_index"); ?>' :
                    '<?php include "table_index.php"; ?>';

            $contenido .= '


<?php
/*        
        Export:
        <a href="index.php?c=addresses&a=export_json">JSON</a>
        <a href="index.php?c=addresses&a=export_pdf">pdf</a>
*/
?>


    </div>
</div>

<?php include view("home", "footer"); ?> 

';
            break;

        ## izq.php.php
        ## izq.php.php
        ## izq.php.php
        ## izq.php.php
        ## izq.php.php
        case "izq.php":
            $contenido = '
<div class="list-group">
    <a href="#" class="list-group-item active">
        <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
            <?php echo _t("' . ucfirst($plugin) . '"); ?>
    </a>
    <a href="index.php?c=' . $plugin . '" class="list-group-item"><?php _t("List"); ?></a>
     <a href="index.php?c=' . $plugin . '&a=add" class="list-group-item"><?php _t("Add"); ?></a> 
</div>';
            break;

        ## der.php
        case "der.php":
            $contenido = '
<div class="list-group">
    <a href="#" class="list-group-item active">
        <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
            <?php echo _t("' . ucfirst($plugin) . '"); ?>
    </a>
    <a href="index.php?c=' . $plugin . '" class="list-group-item"><?php _t("List"); ?></a>
     <a href="index.php?c=' . $plugin . '&a=add" class="list-group-item"><?php _t("Add"); ?></a> 
</div>';
            break;

        ## nav.php
        ## nav.php
        ## nav.php
        ## nav.php
        ## nav.php
        case "nav.php":
            $contenido = '
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
        <a class="navbar-brand" href="#">
            <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
            <?php _t("' . ucfirst($plugin) . '"); ?>
        </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        
        
        
      </ul>
        
        
        <form action="index.php" method="get" class="navbar-form navbar-left">
          <input type="hidden" name="c" value="' . $plugin . '">
          <input type="hidden" name="a" value="search">
          <input type="hidden" name="w" value="all">
        <div class="form-group">
            <input type="text" name="txt" class="form-control" placeholder="">
        </div>
        <button type="submit" class="btn btn-default"><?php _t("Search"); ?></button>
      </form>
        
        
        
        
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>';
            break;

        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        ## search_advanced.php
        case "search_advanced.php":
            $contenido = '
<?php include view("home", "header"); ?>                

<div class="row">
    <div class="col-sm-12 col-md-2 col-lg-2">        
         ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-10 col-lg-10">

        <h1>
            <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
            <?php _t("' . ucfirst($plugin) . ' Search advanced"); ?>
        </h1>
        
        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>
       
        
            
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "form_search_advanced"); ?>' :
                    '<?php include "form_search_advanced.php"; ?>';
            $contenido .= '
                

    </div>

    <div class="col-sm-12 col-md-2 col-lg-2">       
         ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "der"); ?>' :
                    '<?php include "der.php"; ?>';
            $contenido .= '
    </div>
</div>

<?php include view("home", "footer"); ?>

';
            break;

        ## xxxxxxx.php
        ## xxxxxxx.php
        ## xxxxxxx.php
        ## xxxxxxx.php
        ## xxxxxxx.php
        ## xxxxxxx.php
        case "xxxxxxxxx.php":
            $contenido = '';
            break;




        ## table_index.php
        ## table_index.php
        ## table_index.php
        ## table_index.php
        ## table_index.php
        case "table_index.php":
            $contenido = '
            <table class="table table-striped">
                <thead>
                    <tr>' . "\n";
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $contenido .= '                      <th><?php _t("' . ucfirst($columna['Field']) . '"); ?></th>' . "\n";
            }
            $contenido .= '                                                                       
                      <th><?php _t("Action"); ?></th>                                                                      
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                        
                        if( ! $' . $plugin . ' ){
                            message("info", "No items"); 
                        }
                        
                        
                        
                        foreach ($' . $plugin . ' as $' . $plugin . ') {

                            
                            $menu=\'<div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                              Actions
                              <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                              <li><a href="index.php?c=' . $plugin . '&a=details&id=\'.$' . $plugin . '["id"].\'">\'._tr("Details").\'</a></li>
                              <li><a href="index.php?c=' . $plugin . '&a=edit&id=\'.$' . $plugin . '["id"].\'">\'._tr("Edit").\'</a></li>
                              <li role="separator" class="divider"></li>
                              <li><a href="index.php?c=' . $plugin . '&a=delete&id=\'.$' . $plugin . '["id"].\'">\'._tr("Delete").\'</a></li>
                            </ul>
                          </div>\'; 
                         //   $photo = addresses_photos_principal($address["id"]);
                         //   $contact_name = contacts_field_id("name", $' . $plugin . '["contact_id"]);
                         //   $contact_lastname = contacts_field_id("lastname", $' . $plugin . '["contact_id"]);
                         ';
            $contenido .= 'echo "<tr id=\\"$' . $plugin . '[id]\\">"; ' . "\n";
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $contenido .= '                         echo "<td>$' . $plugin . '[' . $columna['Field'] . ']</td>";' . "\n";
            }
            $contenido .= '                              
                         echo "<td>$menu</td>";
                            echo "</tr>";
                        }
                        ?>	
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        ';

            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $contenido .= '                     <th><?php _t("' . ucfirst($columna['Field']) . '"); ?></th>' . "\n";
            }


            $contenido .= '                                                                     
                        <th><?php _t("Action"); ?></th>                                                                      
                    </tr>
                </tfoot>
            </table>
';
            break;



        default:
            $contenido = "------------";
            break;
    }


    return "$contenido";
}

function contenido_functions($plugin) {
    $contenido = '<?php
// plugin = ' . $plugin . '
// creation date : 
// 
// 
function ' . $plugin . '_field_id($field, $id) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT $field FROM ' . $plugin . ' WHERE id= ?");
    $req->execute(array($id));
    $data = $req->fetch();
    //return $data[0];
    return (isset($data[0]))? $data[0] :  false;
}


function ' . $plugin . '_field_code($field, $code) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT $field FROM ' . $plugin . ' WHERE code= ?");
    $req->execute(array($code));
    $data = $req->fetch();
    //return $data[0];
    return (isset($data[0]))? $data[0] :  false;
}

function ' . $plugin . '_search_by_unique($field, $FieldUnique, $valueUnique) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT $field FROM ' . $plugin . ' WHERE   $FieldUnique = ?");
    $req->execute(array($valueUnique));
    $data = $req->fetch();
    //return $data[0];
    return (isset($data[0]))? $data[0] :  false;
}

function ' . $plugin . '_list() {
    global $db;
    $limit = 999;

    $data = null;

    $req = $db->prepare("SELECT * FROM ' . $plugin . '  ORDER BY order_by LIMIT $limit  ");

    $req->execute(array(
        "limit" => $limit
    ));
    $data = $req->fetchall();
    return $data;
}

function ' . $plugin . '_details($id) {
    global $db;
    $req = $db->prepare("SELECT * FROM ' . $plugin . ' WHERE id= ? ");
    $req->execute(array($id));
    $data = $req->fetch();
    return $data;
}

function ' . $plugin . '_delete($id) {
    global $db;
    $req = $db->prepare("DELETE FROM ' . $plugin . ' WHERE id=? ");
    $req->execute(array($id));
}

function ' . $plugin . '_edit(';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";

        $contenido .= '$' . $columna['Field'] . ' ' . $coma . '  ';

        $i++;
    }


    $contenido .= ') {

    global $db;
    $req = $db->prepare(" UPDATE ' . $plugin . ' SET "
            
            ';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        if ($columna['Field'] != "id") {
            $contenido .= '."' . $columna['Field'] . '=:' . $columna['Field'] . ' ' . $coma . ' "   ' . "\n";
        }

        $i++;
    }
    $contenido .= '
            
                    
            
                    
            . " WHERE id=:id ");
    $req->execute(array(
';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) ) ? "," : "";
        $contenido .= ' "' . $columna['Field'] . '" =>$' . $columna['Field'] . ' ' . $coma . '  ';
        $i++;
    }

    $contenido .= '
                

));
}

function ' . $plugin . '_add(';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        if ($columna['Field'] != 'id') {
            $contenido .= '$' . $columna['Field'] . ' ' . $coma . '  ';
        }
        $i++;
    }


    $contenido .= ') {
    global $db;
    $req = $db->prepare(" INSERT INTO `' . $plugin . '` (';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= ' `' . $columna['Field'] . '` ' . $coma . '  ';

        $i++;
    }


    $contenido .= ')
                                       VALUES  (';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= ':' . $columna['Field'] . ' ' . $coma . '  ';

        $i++;
    }

    $contenido .= ') ");

    $req->execute(array(

';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";

        if ($columna['Field'] == "id") {
            $contenido .= ' "' . $columna['Field'] . '" => null ' . $coma . '  ' . "\n";
        } else {
            $contenido .= ' "' . $columna['Field'] . '" => $' . $columna['Field'] . ' ' . $coma . '  ' . "\n";
        }

        $i++;
    }

    $contenido .= '                        
            )
    );
    
     return $db->lastInsertId();
}



function ' . $plugin . '_search($txt) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT * FROM ' . $plugin . ' 
    
            WHERE id like :txt ';

    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $contenido .= 'OR ' . $columna['Field'] . ' like :txt' . "\n";
    }


    $contenido .= '                           
");
    $req->execute(array(
        "txt" => "%$txt%"
    ));
    $data = $req->fetchall();
    return $data;
}



function ' . $plugin . '_select($k, $v, $selected="", $disabled=array()) {    
    $c = "";    
    foreach (' . $plugin . '_list() as $key => $value) {        
        $s = ($selected == $value[$k])?" selected  ":"";       
        $d = ( in_array($value[$k], $disabled )) ? " disabled ":"";                        
        $c .= "<option value=\"$value[$k]\" $s $d >". ucfirst($value[$v])."</option>" ;
    }    
    echo  $c;     
}' . "\n";


    //$i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        //$coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= 'function ' . $plugin . '_is_' . $columna['Field'] . '($' . $columna['Field'] . '){' . "\n";
        $contenido .= '     return true;' . "\n";
        $contenido .= '}' . "\n\n";


        // $i++;
    }



    return $contenido;
}

function crear_carpeta($carpeta) {
    $cmd = "mkdir -p $carpeta";
    shell_exec($cmd);
}

function crear_archivo($archivo, $contenido) {
    $fh = fopen($archivo, 'w') or die("Se produjo un error al crear el archivo");
    fwrite($fh, $contenido) or die("No se pudo escribir en el archivo $archivo \n");
    fclose($fh);
    echo "El archivo '$archivo' Se ha escrito sin problemas  \n";
}

function crear_plugin($plugin) {
    global $config_destino;

    crear_carpeta("../$config_destino/$plugin");
    crear_carpeta("../$config_destino/$plugin/controllers");
    crear_carpeta("../$config_destino/$plugin/models");
    crear_carpeta("../$config_destino/$plugin/views");



    if ($config_destino == "www") {
        crear_archivo("../$config_destino/$plugin/functions.php", contenido_functions($plugin));
    } else {
        crear_archivo("../$config_destino/$plugin/functions.php", "<?php");
    }

    crear_archivo("../$config_destino/$plugin/readme.md", bdd_total_columnas_segun_tabla($plugin));

    $archivos = array(
        "add.php",
        // "addOk.php",
        "ok_add.php",
        "delete.php",
        //  "deleteOk.php",
        "ok_delete.php",
        "details.php",
        "edit.php",
        //  "editOk.php",
        "ok_edit.php",
        "export_json.php",
        "export_pdf.php",
        //    "form_add.php",
        //    "form_edit.php",
        //    "form_details.php",
        //    "form_delete.php",
        //    "form_search_advanced.php",
        "index.php",
        //    "izq.php",
        //    "nav.php",
        "search.php",
        "search_advanced.php"
            //    "table_index.php"
    );

    $contenido = "<?php";

    foreach ($archivos as $archivo) {
        crear_archivo("../$config_destino/$plugin/controllers/$archivo", contenido_controllers($plugin, $archivo));
    }


    $archivos = array(
        "add.php",
        // "addOk.php",
        // "ok_add.php",
        "delete.php",
        //  "deleteOk.php",
        //  "ok_delete.php",
        "details.php",
        "edit.php",
        //  "editOk.php",
        //  "ok_edit.php",
        "export_json.php",
        "export_pdf.php",
        "form_add.php",
        "form_edit.php",
        "form_details.php",
        "form_delete.php",
        "form_search_advanced.php",
        "index.php",
        "izq.php",
        "der.php",
        "nav.php",
        "search.php",
        "search_advanced.php",
        "table_index.php"
    );

    foreach ($archivos as $archivo) {
        crear_archivo("../$config_destino/$plugin/views/$archivo", contenido_views($plugin, $archivo));
    }
}

/**
 * Crea una clase
 * 
 * @global type $config_destino
 * @param type $plugin
 */
function crear_clase($plugin) {
    global $config_destino;

    $clase = ucfirst($plugin);

    crear_carpeta("../$config_destino/$plugin");
    crear_carpeta("../$config_destino/$plugin/models");


    $archivos = array(
        "$clase.php");

    $contenido = "";

    foreach ($archivos as $archivo) {
        // crear_archivo("../$config_destino/$plugin/controllers/$archivo" , contenido_controllers($plugin , $archivo)) ;
        crear_archivo("../$config_destino/$plugin/models/$archivo", contenido_clase($plugin, $archivo));
        // crear_archivo("../$config_destino/$plugin/views/$archivo" , contenido_views($plugin , $archivo)) ;
    }
}

function crear_mvc_esqueleto($plugin) {
    global $config_destino;

    crear_carpeta("../$config_destino/$plugin");
    crear_carpeta("../$config_destino/$plugin/controllers");
    crear_carpeta("../$config_destino/$plugin/models");
    crear_carpeta("../$config_destino/$plugin/views");

    if ($config_destino == "www") {
        crear_archivo("../$config_destino/$plugin/functions.php", contenido_functions($plugin));
    } else {
        crear_archivo("../$config_destino/$plugin/functions.php", "<?php");
    }

    crear_archivo("../$config_destino/$plugin/readme.md", bdd_total_columnas_segun_tabla($plugin));

    $archivos = array(
        "add.php",
        "ok_add.php",
        "delete.php",
        "ok_delete.php",
        "details.php",
        "edit.php",
        "ok_edit.php",
        "export_json.php",
        "export_pdf.php",
        "form_add.php",
        "form_edit.php",
        "form_details.php",
        "form_delete.php",
        "form_search_advanced.php",
        "index.php",
        "izq.php",
        "nav.php",
        "search.php",
        "search_advanced.php",
        "table_index.php");

    $contenido = "<?php";

    foreach ($archivos as $archivo) {
        // crear_archivo("../$config_destino/$plugin/controllers/$archivo" , contenido_controllers($plugin , $archivo)) ;
        // crear_archivo("../$config_destino/$plugin/models/$archivo" , $contenido) ;
        // crear_archivo("../$config_destino/$plugin/views/$archivo" , contenido_views($plugin , $archivo)) ;
    }
}

function magia_registrar_en_tabla($plugin) {
    global $config_db;


    foreach (bdd_columnas_segun_tabla($plugin) as $campo) {

        //echo $campo['Field'] . " \n" ; 
        $tipo = campos_tipo($campo['Type']);

        $te = bdd_referencias($plugin, $campo['Field']);

        //echo var_dump($tabla_externa);
        $tabla_externa = (isset($te['REFERENCED_TABLE_NAME'])) ? $te['REFERENCED_TABLE_NAME'] : false;
        $columna_externa = (isset($te['REFERENCED_COLUMN_NAME'])) ? $te['REFERENCED_COLUMN_NAME'] : false;


        echo ($tabla_externa) ? "La tabla externa es: $tabla_externa \n" : "No tiene tabla externa \n";
        echo ($columna_externa) ? "la columlna externa es: $columna_externa \n" : "No tiene columna externa\n";


        foreach (array("ver", "crear", "editar", "borrar") as $crud) {
            bdd_add_en_magia($config_db, $plugin, $campo['Field'], $crud, $campo['Field'], $tipo, $tabla_externa, $columna_externa, "form-control", $campo['Field'], $campo['Field'], $campo['Field'], "valor");
        }
    }
}
