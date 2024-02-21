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
             INSERT INTO `controllers` (`id`, `controller`, `title`, `description`) VALUES (null, :plugin , :plugin, :plugin); 
            ");
    $req->execute(array(
        "plugin" => $plugin
    ));

    return $db->lastInsertId();
//    return $plugin;
}

function bdd_add_permissions($plugin, $rol, $permiso) {
    global $db;

    $req = $db->prepare("            
             INSERT INTO `permissions` (`id`, `rol`, `controller`, `crud`) VALUES (NULL, :rol, :plugin, :permiso);
            ");
    $req->execute(array(
        "rol" => $rol,
        "plugin" => $plugin,
        "permiso" => $permiso
    ));
}

function bdd_menu_search($tabla, $location, $father, $label) {
    global $db;
    $data = null;
    $req = $db->prepare("            
             SELECT * FROM :tabla WHERE location = :location AND father = :father AND label = :label
            ");
    $req->execute(array(
        "tabla" => $tabla,
        "location" => $location,
        "father" => $father,
        "label" => $label
    ));
    $data = $req->fetchall();
    return $data;
}

function bdd_add_en_menu($location, $father_id, $label, $controller, $url, $icon, $order_by) {
    global $db;

    $req = $db->prepare("            
             INSERT INTO `_menus` (`id`, `location`, `father_id`, `label`, `controller`, `url`, `target`, `icon`, `order_by`) 
                           VALUES (:id,  :location,  :father_id,  :label,  :controller,  :url,  :target,  :icon,  :order_by);
            ");
    $req->execute(array(
        "id" => null,
        "location" => $location,
        "father_id" => $father_id,
        "label" => $label,
        "controller" => $controller,
        "url" => $url,
        "target" => '',
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
// valor por defecto 
//$_options["status"] = 1; ';

//            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
//
//                $field = $columna['Field'];
//
//                $default = ($columna['Default']) ? $columna['Default'] : " false ";
//
//                if ($default == 'current_timestamp()') {
//                    $default = 'date("Y-m-d")';
//                }
//
//                if ($default) {
//                    $contenido .= '$' . $plugin . '["' . $columna['Field'] . '"] = ' . $default . ';' . "\n";
//                }
//            }

            $contenido .= '
include view("' . $plugin . '", "add");                 
';

            break;

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
    $' . $plugin . ' = new ' . ucfirst($plugin) . '();
    $' . $plugin . '->set' . ucfirst($plugin) . '($id);

    include view("' . $plugin . '", "details");
} else {
    include view("home", "pageError");
}    

';
            break;

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
    $' . $plugin . ' = new ' . ucfirst($plugin) . '();
    $' . $plugin . '->set' . ucfirst($plugin) . '($id);

    include view("' . $plugin . '", "edit");
} else {
    include view("home", "pageError");
}    


';
            break;

        ## export_json.php
        case "export_json.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}


$error = array();

if (!$error) {
    $' . $plugin . ' = new ' . ucfirst($plugin) . '();
    $' . $plugin . '->set' . ucfirst($plugin) . '($id);

    include view("' . $plugin . '", "export_json");
} else {
    include view("home", "pageError");
}    
';
            break;

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
if (!$error) {
    $' . $plugin . ' = new ' . ucfirst($plugin) . '();
    $' . $plugin . '->set' . ucfirst($plugin) . '($id);

    include view("' . $plugin . '", "delete");
} else {
    include view("home", "pageError");
}    

';
            break;

        ## index.php
        case "index.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$order_col = (isset($_GET["order_col"]) && $_GET["order_col"] != "" ) ? clean($_GET["order_col"]) : "id";
$order_way = (isset($_GET["order_way"]) && $_GET["order_way"] != "" ) ? clean($_GET["order_way"]) : "desc";
$error = array();
################################################################################
// Actualizo con que columna esta ordenado 
if (isset($_GET["order_col"])) {
    $data = json_encode(array("order_col" => $order_col, "order_way" => $order_way));
    _options_push("config_' . $plugin . '_order_col", $data, "' . $plugin . '");
}
################################################################################
$' . $plugin . ' = null;
    
################################################################################
$pagination = new Pagination($page, ' . $plugin . '_list());
// puede hacer falta
//$pagination->setUrl($url);
$' . $plugin . ' = ' . $plugin . '_list($pagination->getStart(), $pagination->getLimit());
################################################################################    
//$' . $plugin . ' = ' . $plugin . '_list(10, 5);
    

include view("' . $plugin . '", "index");  
    
if ($debug) {
    include "www/' . $plugin . '/views/debug.php";
}';
            break;

        ## ok_add.php
        case "ok_add.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "create")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $field = $columna['Field'];

                $default = ($columna['Default']) ? $columna['Default'] : " false ";

                if ($field != 'id') {

                    // si es date_registre
                    if ($field == 'date_registre') {
                        $default = 'date("Y-m-d G:i:s")';
                    }


                    $check_null = ($columna['Default'] === NULL) ? ' && $_POST["' . $field . '"] !="null" ' : "";

                    $contenido .= '$' . $field . ' = (isset($_POST["' . $field . '"]) && $_POST["' . $field . '"] !="" ' . $check_null . ') ? clean($_POST["' . $field . '"]) : ' . $default . ';' . "\n";
                }
            }
            $contenido .= '$redi = (isset($_POST["redi"]) && $_POST["redi"] !="" ) ? ($_POST["redi"]) : false;';
            $contenido .= '  
$error = array();
################################################################################
# REQUIRED
################################################################################
';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                if ($columna['Field'] != 'id' && $columna['Null'] == 'NO') {
                    //$contenido .= '$text = (isset($_POST["'.$columna['Field'].'"])) ? clean($_POST["'.$columna['Field'].'"]) : false;';                                                                         
                    $contenido .= 'if (!$' . $columna['Field'] . ') {
    array_push($error, \'$' . $columna['Field'] . ' not send\');
}' . "\n";
                }
            }
            $contenido .= '
###############################################################################
# FORMAT
###############################################################################
';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                if ($columna['Field'] != 'id' && $columna['Null'] == 'NO') {
                    //$contenido .= '$text = (isset($_POST["'.$columna['Field'].'"])) ? clean($_POST["'.$columna['Field'].'"]) : false;';                                                                         
                    $contenido .= 'if (! ' . $plugin . '_is_' . $columna['Field'] . '($' . $columna['Field'] . ') ) {
    array_push($error, \'$' . $columna['Field'] . ' format error\');
}' . "\n";
                }
            }
            $contenido .= '
###############################################################################
# CONDITIONAL
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
//if( ' . $plugin . '_search($' . $columna['Field'] . ')){
    //array_push($error, "That text with that context the database already exists");
//}
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
              

    switch ($redi) {
        case 1:
            header("Location: index.php?c=' . $plugin . '");
            break;
            
        case 2:
            header("Location: index.php?c=' . $plugin . '&a=details&id=$id");
            break;
            
        case 3:
            header("Location: index.php?c=' . $plugin . '&a=edit&id=$id");
            break;
            
        case 4:
            header("Location: index.php?c=' . $plugin . '&a=details&id=$lastInsertId");
            break;
            
        case 5: // custom
            header("Location: index.phpc=xxx&a=yyy");
            break;

        default:
            header("Location: index.php?");
            break;
    }
 
} else {

    include view("home", "pageError");
}


';
            break;

        ## ok_edit.php
        case "ok_edit.php":
            $contenido = '<?php
if (!permissions_has_permission($u_rol, $c, "update")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}
// Recolection vars
$id = (isset($_REQUEST["id"]) && $_REQUEST["id"] != "") ? clean($_REQUEST["id"]) : false;
';
            $i = 0;
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $coma = ($i < bdd_total_columnas_segun_tabla($plugin) ) ? "," : "";
                $field = $columna['Field'];
                $default = ($columna['Default']) ? $columna['Default'] : " false ";
                if ($field != 'id') {
                    $contenido .= '$' . $field . ' = (isset($_REQUEST["' . $field . '"]) && $_REQUEST["' . $field . '"] !="") ? clean($_REQUEST["' . $field . '"]) : false;' . "\n";
                }
                //$contenido .= '$' . $columna['Field'] . ' = (isset($_POST["' . $columna['Field'] . '"]) && $_POST["' . $columna['Field'] . '"] !="" ) ? clean($_POST["' . $columna['Field'] . '"]) : false;' . "\n";
                $i++;
            }
            $contenido .= '$redi = (isset($_POST["redi"]) && $_POST["redi"] !="" ) ? clean($_POST["redi"]) : false;';
            $contenido .= ' 
