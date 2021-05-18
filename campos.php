<?php
//function bdd_tipo_campo($Type) {    
function campos_tipo($SQL_Type) {    

    $t = false;
    if (strpos($SQL_Type, "int(1)") !== false) {
        $t = "boolean";
    }
    if (strpos($SQL_Type, "int(11)") !== false) {
        $t = "int";
    }

    if (strpos($SQL_Type, "varchar(") !== false) {
        $t = "text";
    }
    if (strpos($SQL_Type, "date") !== false) {
        $t = "date";
    }
    if (strpos($SQL_Type, "timestamp") !== false) {
        $t = "timestamp";
    }
    return $t;
}


function campos_crear_campo($tipo, $nombre, $id, $clase ="form-control", $marca_agua="", $valor=""  ) {
    global $plugin;


    $te = bdd_referencias($plugin, $nombre);

    //echo var_dump($tabla_externa);
    $tabla_externa = ( $te['REFERENCED_TABLE_NAME']) ? $te['REFERENCED_TABLE_NAME'] : "";
    $columna_externa = ( $te['REFERENCED_COLUMN_NAME']) ? $te['REFERENCED_COLUMN_NAME'] : "";

    
    
    
    
    
    switch ($tipo) {

        case 'select':
            $campo = '<select  name="' . $nombre . '" class="' . $clase . ' selectpicker" id="' . $id . '" data-live-search="true">
                <?php ' . $tabla_externa . '_select("' . $columna_externa . '","' . $columna_externa . '", array(), array()); ?>                        
                </select>';
            break;

        case 'boolean':
            $campo = '<select name="' . $nombre . '" class="' . $clase . '" id="' . $id . '">                
                <option value="0">Off</option>
                <option value="1">On</option>                
                </select>';
            break;
                
        case 'int':            
            $campo = '<input type="number"  name="' . $nombre . '" class="' . $nombre . '" id="' . $id . '" placeholder="' . $marca_agua . ' - int">';            
            break;
        
        case 'text':            
            $campo = '<textarea type="number"  name="' . $nombre . '" class="' . $clase . '" id="' . $id . '" placeholder="' . $marca_agua . ' - textarea"></textarea>';            
            break;
        
        case 'date':            
            $campo = '<input type="date"  name="' . $nombre . '" class="' . $clase . '" id="' . $id . '" placeholder="' . $marca_agua . ' - date">';            
            break;

        case 'timestamp':            
            $campo = '<input type="date"  name="' . $nombre . '" class="' . $clase . '" id="' . $id . '" placeholder="' . $marca_agua . ' - timestamp">';            
            break;

        default:           
            $campo = '<input type="text"  name="' . $nombre . '" class="' . $clase . '" id="' . $id . '" placeholder="' . $marca_agua . ' - defecto">';           
            break;
    }

    return $campo;
}
