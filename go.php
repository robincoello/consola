<?php

include 'config.php';
include 'campos.php';
include 'v2.php';
include 'bd.php';

$config_destino = "www";

################################################################################
################################################################################
echo "-=- MAGIA PHP -=-" . "\n";
echo "-- Tablas por crear el plugin\n" . "\n";

echo "$config_destino \n";
echo "Tables in: $config_db \n";

//var_dump(bd_tablas($config_db));

$plugins = array();

$i = 1;
foreach (bd_tablas($config_db) as $key => $value) {
    //echo var_dump($value);  
    $ti = "Tables_in_$config_db";
    if (!file_exists("../$config_destino/$value[$ti]")) {
        array_push($plugins, $value[$ti]);
//        echo "$i - $value[$ti] \n";
    }
    $i++;
}

$i = 0;
foreach ($plugins as $key => $p) {
    echo "$i) $p \n";
    $i++;
}

echo "Escoja un plugin?\n";
do {
    $opcion = trim(fgets(STDIN)); // lee una línea de STDIN        
} while ($opcion > count($plugins) || $opcion <= -1 || $opcion === '');

$plugin = $plugins[$opcion];

################################################################################
################################################################################
crear_plugin($plugin);
crear_clase($plugin);
echo "\n";
echo "\n";
echo "\n";
echo "\n";
echo "1 ) ## ADD CONTROLLER ################################################\n";
echo "Registro del plugin como controlador en la base de datos\n";
echo var_dump(bdd_add_controllers($plugin));
echo "2) ## PERMISOS #######################################################\n";
echo "3) Agrego los permisos para el admin ***" . "\n";
//bdd_add_permissions($plugin, "admin", 1110);
echo "4) Agrego los permisos para root ----- \n";
bdd_add_permissions($plugin, "root", 1111);
echo "5) ## MENU ###########################################################\n";
echo "agrego al menu \n";

var_dump(bdd_menu_search($plugin, 'top', 'config', $plugin));

if (!bdd_menu_search($plugin, 'top', 'config', $plugin)) { // si no hay en el menu lo instalamos
    // $location, $father, $label, $url, $icon, $order_by    
    bdd_add_en_menu("top", "config", $plugin, "index.php?c=$plugin", "far fa-folder", "0");
}
echo "6) ## MAGIA ###########################################################\n";
echo "Registro en la tabla magia\n";
//magia_registrar_en_tabla($plugin);
echo "7) ## FIN ############################################################\n";
echo "Registro en Magia";
echo "\n";