$error = array();
################################################################################
# REQUIRED
################################################################################
';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                if ($columna['Field'] != 'id' && $columna['Null'] == 'NO') {
                    //$contenido .= '$text = (isset($_POST["'.$columna['Field'].'"])) ? clean($_POST["'.$columna['Field'].'"]) : false;';                                                                         
                    $contenido .= 'if (!$' . $columna['Field'] . ') {
    array_push($error, \'$' . $columna['Field'] . ' not send\');
}' . "\n";
                }
            }
            $contenido .= '
###############################################################################
# FORMAT
###############################################################################
';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                if ($columna['Field'] != 'id' && $columna['Null'] == 'NO') {
                    //$contenido .= '$text = (isset($_POST["'.$columna['Field'].'"])) ? clean($_POST["'.$columna['Field'].'"]) : false;';                                                                         
                    $contenido .= 'if (! ' . $plugin . '_is_' . $columna['Field'] . '($' . $columna['Field'] . ') ) {
    array_push($error, \'$' . $columna['Field'] . ' format error\');
}' . "\n";
                }
            }
            $contenido .= '
###############################################################################
# CONDITIONAL
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
//if( ' . $plugin . '_search($' . $columna['Field'] . ')){
    //array_push($error, "That text with that context the database already exists");
//}
################################################################################
################################################################################
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
        
    switch ($redi) {
        case 1:
            header("Location: index.php?c=' . $plugin . '");
            break;

        default:
            header("Location: index.php?c=' . $plugin . '&a=details&id=$id");
            break;
    }
} else {

    include view("home", "pageError");
}
';
            break;

        ## delete.php
        case "ok_delete.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "delete")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$id   = (isset($_REQUEST["id"])   && $_REQUEST["id"]   !="" )  ? clean($_REQUEST["id"]) : false;
$redi = (isset($_REQUEST["redi"]) && $_REQUEST["redi"] !="" ) ? ($_REQUEST["redi"]) : false;
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
        
    switch ($redi) {
        case 1:
            header("Location: index.php?c=' . $plugin . '");
            break;

        case 2:
            header("Location: index.php?c=' . $plugin . '&a=edit&id=$id");
            break;

        case 5: // custom
            header("Location: index.php?c=xxx&a=yyy");
            break;

        default:
            header("Location: index.php#default");
            break;
    }
} else {

    include view("home", "pageError");
}  
';
            break;

        ## delete.php
        case "ok_pagination_items_limit.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "update")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$data = (isset($_POST["data"])) ? clean($_POST["data"]) : false;
$redi = (isset($_POST["redi"])) ? clean($_POST["redi"]) : false;
$error = array();

$data = intval($data);

if ($data < 1 || $data > 1000) {
    array_push($error, "Must be between 1 and 1000");
}
################################################################################
################################################################################
################################################################################
if (!$error) {

    // si no existe lo crea
    _options_push("config_' . $plugin . '_pagination_items_limit", $data, "' . $plugin . '");

    switch ($redi) {
        case 1:
            header("Location: index.php?c=' . $plugin . '");
            break;

        default:
            header("Location: index.php?c=config&a=' . $plugin . '_pagination_items_limit&sms=1");
            break;
    }
} else {

    include view("home", "pageError");
}
';
            break;

        ## delete.php
        case "ok_show_col_from_table.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "update")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}

$data = (isset($_POST)) ? json_encode($_POST) : false;
$redi = (isset($_POST["redi"])) ? clean($_POST["redi"]) : false;
$error = array();

################################################################################
################################################################################
################################################################################
if (!$error) {

    // si no existe lo crea
    _options_push("config_' . $plugin . '_show_col_from_table", $data, \'' . $plugin . '\');

    switch ($redi) {
        case 1:
            header("Location: index.php?c=' . $plugin . '");
            break;

        default:
            header("Location: index.php");
            break;
    }
} else {

    include view("home", "pageError");
}

';
            break;

        ## search.php
        case "search.php":
            $contenido = '<?php

if (!permissions_has_permission($u_rol, $c, "read")) {
    header("Location: index.php?c=home&a=no_access");
    die("Error has permission ");
}
$' . $plugin . ' = null;
$order_col = (isset($_POST["order_col"]) && $_POST["order_col"] !="" ) ? clean($_POST["order_col"]) : "id";  
$order_way = (isset($_POST["order_way"]) && $_POST["order_way"] !="" ) ? clean($_POST["order_way"]) : "desc";  
$w = (isset($_GET["w"]) && $_GET["w"] !="") ? clean($_GET["w"]) : false;
$error = array();

################################################################################
################################################################################
switch ($w) {
    case "id":
        $txt = (isset($_GET["txt"]) && $_GET["txt"] !="" ) ? clean($_GET["txt"]) : false;        
        $' . $plugin . ' = ' . $plugin . '_search_by_id($txt);
        break;
        

    #### --- ####################################################################
';
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

                $field = $columna['Field'];
                $default = ($columna['Default']) ? $columna['Default'] : " false ";

                if ($field != 'id') {
                    $contenido .= '    case "by_' . $field . '":
        $' . $field . ' = (isset($_GET["' . $field . '"]) && $_GET["' . $field . '"] != "" ) ? clean($_GET["' . $field . '"]) : false;
        ################################################################################
        $pagination = new Pagination($page, ' . $plugin . '_search_by("' . $field . '", $' . $field . '));
        // puede hacer falta
        $url = "index.php?c=' . $plugin . '&a=search&w=by_' . $field . '&' . $field . '=" . $' . $field . ';
        $pagination->setUrl($url);
        $' . $plugin . ' = ' . $plugin . '_search_by("' . $field . '", $' . $field . ', $pagination->getStart(), $pagination->getLimit());
        ################################################################################ 
        break;
            
';
                }
            }
            $contenido .= '
        #### --- ####################################################################
        
    default:
        $txt = (isset($_GET["txt"]) && $_GET["txt"] !="" ) ? clean($_GET["txt"]) : false;
        ################################################################################
        $pagination = new Pagination($page, ' . $plugin . '_search($txt));
        // puede hacer falta
        $url = "index.php?c=' . $plugin . 'a=search&w=&txt=" . $txt;
        $pagination->setUrl($url);
        $' . $plugin . ' = ' . $plugin . '_search($txt, $pagination->getStart(), $pagination->getLimit());
        ################################################################################ 
        //$' . $plugin . ' = ' . $plugin . '_search($txt);
        break;
}


include view("' . $plugin . '", "index");      
';
            break;

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
        $contenido .= "    function set" . ucfirst($columna['Field']) . " ($" . ($columna['Field']) . ") {" . "\n";
        $contenido .= "        \$this->_$columna[Field] = $" . ($columna['Field']) . "; " . "\n";
        $contenido .= '    }' . "\n";
    }
    $contenido .= '   
    function set' . ucfirst($plugin) . '($id) {
        $' . $plugin . ' = ' . $plugin . '_details($id);
        //' . "\n";

    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $contenido .= '        $this->_' . $columna['Field'] . ' = $' . $plugin . '["' . $columna['Field'] . '"];' . "\n";
    }


    $contenido .= '';

    $contenido .= '

}
################################################################################

    // /////////////////////////////////////////////////////////////////////////
    function field_id($field, $id) {
        return ' . $plugin . '_field_id($field, $id);
    }

    function field_code($field, $code) {
        return ' . $plugin . '_field_code($field, $code);
    }

    function search_by_unique($field, $FieldUnique, $valueUnique) {
        return ' . $plugin . '_search_by_unique($field, $FieldUnique, $valueUnique);
    }

    function list($start = 0, $limit = 999) {
        return ' . $plugin . '_list($start, $limit);
    }

    function details($id) {
        return ' . $plugin . '_details($id);
    }

    function delete($id) {
        return ' . $plugin . '_delete($id);
    }

    function edit($id, $regla, $condition_id, $action_id, $order_by, $status) {
        return ' . $plugin . '_edit($id, $regla, $condition_id, $action_id, $order_by, $status);
    }

    function add($regla, $condition_id, $action_id, $order_by, $status) {
        return ' . $plugin . '_add($regla, $condition_id, $action_id, $order_by, $status);
    }

    function search($txt, $start = 0, $limit = 999) {
        return ' . $plugin . '_search($txt, $start, $limit);
    }

    function select($k, $v, $selected = "", $disabled = array()) {
        return ' . $plugin . '_select($k, $v, $selected, $disabled);
    }

    function unique_from_col($col) {
        return ' . $plugin . '_unique_from_col($col);
    }

    function search_by($field, $txt, $start = 0, $limit = 999) {
        return ' . $plugin . '_search_by($field, $txt, $start, $limit);
    }

    function db_show_col_from_table($table) {
        return ' . $plugin . '_db_show_col_from_table($table);
    }

    function db_col_list_from_table($table) {
        return ' . $plugin . '_db_col_list_from_table($table);
    }

    function add_filter($col_name, $value) {
//        return ' . $plugin . '_function($col_name, $value);
        $res = null;
        switch ($col_name) {
';
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        echo "Field: " . $campo['Field'] . " \n";
        $tipo = campos_tipo($campo['Type']);
        $te = bdd_referencias($plugin, $campo['Field']);
        //echo var_dump($tabla_externa);
        //REFERENCED_TABLE_NAME, 
        //REFERENCED_COLUMN_NAME 
        $bdd_referencias = bdd_referencias($plugin, $columna['Field']);
        $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
        $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;
        //
        if ($bdd_ref_tabla_externa) {
            $contenido .= '        case "' . $columna['Field'] . '":
                return ' . $bdd_ref_tabla_externa . '_field_id("' . $bdd_col_externa . '", $value);
                break;
        ';
        }
    }
    $contenido .= '       
            default:
                $res = $value;
                break;
        }
        return $res;
    }
