<?php

include './config.php';
include './campos.php';
include './v2.php';
include './bd.php';

//$config_destino = "www_extended/$theme" ;
$config_destino = "www" ;

################################################################################
################################################################################
echo "-=- MAGIA PHP -=-" . "\n" ;
echo "-- Tablas por crear el plugin\n" ;
echo "-- $config_destino -- " . "\n" ;
echo "\n\n" ;


$plugins = array() ;

$i = 1 ;
foreach ( bd_tablas($config_db) as $key => $value ) {
    //echo var_dump($value);  
    $ti = "Tables_in_$config_db" ;

    if ( ! file_exists("../$config_destino/$value[$ti]/models/$config_db.php") ) {

        array_push($plugins , $value[$ti]) ;

        //echo "$i - $value[$ti] \n"; 
    }
    $i ++ ;
}

$i = 0 ;
foreach ( $plugins as $key => $p ) {
    echo "$i) $p \n" ;
    $i ++ ;
}




echo "Escoja un plugin?\n" ;
do {
    $opcion = trim(fgets(STDIN)) ; // lee una línea de STDIN        
} while ( $opcion > count($plugins) || $opcion <= -1 ) ;

//echo var_dump($plugins[$opcion]);

$plugin = $plugins[$opcion] ;



if ( $plugin ) {

################################################################################
################################################################################
//
    crear_clase($plugin) ;
    echo "###########################################################\n" ;
    echo "############################################################\n" ;
//echo "Registro en Magia";
    echo "\n" ;
}