';
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $contenido .= '    function is_' . $columna['Field'] . '($' . $columna['Field'] . ') {' . "\n";
        $contenido .= '        return ' . $plugin . '_is_' . $columna['Field'] . '($' . $columna['Field'] . ');' . "\n";
        $contenido .= '    }' . "\n\n";
    }
    $contenido .= '
}
';

    return "$contenido";
}

function contenido_views($plugin, $archivo) {
    global $config_destino;
    switch ($archivo) {

        ## add.php
        case "add.php":
            $contenido = "<?php \n";
            $contenido .= "# MagiaPHP \n";
            $contenido .= "# file date creation: " . date("Y-m-d") . " \n";
            $contenido .= "?>\n";
            $contenido .= '<?php include view("home", "header"); ?>  

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '       <?php include view("' . $plugin . '", "izq_add"); ?>' :
                    '       <?php include "izq.php"; ?>';
            $contenido .= '</div>

    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
        <h1>
            <?php _menu_icon("top" , \'' . $plugin . '\'); ?>
            <?php _t("Add ' . $plugin . '"); ?>
        </h1>
        ';

            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "form_add", $arg = ["redi" => 1]); ?>' :
                    '<?php  include "form_add.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
            ';
            $contenido .= ($config_destino == "www") ?
                    '<?php  include view("' . $plugin . '", "der_add"); ?>' :
                    '<?php  include "der.php"; ?>';
            $contenido .= '
        
    </div>
</div>

<?php include view("home", "footer"); ?>

';
            break;

        ## delete.php
        case "delete.php":
            $contenido = "<?php \n";
            $contenido .= "# MagiaPHP \n";
            $contenido .= "# file date creation: " . date("Y-m-d") . " \n";
            $contenido .= "?>\n";
            $contenido .= '<?php include view("home", "header"); ?>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
            ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq_delete"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
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
                    '<?php include view("' . $plugin . '", "form_delete" , $arg = ["redi" => 1] ); ?>' :
                    '<?php include "form_delete.php"; ?>';
            $contenido .= '

    </div>

    <div class="col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "der_delete"); ?>' :
                    '<?php include "der.php"; ?>';
            $contenido .= '
    </div>
</div>
<?php include view("home", "footer"); ?>
';
            break;

        ## details.php
        case "details.php":
            $contenido = "<?php \n";
            $contenido .= "# MagiaPHP \n";
            $contenido .= "# file date creation: " . date("Y-m-d") . " \n";
            $contenido .= "?>\n";
            $contenido .= '<?php  include view("home", "header"); ?> 

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '       <?php include view("' . $plugin . '", "izq_details"); ?>' :
                    '       <?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-8 col-lg-8">
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
                    '       <?php include view("' . $plugin . '", "form_details"  , $arg = ["redi" => 1]  ); ?>' :
                    '       <?php include "form_details.php"; ?>';
            $contenido .= '
    </div>
    <div class="col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '       <?php  include view("' . $plugin . '", "der_details"); ?>' :
                    '       <?php include "der.php"; ?>';
            $contenido .= '
    </div>
</div>

<?php include view("home", "footer"); ?>
';
            break;

        ## edit.php
        case "edit.php":
            $contenido = "<?php \n";
            $contenido .= "# MagiaPHP \n";
            $contenido .= "# file date creation: " . date("Y-m-d") . " \n";
            $contenido .= "?>\n";
            $contenido .= '
<?php include view("home", "header"); ?>                

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">      
         ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq_edit"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
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
                    '<?php include view("' . $plugin . '", "form_edit"  , $arg = ["redi" => 1] ); ?>' :
                    '<?php include "form_edit.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-sm-12 col-md-2 col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php  include view("' . $plugin . '", "der_edit"); ?>' :
                    '<?php // include "der.php"; ?>';
            $contenido .= '
    </div>
</div>

<?php include view("home", "footer"); ?>
';
            break;

        ## export_json.php
        case "export_json.php":
            $contenido = '<?php
//
header("Content-type: application/json");
echo json_encode($' . $plugin . ');

';
            break;

        ## export_pdf.php
        case "export_pdf.php":
            $contenido = "<?php \n";
            $contenido .= "# MagiaPHP \n";
            $contenido .= "# file date creation: " . date("Y-m-d") . " \n";
            $contenido .= "?>\n";
            $contenido .= '<?php
require("includes/fpdf185/fpdf.php");
$pdf = new FPDF();
$pdf->AddPage("L");
$pdf->SetFont("Arial","B",16);
$pdf->Cell(40,10,"' . $plugin . ' !");
$pdf->SetFont("Arial","B",12);
$pdf->Cell(40,10,"Edit file: ' . $plugin . '/views/export_pdf.php !");
$pdf->Output();
';
            break;

        ## form_add.php
        case "form_add.php":
            $contenido = "<?php \n";
            $contenido .= "# MagiaPHP \n";
            $contenido .= "# file date creation: " . date("Y-m-d") . " \n";
            $contenido .= "?>\n";
            $contenido .= '<form class="form-horizontal" action="index.php" method="post" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_add">
    <input type="hidden" name="redi" value="<?php echo $arg["redi"]; ?>">
    
';
            ####################################################################
            ####################################################################
            ####################################################################
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

                $bdd_referencias = bdd_referencias($plugin, $columna['Field']);

                $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
                $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;
                echo ($bdd_ref_tabla_externa) ? $bdd_ref_tabla_externa . '\n' : false;
                echo ($bdd_col_externa) ? $bdd_col_externa . '\n' : false;

                $marca_agua = ($columna['Field']) ? $columna['Field'] : "";
                $valor = ($columna['Default']) ? $columna['Default'] : "";

                $campos_tipo = campos_tipo($columna['Type']);
                $nombre = $columna['Field'];
                $id = $columna['Field'];

//                campos_crear_campo($tipo, $nombre, $id, $clase, $marca_agua, $valor, $desactivo)
                //--------------------------------------------------------------
                //--------------------------------------------------------------
                //--------------------------------------------------------------
                $campo_select = campos_crear_campo("select", $nombre, $id, "form-control", $marca_agua, '""', false);
                // Campo

                if ($campos_tipo == 'boolean') {
                    $campo = campos_crear_campo('boolean_add', $nombre, $id, 'form-control', $marca_agua, $valor, false);
                } else {
                    $campo = campos_crear_campo($campos_tipo, $nombre, $id, 'form-control', $marca_agua, $valor, false);
                }

                if ($columna['Field'] != 'id') {
                    $contenido .= '    <?php # ' . $nombre . ' ?>' . "\n";

                    // si es diferente a date_registre
                    if ($columna['Field'] != 'date_registre') {

                        $contenido .= '    <div class="form-group">
        <label class="control-label col-sm-3" for="' . $id . '"><?php _t("' . ucfirst($nombre) . '"); ?></label>
        <div class="col-sm-8">' . "\n";
                        $contenido .= ( $bdd_ref_tabla_externa ) ? "               " . $campo_select : "            " . $campo;
                        $contenido .= "\n        </div>	
    </div>" . "\n";

                        $contenido .= '    <?php # /' . $nombre . ' ?>' . "\n\n";
                        echo "\n\n";
                    }
                }
            }

            ####################################################################
            ####################################################################
            ####################################################################
            ####################################################################

            $contenido .= '  
    <div class="form-group">
        <label class="control-label col-sm-3" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Save"); ?>">
        </div>      							
    </div>      							

</form>
';

            break;

        ## form_delete.php
        case "form_delete.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="post" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_delete">
    <input type="hidden" name="id" value="<?php echo $' . $plugin . '->getId(); ?>">
    <input type="hidden" name="redi" value="<?php echo $arg["redi"]; ?>">

    ';

            ####################################################################
            ####################################################################
            ####################################################################
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $bdd_referencias = bdd_referencias($plugin, $columna['Field']);
                $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
                $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;

                echo ($bdd_ref_tabla_externa) ? $bdd_ref_tabla_externa . '\n' : false;
                echo ($bdd_col_externa) ? $bdd_col_externa . '\n' : false;
                // 
                $marca_agua = ($columna['Field']) ? $columna['Field'] : "";

//                $valor = ($columna['Default']) ? $columna['Default'] : "";


                $valor = '<?php echo $' . $plugin . '->get' . ucfirst($columna['Field']) . '(); ?>';

//                $valor = '$' . $plugin . '[\'' . $columna['Field'] . '\']';
                $campos_tipo = campos_tipo($columna['Type']);
                $nombre = $columna['Field'];
                $id = $columna['Field'];
//
                $campo_select = campos_crear_campo("select", $nombre, $id, "form-control", $marca_agua, '$' . $plugin . '->get' . ucfirst($columna['Field']) . '()', true);
                // Campo
                $campo = campos_crear_campo($campos_tipo, $nombre, $id, 'form-control', $marca_agua, $valor, true);
                //*************************************************************
                if ($columna['Field'] != 'id') {
                    $contenido .= '    <?php # ' . $nombre . ' ?>' . "\n";
                    $contenido .= '    <div class="form-group">
        <label class="control-label col-sm-3" for="' . $id . '"><?php _t("' . ucfirst($nombre) . '"); ?></label>
        <div class="col-sm-8">' . "\n";
                    $contenido .= ( $bdd_ref_tabla_externa ) ? "               " . $campo_select : "            " . $campo;
                    $contenido .= "\n        </div>	
    </div>" . "\n";
                    $contenido .= '    <?php # /' . $nombre . ' ?>' . "\n\n";
                    echo "\n\n";
                }
            }
            ####################################################################
            ####################################################################
            ####################################################################

            $contenido .= '

    <div class="form-group">
        <label class="control-label col-sm-3" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-danger active" type ="submit" value ="<?php _t("Delete"); ?>">
        </div>      							
    </div>      							

</form>

';
            break;

        ## form_details.php
        case "form_details.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="get" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="edit">
    <input type="hidden" name="id" value="<?php echo $' . $plugin . '->getId(); ?>">
    ';

            ////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////
            /**
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
             * 
             */
            ////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////
            ####################################################################
            ####################################################################
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $bdd_referencias = bdd_referencias($plugin, $columna['Field']);
                $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
                $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;

                echo ($bdd_ref_tabla_externa) ? $bdd_ref_tabla_externa . '\n' : false;
                echo ($bdd_col_externa) ? $bdd_col_externa . '\n' : false;
                // 
                $marca_agua = ($columna['Field']) ? $columna['Field'] : "";
                //$valor = ($columna['Default']) ? $columna['Default'] : "";

                $valor = '<?php echo $' . $plugin . '->get' . ucfirst($columna['Field']) . '(); ?>';

                $campos_tipo = campos_tipo($columna['Type']);
                $nombre = $columna['Field'];
                $id = $columna['Field'];
//
                $campo_select = campos_crear_campo("select", $nombre, $id, "form-control", $marca_agua, '$' . $plugin . '->get' . ucfirst($columna['Field']) . '()', true);
                // Campo
                $campo = campos_crear_campo($campos_tipo, $nombre, $id, 'form-control', $marca_agua, $valor, true);
                //*************************************************************
                if ($columna['Field'] != 'id') {
                    $contenido .= '    <?php # ' . $nombre . ' ?>' . "\n";
                    $contenido .= '    <div class="form-group">
        <label class="control-label col-sm-3" for="' . $id . '"><?php _t("' . ucfirst($nombre) . '"); ?></label>
        <div class="col-sm-8">' . "\n";
                    $contenido .= ( $bdd_ref_tabla_externa ) ? "               " . $campo_select : "            " . $campo;
                    $contenido .= "\n        </div>	
    </div>" . "\n";
                    $contenido .= '    <?php # /' . $nombre . ' ?>' . "\n\n";
                    echo "\n\n";
                }
            }
            ####################################################################
            ####################################################################
            ####################################################################



            $contenido .= '
    <div class="form-group">
        <label class="control-label col-sm-3" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Edit"); ?>">
        </div>      							
    </div>      							

</form>

';
            break;

        ## form_edit.php
        case "form_edit.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="post" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_edit">
    <input type="hidden" name="id" value="<?php echo $' . $plugin . '->getId(); ?>">
    <input type="hidden" name="redi" value="<?php echo $arg["redi"]; ?>">

    ';
            /**
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
             * 
             */
            ####################################################################
            ####################################################################
            ####################################################################
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
                $bdd_referencias = bdd_referencias($plugin, $columna['Field']);
                $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
                $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;

                echo ($bdd_ref_tabla_externa) ? $bdd_ref_tabla_externa . '\n' : false;
                echo ($bdd_col_externa) ? $bdd_col_externa . '\n' : false;
                // 
                $marca_agua = ($columna['Field']) ? $columna['Field'] : "";
                $valor = ($columna['Default']) ? $columna['Default'] : "";
                $valor = '<?php echo $' . $plugin . '[\'' . $columna['Field'] . '\']; ?>';

                $valor = '<?php echo $' . $plugin . '->get' . ucfirst($columna['Field']) . '(); ?>';

                $campos_tipo = campos_tipo($columna['Type']);
                $nombre = $columna['Field'];
                $id = $columna['Field'];
//
                $campo_select = campos_crear_campo("select", $nombre, $id, "form-control", $marca_agua, '$' . $plugin . '->get' . ucfirst($columna['Field']) . '()', false);
                // Campo
                $campo = campos_crear_campo($campos_tipo, $nombre, $id, 'form-control', $marca_agua, $valor, false);
                //*************************************************************
                if ($columna['Field'] != 'id') {
                    $contenido .= '    <?php # ' . $nombre . ' ?>' . "\n";
                    $contenido .= '    <div class="form-group">
        <label class="control-label col-sm-3" for="' . $id . '"><?php _t("' . ucfirst($nombre) . '"); ?></label>
        <div class="col-sm-8">' . "\n";
                    $contenido .= ( $bdd_ref_tabla_externa ) ? "               " . $campo_select : "            " . $campo;
                    $contenido .= "\n        </div>	
    </div>" . "\n";
                    $contenido .= '    <?php # /' . $nombre . ' ?>' . "\n\n";
                    echo "\n\n";
                }
            }
            ####################################################################
            ####################################################################
            ####################################################################

            $contenido .= '
    <div class="form-group">
        <label class="control-label col-sm-3" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Edit"); ?>">
        </div>      							
    </div>      							

</form>

';
            break;

        ## form_pagination_items_limit.php
        case "form_pagination_items_limit.php":
            $contenido = '
<form class="form-inline" method="post">
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_pagination_items_limit">
    <input type="hidden" name="redi" value="<?php echo $arg["redi"]; ?>">
    
  <div class="form-group">
    <div class="input-group">

        <input 
          type="text" 
          name="data" 
          class="form-control" 
          id="data" 
          placeholder="10"
          value="<?php echo _options_option("config_' . $plugin . '_pagination_items_limit")?>"
          >
      <div class="input-group-addon"><?php _t("items/page"); ?></div>
    </div>
  </div>
  <button type="submit" class="btn btn-primary"><?php _t("Save"); ?></button>
</form>



';
            break;

        ## form_search.php
        case "form_search.php":

            $contenido = '<form action="index.php" method="get" class="navbar-form navbar-left">
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="search">
    
    <div class="form-group">
        <input type="text" name="txt" class="form-control" placeholder="">
    </div>
    
    <button type="submit" class="btn btn-default"><?php _t("Search"); ?></button>
    
</form>';

            $contenido .= '';
            break;

        ## form_search_advanced.php.php
        case "form_search_advanced.php":
            $contenido = '<form class="form-horizontal" action="index.php" method="get" >
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="search">
    <input type="hidden" name="w" value="all">


    ';

            ####################################################################
            ####################################################################
            ####################################################################
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
//                var_dump(array(
//                    '__LINE__' => __LINE__,
//                    '$columna' => $columna
//                ));
//                
                //REFERENCED_TABLE_NAME, 
                //REFERENCED_COLUMN_NAME 
                $bdd_referencias = bdd_referencias($plugin, $columna['Field']);

//                var_dump($bdd_referencias);

                $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
                $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;
                echo ($bdd_ref_tabla_externa) ? $bdd_ref_tabla_externa . '\n' : false;
                echo ($bdd_col_externa) ? $bdd_col_externa . '\n' : false;

                $marca_agua = ($columna['Field']) ? $columna['Field'] : "";
                $valor = ($columna['Default']) ? $columna['Default'] : "";
                $valor = '<?php echo $' . $plugin . '[\'' . $columna['Field'] . '\']; ?>';

                $valor = '<?php echo $' . $plugin . '->get' . ucfirst($columna['Field']) . '(); ?>';

                $campos_tipo = campos_tipo($columna['Type']);
                $nombre = $columna['Field'];
                $id = $columna['Field'];
//                campos_crear_campo($tipo, $nombre, $id, $clase, $marca_agua, $valor, $desactivo)
                //--------------------------------------------------------------
                $campo_select = campos_crear_campo("select", $nombre, $id, "form-control", $marca_agua, '$' . $plugin . '->get' . ucfirst($columna['Field']) . '()', false);
                // Campo
                $campo = campos_crear_campo($campos_tipo, $nombre, $id, 'form-control', $marca_agua, false, false);

                if ($columna['Field'] != 'id') {
                    $contenido .= '    <?php # ' . $nombre . ' ?>' . "\n";
                    $contenido .= '    <div class="form-group">
        <label class="control-label col-sm-3" for="' . $id . '"><?php _t("' . ucfirst($nombre) . '"); ?></label>
        <div class="col-sm-8">' . "\n";
                    $contenido .= ( $bdd_ref_tabla_externa ) ? "               " . $campo_select : "            " . $campo;
                    $contenido .= "\n        </div>	
    </div>" . "\n";
                    $contenido .= '    <?php # /' . $nombre . ' ?>' . "\n\n";
                    echo "\n\n";
                }
            }

            ####################################################################
            ####################################################################
            ####################################################################

            $contenido .= '

    <div class="form-group">
        <label class="control-label col-sm-3" for=""></label>
        <div class="col-sm-8">    
            <input class="btn btn-primary active" type ="submit" value ="<?php _t("Search"); ?>">
        </div>      							
    </div>      							

</form>
';
            break;

        ## form_show_col_from_table.php
        case "form_show_col_from_table.php":
            $contenido = '
<form method="post" action="index.php">
    <input type="hidden" name="c" value="' . $plugin . '">
    <input type="hidden" name="a" value="ok_show_col_from_table">
    <input type="hidden" name="redi" value="1">
    
    <table class="table table-bordered">
        <tr>
        <?php
            $checked_array = json_decode(_options_option("config_' . $plugin . '_show_col_from_table"), true);
            foreach (' . $plugin . '_db_col_list_from_table($c) as $th) {
                // si hay como parte del array $checked_array
                // o si el array tiene cero elementos (au no registrado)
                $checked = (isset($checked_array[$th]) || !isset($checked_array) ) ? " checked " : "";
                echo \'<td><input \' . $checked . \' type="checkbox" name="\' . $th . \'" id="\' . $th . \'"> \' . $th . \' </td>\';
            }
            ?>
            <td>
                <button type="submit" class="btn btn-default"><?php _t("Save"); ?></button>
            </td>
        </tr>
    </table>
</form>




';
            break;

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
';

            $contenido .= ($config_destino == "www") ?
                    '        <?php
        if ($' . $plugin . ') {
            include view("' . $plugin . '", "table_index");
        } else {
            message("info", "No items");
        }
        ?>' :
                    '<?php include "table_index.php"; ?>';

            $contenido .= '
    </div>
</div>

<?php include view("home", "footer"); ?> 
';
            break;

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
</div>

';

            //-------------------------------------------------------------------
            //-------------------------------------------------------------------
            //-------------------------------------------------------------------
            $i = 0;
            foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

                //$coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
                $field = $columna['Field'];

                if ($field != 'id') {
                    $contenido .= '<div class="list-group">
    <a href="#" class="list-group-item active">
        <?php _menu_icon("top", "' . $plugin . '"); ?>
        <?php echo _t("By ' . $field . '"); ?>
    </a>
    <?php
    foreach (' . $plugin . '_unique_from_col("' . $field . '") as $' . $field . ') {
        if ($' . $field . '[\'' . $field . '\'] != "") {
            echo \'<a href="index.php?c=' . $plugin . '&a=search&w=by_' . $field . '&' . $field . '=\' . $' . $field . '[\'' . $field . '\'] . \'" class="list-group-item">\' . $' . $field . '[\'' . $field . '\'] . \'</a>\';
        }
    }
    ?>
</div>

';
                }

                $i++;
            }
            //-------------------------------------------------------------------
            //-------------------------------------------------------------------
            //-------------------------------------------------------------------



            break;

        ## izq_add.php
        case "izq_add.php":
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

        ## izq_delete.php
        case "izq_delete.php":
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

        ## izq_details.php
        case "izq_details.php":
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

        ## izq_edit.php
        case "izq_edit.php":
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

        ## der_add.php
        case "der_add.php":
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

        ## der_delete.php
        case "der_delete.php":
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

        ## der_details.php
        case "der_details.php":
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

        ## der_edit.php
        case "der_edit.php":
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

        ## MODAL
        case "modal_form_add.php":
            $contenido = '

<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#' . $plugin . '_add_">
    <span class="glyphicon glyphicon-plus"></span> 
    <?php _t("Add new"); ?>
</button>

<!-- Modal -->
<div class="modal fade" id="' . $plugin . '__add_" tabindex="-1" role="dialog" aria-labelledby="' . $plugin . '_add_Label">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="' . $plugin . '_add_Label"> <?php _t("Add"); ?></h4>
      </div>
      <div class="modal-body">
        <?php 
        $redi = 1;
        include views("' . $plugin . '","form_add"   , $arg = ["redi" => 1]); 
        $redi = "";     
        ?>
      </div>
      

      
    </div>
  </div>
</div>


';
            break;
        ## MODAL
        case "modal_form_delete.php":
            $contenido = '
<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#' . $plugin . '_delete_">
    <span class="glyphicon glyphicon-plus"></span> 
    <?php _t("Delete"); ?>
</button>

<!-- Modal -->
<div class="modal fade" id="' . $plugin . '_delete_" tabindex="-1" role="dialog" aria-labelledby="' . $plugin . '_delete_Label">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="' . $plugin . '__delete_Label"> <?php _t("Add"); ?></h4>
      </div>
      <div class="modal-body">
        <?php include views("' . $plugin . '","form_delete", $arg = ["redi" => 1]); ?>
      </div>
      
      
    </div>
  </div>
</div>';

            break;
        ## MODAL
        case "modal_form_edit.php":
            $contenido = '
<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#' . $plugin . '_edit_">
    <span class="glyphicon glyphicon-plus"></span> 
    <?php _t("Edit"); ?>
</button>

<!-- Modal -->
<div class="modal fade" id="' . $plugin . '__edit_" tabindex="-1" role="dialog" aria-labelledby="' . $plugin . '_edit_Label">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="' . $plugin . '_edit_Label"> <?php _t("Add"); ?></h4>
      </div>
      <div class="modal-body">
        <?php include views("' . $plugin . '","form_edit"   , $arg = ["redi" => 1]); ?>
      </div>
      

    </div>
  </div>
</div>';
            break;

        ## MODAL
        case "modal_form_search.php":
            $contenido = '
<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#' . $plugin . '_search_">
    <span class="glyphicon glyphicon-plus"></span> 
    <?php _t("Search"); ?>
</button>

<!-- Modal -->
<div class="modal fade" id="' . $plugin . '__search_" tabindex="-1" role="dialog" aria-labelledby="' . $plugin . '_search_Label">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="' . $plugin . '_search_Label"> <?php _t("Add"); ?></h4>
      </div>
      <div class="modal-body">
        <?php include views("' . $plugin . '","form_search"  , $arg = ["redi" => 1]); ?>
      </div>
      

      
    </div>
  </div>
</div>';
            break;

        ## nav.php
        case "nav.php":
            $contenido = '
<nav class="navbar navbar-default">
  <div class="container-fluid">

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

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        
      </ul>
        
        <?php include view("' . $plugin . '", "form_search"   , $arg = ["redi" => 1]) ?>
            
            <ul class="nav navbar-nav">
                <?php if (permissions_has_permission($u_rol, "config", "update")) { ?>
                    <li>
                        <a 
                            data-toggle="collapse" 
                            href="#collapse_form_' . $plugin . '_pagination_items_limit" 
                            aria-expanded="false" 
                            aria-controls="collapse_form_' . $plugin . '_pagination_items_limit">
                            <span class="glyphicon glyphicon-cog"></span>
                        </a>
                    </li>
                    
                    

                <?php } ?>
            </ul>
            
            <ul class="nav navbar-nav">
                <li><a href="index.php?c=' . $plugin . '&a=search_advanced"><?php _t("Search avanced"); ?></a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <?php echo _t("Export"); ?>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?c=' . $plugin . '&a=export_json"><?php _t("Json"); ?></a></li>
                        <li><a href="index.php?c=' . $plugin . '&a=export_pdf"><?php _t("Pdf"); ?></a></li>
                        <li><a href="index.php?c=' . $plugin . '&a=export_csv"><?php _t("CSV"); ?></a></li>
                        <li><a href="index.php?c=' . $plugin . '&a=export_xml"><?php _t("XML"); ?></a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="#">Other</a></li>
                    </ul>
                </li>
            </ul>
            

            
<div class="collapse navbar-collapse" id="' . $plugin . '_add">
                <?php if (permissions_has_permission($u_rol, "' . $plugin . '", "create")) { ?>     
                    
                    <button type="button" class="btn btn-primary navbar-btn navbar-right" data-toggle="modal" data-target="#' . $plugin . 'Modal">
                        <span class="glyphicon glyphicon-plus-sign"></span>
                        <?php _t("New ' . $plugin . '"); ?>
                    </button>

                    <div class="modal fade" id="' . $plugin . 'Modal" tabindex="-1" role="dialog" aria-labelledby="' . $plugin . 'ModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                     <h4 class="modal-title" id="' . $plugin . '_addLabel">
                                        <?php _t("Add new ' . $plugin . '"); ?>                
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <?php include view("' . $plugin . '", "form_add"); ?>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                <?php } ?>    
            </div>           
    </div>
  </div>
</nav>

<div class="collapse" id="collapse_form_' . $plugin . '_pagination_items_limit">
    <div class="well">
        <?php
        $redi = 1;
        include view("' . $plugin . '", "form_pagination_items_limit");
        echo "<br>"; 
        echo "<h3>"._tr("Columne to show")."</h3>"; 
        include view("' . $plugin . '", "form_show_col_from_table");
        ?>
    </div>
</div>



';
            break;

        ## search.php
        case "search.php":
            $contenido = '<?php include view("home", "header"); ?> 

<div class="row">
    <div class="col-lg-2">
 ';
            $contenido .= ($config_destino == "www") ?
                    '<?php include view("' . $plugin . '", "izq"); ?>' :
                    '<?php include "izq.php"; ?>';
            $contenido .= '
    </div>

    <div class="col-lg-10">
        <h1><?php _t("' . $plugin . '"); ?></h1>
        
        <?php
        if ($_REQUEST) {
            foreach ($error as $key => $value) {
                message("info", "$value");
            }
        }
        ?>

        <?php include views("' . $plugin . '","form_search"   , $arg = ["redi" => 1]);?>
        
    </div>
</div>

<?php include view("home", "footer"); ?>
';
            break;

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

    <div class="col-sm-12 col-md-8 col-lg-8">
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
                    '<?php include view("' . $plugin . '", "form_search_advanced"   , $arg = ["redi" => 1]); ?>' :
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
        case "xxxxxxxxx.php":
            $contenido = '';
            break;

        ## table_index.php
        case "table_index.php":
            $contenido = '<table class="table table-striped">
    <thead>
        <tr class="info">
            <?php
            $order_icon_show = false;
            $checked_array = json_decode(_options_option("config_' . $plugin . '_show_col_from_table"), true);
            foreach (' . $plugin . '_db_col_list_from_table($c) as $th) {
                $order_icon_up = \'<span class="glyphicon glyphicon-sort-by-attributes"></span>\';
                $order_icon_down = \'<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>\';
                $order_icon = ($order_way == "desc") ? $order_icon_down : $order_icon_up;
                if (isset($checked_array[$th]) || !isset($checked_array)) {
                    $order_icon_show = ($th == $order_col ) ? $order_icon : "";
                    $link_order_way = ($order_way == "desc") ? "&order_way=asc" : "&order_way=desc";
                    echo \'<th><a href="index.php?c=' . $plugin . '&order_col=\' . $th . \'\' . $link_order_way . \' ">\' . _tr(ucfirst($th)) . \' \' . $order_icon_show . \'</a></th>\';
                }
                $order_icon_show = false;
            }
            ?>
            <th><?php _t("Action"); ?></th>                                                      
        </tr>
    </thead>
    <tfoot>
        <tr class="info">
            <?php
            //$checked_array = json_decode(_options_option("config_' . $plugin . '_show_col_from_table"), true);
            foreach (' . $plugin . '_db_col_list_from_table($c) as $th) {
                if (isset($checked_array[$th]) || !isset($checked_array)) {
                    echo "<th>" . _tr(ucfirst($th)) . "</th>";
                }
            }
            ?>
            <th><?php _t("Action"); ?></th>                                                      
        </tr>
    </tfoot>
';

            $contenido .= '<tbody>
        <tr>
            <?php            
            if( ! $' . $plugin . ' ){
                message("info", "No items"); 
            }

            foreach ($' . $plugin . ' as $' . $plugin . '_item) {
                $menu=\'<div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                              Actions
                              <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                              <li><a href="index.php?c=' . $plugin . '&a=details&id=\'.$' . $plugin . '_item["id"].\'">\'._tr("Details").\'</a></li>
                              <li><a href="index.php?c=' . $plugin . '&a=edit&id=\'.$' . $plugin . '_item["id"].\'">\'._tr("Edit").\'</a></li>
                              <li role="separator" class="divider"></li>
                              <li><a href="index.php?c=' . $plugin . '&a=delete&id=\'.$' . $plugin . '_item["id"].\'">\'._tr("Delete").\'</a></li>
                            </ul>
                          </div>\'; 
                         ';

            $contenido .= '                echo "<tr id=\"$' . $plugin . '_item[id]\">";
                $checked_array = json_decode(_options_option("config_' . $plugin . '_show_col_from_table"), true);
                foreach (' . $plugin . '_db_col_list_from_table($c) as $col_name) {
                    if (isset($checked_array[$col_name]) || !isset($checked_array)) {
                        //echo "<td>$' . $plugin . '_item[$col_name]</td>";
                        echo "<td>" . ' . $plugin . '_add_filter($col_name, $' . $plugin . '_item[$col_name]) . "</td>";
                    }
                }';

            $contenido .= '                echo "<td>$menu</td>";
                echo "</tr>";
                }
            ?>	
        </tr>
    </tbody>
</table>
<?php 
$pagination->createHtml();
?>
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
// creation date : ' . date("Y-m-d") . '
// 
// 
function ' . $plugin . '_field_id($field, $id) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT `$field` FROM `' . $plugin . '` WHERE `id`= ?");
    $req->execute(array($id));
    $data = $req->fetch();
    //return $data[0];
    return (isset($data[0]))? $data[0] :  false;
}

function ' . $plugin . '_field_code($field, $code) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT `$field` FROM `' . $plugin . '` WHERE `code` = ?");
    $req->execute(array($code));
    $data = $req->fetch();
    //return $data[0];
    return (isset($data[0]))? $data[0] :  false;
}

function ' . $plugin . '_search_by_unique($field, $FieldUnique, $valueUnique) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT `$field` FROM `' . $plugin . '` WHERE   `$FieldUnique` = ?");
    $req->execute(array($valueUnique));
    $data = $req->fetch();
    //return $data[0];
    return (isset($data[0]))? $data[0] :  false;
}

function ' . $plugin . '_list($start = 0, $limit = 999) {
    global $db;
    $data = null;
    $sql = "SELECT ';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= '`' . $columna['Field'] . '`' . $coma . '  ';
        $i++;
    }
    $contenido .= ' 
    FROM `' . $plugin . '` ORDER BY `order_by` DESC, `id` DESC  Limit  :limit OFFSET :start  ";
    $query = $db->prepare($sql);
    $query->bindValue(\':start\', (int) $start, PDO::PARAM_INT);
    $query->bindValue(\':limit\', (int) $limit, PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetchall();
    return $data;
}

function ' . $plugin . '_details($id) {
    global $db;
    $req = $db->prepare(
    "
    SELECT ';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= '`' . $columna['Field'] . '`' . $coma . '  ';
        $i++;
    }
    $contenido .= ' 
    FROM `' . $plugin . '` 
    WHERE `id` = ? 
    ");
    $req->execute(array(
        $id
    ));
    $data = $req->fetch();
    return $data;
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
    $req = $db->prepare(" UPDATE `' . $plugin . '` SET ';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        if ($columna['Field'] != "id") {
//            $contenido .= '."' . $columna['Field'] . '=:' . $columna['Field'] . ' ' . $coma . ' "   ' . "\n";
            $contenido .= '`' . $columna['Field'] . '` =:' . $columna['Field'] . '' . $coma . ' ';
        }

        $i++;
    }
    $contenido .= ' WHERE `id`=:id ");
    $req->execute(array(
';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) ) ? "," : "";
        $contenido .= ' "' . $columna['Field'] . '" =>$' . $columna['Field'] . ' ' . $coma . '  ' . "\n";
        $i++;
    }

    $contenido .= '
));
}

function ' . $plugin . '_add(';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        if ($columna['Field'] != "id") {
            $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
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

// SEARCH
function ' . $plugin . '_search($txt, $start = 0, $limit = 999) {
    global $db;
    $data = null;
    $sql = "SELECT ';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= '`' . $columna['Field'] . '`' . $coma . '  ';
        $i++;
    }
    $contenido .= '  
            FROM `' . $plugin . '` 
            WHERE `id` = :txt ';

    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $contenido .= 'OR `' . $columna['Field'] . '` like :txt' . "\n";
    }
    $contenido .= ' 
    ORDER BY `order_by` DESC, `id` DESC
    Limit  :limit OFFSET :start
";
    $query = $db->prepare($sql);
    $query->bindValue(\':txt\', "%$txt%", PDO::PARAM_STR);
    $query->bindValue(\':start\', (int) $start, PDO::PARAM_INT);
    $query->bindValue(\':limit\', (int) $limit, PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetchall();
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

    $contenido .= 'function ' . $plugin . '_unique_from_col($col) {
    global $db;
    $data = null;
    $req = $db->prepare("SELECT $col FROM `' . $plugin . '` GROUP BY $col ");
    $req->execute(array());
    $data = $req->fetchall();
    return (isset($data)) ? $data : false;
}

// SEARCH
function ' . $plugin . '_search_by($field, $txt, $start = 0, $limit = 999) {
    global $db;
    $data = null;
    $sql = "SELECT ';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= '`' . $columna['Field'] . '`' . $coma . '  ';
        $i++;
    }
    $contenido .= '  FROM `' . $plugin . '` 
    WHERE `$field` = \'$txt\' 
    ORDER BY `order_by` DESC, `id` DESC
    Limit  $limit OFFSET $start
";
    $query = $db->prepare($sql);
//    $query->bindValue(\':field\', "field", PDO::PARAM_STR);
//    $query->bindValue(\':txt\',   "%$txt%", PDO::PARAM_STR);
//    $query->bindValue(\':start\', (int) $start, PDO::PARAM_INT);
//    $query->bindValue(\':limit\', (int) $limit, PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetchall();
    return $data;
}

function ' . $plugin . '_db_show_col_from_table($c) {
    global $db;
    $data = null;
    $req = $db->prepare("            
             SHOW COLUMNS FROM `$c`
            ");
    $req->execute(array(
    ));
    $data = $req->fetchAll();
    return $data;
}
//
function ' . $plugin . '_db_col_list_from_table($c){
    $list = array();
    foreach (' . $plugin . '_db_show_col_from_table($c) as $key => $value) {
        array_push($list, $value[\'Field\']);   
    }
    return $list;
}
//
//
';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
//        $contenido .= '`' . $columna['Field'] . '`' . $coma . '  ';
        $contenido .= 'function ' . $plugin . '_update_' . $columna['Field'] . '($id, $new_data) {

    global $db;
    $req = $db->prepare(" UPDATE `' . $plugin . '` SET `' . $columna['Field'] . '`=:new_data WHERE id=:id ");
    $req->execute(array(
        "id" => $id,
        "new_data" => $new_data,
    ));
}
//
';
        $i++;
    }

    $contenido .= '
//
function ' . $plugin . '_update_field($id, $field, $new_data) {
    switch ($field) {';
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
//        $contenido .= '`' . $columna['Field'] . '`' . $coma . '  ';
        $contenido .= '
        case "' . $columna['Field'] . '":
            ' . $plugin . '_update_' . $columna['Field'] . '($id, $new_data);
            break;
';
        $i++;
    }
    $contenido .= '

        default:
            break;
    }
}
//
function ' . $plugin . '_delete($id) {
    global $db;
    $req = $db->prepare("DELETE FROM `' . $plugin . '` WHERE `id` =? ");
    $req->execute(array($id));
}
//
// To modify this function
// Copy tis function in /www_extended/' . $plugin . '/functions.php
// and comment here (this function)
function ' . $plugin . '_add_filter($col_name, $value) {
    
    switch ($col_name) {
';
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        echo "Field: " . $campo['Field'] . " \n";
        $tipo = campos_tipo($campo['Type']);
        $te = bdd_referencias($plugin, $campo['Field']);
        //echo var_dump($tabla_externa);
        //REFERENCED_TABLE_NAME, 
        //REFERENCED_COLUMN_NAME 
        $bdd_referencias = bdd_referencias($plugin, $columna['Field']);
        $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
        $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;
        //
        if ($bdd_ref_tabla_externa) {
            $contenido .= '        case "' . $columna['Field'] . '":
            return ' . $bdd_ref_tabla_externa . '_field_id("' . $bdd_col_externa . '", $value);
            break;' . " \n";
        }
    }
    $contenido .= '       

        default:
            return $value;
            break;
    }
}
//
//
//
';
    //$i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $contenido .= 'function ' . $plugin . '_exists_' . $columna['Field'] . '($' . $columna['Field'] . '){' . "\n";

        $contenido .= '    global $db;
    $data = null;
    $req = $db->prepare("SELECT `' . $columna['Field'] . '` FROM `' . $plugin . '` WHERE   `' . $columna['Field'] . '` = ?");
    $req->execute(array($' . $columna['Field'] . '));
    $data = $req->fetch();
    //return $data[0];
    return (isset($data[0]))? $data[0] :  false; ';

        $contenido .= '
}' . "\n\n";
    }

    $contenido .= '
//        
//        
//    

';
    //$i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        //$coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? "," : "";
        $contenido .= 'function ' . $plugin . '_is_' . $columna['Field'] . '($' . $columna['Field'] . '){' . "\n";

        if ($columna['Field'] == 'id' || $columna['Field'] == 'order_by' || $columna['Field'] == 'status') {
            $contenido .= '     return (is_' . $columna['Field'] . '($' . $columna['Field'] . ') )? true : false ;' . "\n";
        } else {
            $contenido .= '     return true;' . "\n";
        }
        $contenido .= '}' . "\n\n";
    }

    $contenido .= '
//
//
function ' . $plugin . '_db_is_col_from_table($col, $table) {

    $is = false;

    if ($col == "") {
        $is = false;
    }

    if (in_array($col, ' . $plugin . '_db_col_list_from_table($table))) {
        $is = true;
    }

    return $is;
}
//
//
//
function ' . $plugin . '_is_field($field, $value) {
    $is = false;

    switch ($field) {
';
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {

        $contenido .= '     case "' . $columna['Field'] . '":
            $is = (' . $plugin . '_is_' . $columna['Field'] . '($value)) ? true : false;
            break;' . "\n";
    }
    $contenido .= '
        
        default:
            $is = false;
            break;
    }

    return $is;
}
//
//
//        
';

    $contenido .= '################################################################################' . "\n";
    $contenido .= '################################################################################' . "\n";
    $contenido .= '################################################################################' . "\n";

    return $contenido;
}

function contenido_readme($plugin) {
    $contenido = '#' . $plugin . '
## ' . $plugin . ' description
Here you can write a text

## ' . $plugin . ' help
Here you can write a text

## ' . $plugin . ' more info
Here you can write a text

## Data base: ' . $plugin . ' info' . "\n";
    $i = 0;
    foreach (bdd_columnas_segun_tabla($plugin) as $columna) {
        $coma = ($i < bdd_total_columnas_segun_tabla($plugin) - 1 ) ? ", \n" : "\n";

        $contenido .= '' . $columna['Field'] . ' ' . $coma . '  ';

        $i++;
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

function copiar_archivo($archivo_origen, $archivo_destino = null, $crear = "0", $despues_de = null) {
    $file_array = file($archivo_origen);

    $new_lines = array();

    foreach ($file_array as $line) {
        echo "$line\n";
        array_push($new_lines, $line);

        if (stristr($line, $despues_de)) {
            echo "*************************************************************\n";
            echo "Aca copio el nuevo field\n";
            array_push($new_lines, crear_parte_de_formulario($crear));
            echo "*************************************************************\n";
        }
    }

    // con esto creo el nuevo archivo 
    // paso de array a un string
    crear_archivo($archivo_destino, implode(" ", $new_lines));
}

/**
 * 
 * @param type $documento
 * @param type $inicio
 * @param type $fin
 */
function busca_y_borra_parte_de_documento($documento, $desde, $hasta) {
    $file_array = file($documento);
    $new_lines = array();
    $saltarse = false;
    // asi si no se manda hasta donde borrar, 
    // borra el bloque desde-hasta


    foreach ($file_array as $line) {

        if (stristr($line, $desde)) {
            $saltarse = true;
        }

        if (!$saltarse) {
            array_push($new_lines, $line);
        }
        if (stristr($line, $hasta)) {
            $saltarse = false;
        }
    }
    // con esto creo el nuevo archivo 
    // paso de array a un string
    crear_archivo($documento, implode(" ", $new_lines));
}

function crear_parte_de_formulario($new_field) {
    $data = '
    <?php # ' . $new_field . ' ?>
    <div class="form-group">
        <label class="control-label col-sm-3" for="contexto"><?php _t("' . $new_field . '"); ?></label>
        <div class="col-sm-8">
            <input type="text"   name="titulo" class="form-control" id="titulo" placeholder="titulo" value="" >
        </div>	
    </div>
    <?php # /' . $new_field . ' ?>
    
';
    return $data;
}

function columna_info($plugin, $columna) {
    $info = array();
    //REFERENCED_TABLE_NAME, 
    //REFERENCED_COLUMN_NAME 
    $bdd_referencias = bdd_referencias($plugin, $columna['Field']);
    $bdd_ref_tabla_externa = ($bdd_referencias['REFERENCED_TABLE_NAME']) ? $bdd_referencias['REFERENCED_TABLE_NAME'] : false;
    $bdd_col_externa = ($bdd_referencias['REFERENCED_COLUMN_NAME']) ? $bdd_referencias['REFERENCED_COLUMN_NAME'] : false;
    //
    $marca_agua = ($columna['Field']) ? $columna['Field'] : "";
    $valor = ($columna['Default']) ? $columna['Default'] : "";
    $valor = '<?php echo $' . $plugin . '[\'' . $columna['Field'] . '\']; ?>';
    $campos_tipo = campos_tipo($columna['Type']);
    $nombre = $columna['Field'];
    $id = $columna['Field'];

    $info = array(
        'bdd_referencias' => $bdd_referencias,
        'bdd_ref_tabla_externa' => $bdd_ref_tabla_externa,
        'bdd_col_externa' => $bdd_col_externa,
        'marca_agua' => $marca_agua,
        'valor' => $valor,
        'campos_tipo' => $campos_tipo,
        'nombre' => $nombre,
        "id" => $id,
    );

    return $info;
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

    crear_archivo("../$config_destino/$plugin/readme.md", contenido_readme($plugin));

    $archivos = array(
        "add.php",
        "delete.php",
        "details.php",
        "edit.php",
        "export_json.php",
        "export_pdf.php",
        "index.php",
        "ok_add.php",
        "ok_delete.php",
        "ok_edit.php",
        "ok_pagination_items_limit.php",
        "ok_show_col_from_table.php",
        "search.php",
        "search_advanced.php"
    );

    $contenido = "<?php";

    foreach ($archivos as $archivo) {
        crear_archivo("../$config_destino/$plugin/controllers/$archivo", contenido_controllers($plugin, $archivo));
    }


    $archivos = array(
        "add.php",
        "delete.php",
        "details.php",
        "edit.php",
        "export_json.php",
        "export_pdf.php",
        "form_add.php",
        "form_edit.php",
        "form_details.php",
        "form_delete.php",
        "form_search.php",
        "form_search_advanced.php",
        "form_pagination_items_limit.php",
        "form_show_col_from_table.php",
        "index.php",
        "izq.php",
        "izq_add.php",
        "izq_details.php",
        "izq_delete.php",
        "izq_edit.php",
        "der.php",
        "der_add.php",
        "der_details.php",
        "der_edit.php",
        "der_delete.php",
        "modal_form_add.php",
        "modal_form_edit.php",
        "modal_form_delete.php",
        "modal_form_search.php",
        "form_show_col_from_table.php",
        "nav.php",
        "search.php",
        "search_advanced.php",
        "table_index.php"
    );

    foreach ($archivos as $archivo) {
        crear_archivo("../$config_destino/$plugin/views/$archivo", contenido_views($plugin, $archivo));
    }
}

//
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
        echo "Field: " . $campo['Field'] . " \n";

        $tipo = campos_tipo($campo['Type']);

        $te = bdd_referencias($plugin, $campo['Field']);
        //echo var_dump($tabla_externa);
        $tabla_externa = (isset($te['REFERENCED_TABLE_NAME'])) ? $te['REFERENCED_TABLE_NAME'] : false;
        $columna_externa = (isset($te['REFERENCED_COLUMN_NAME'])) ? $te['REFERENCED_COLUMN_NAME'] : false;

        echo ($tabla_externa) ? "La tabla externa es: $tabla_externa \n" : "No \n";
        echo ($columna_externa) ? "la columlna externa es: $columna_externa \n" : "No \n";

        foreach (array("ver", "crear", "editar", "borrar") as $crud) {
            bdd_add_en_magia($config_db, $plugin, $campo['Field'], $crud, $campo['Field'], $tipo, $tabla_externa, $columna_externa, "form-control", $campo['Field'], $campo['Field'], $campo['Field'], "valor");
        }
        echo " \n";

        $tabla_externa = null;
        $columna_externa = null;
    }
}

function magia_analiza_tabla($plugin) {

    echo "*******************************************************************\n";
    echo "*** bdd_columnas_segun_tabla *************************************\n";

    foreach (bdd_columnas_segun_tabla($plugin) as $col) {
        echo "*** Field: " . $col['Field'] . " **************************** \n";
        $tipo = campos_tipo($col['Type']);
        $te = bdd_referencias($plugin, $col['Field']);
        $tabla_externa = (isset($te['REFERENCED_TABLE_NAME'])) ? $te['REFERENCED_TABLE_NAME'] : false;
        $columna_externa = (isset($te['REFERENCED_COLUMN_NAME'])) ? $te['REFERENCED_COLUMN_NAME'] : false;
        echo ($tabla_externa) ? "La tabla externa es: $tabla_externa \n" : "Tabla externa: x \n";
        echo ($columna_externa) ? "la columlna externa es: $columna_externa \n" : "Colum externa: x \n";
        echo $tipo . "\n";
        echo " \n";
    }
}
