<?php

function bd_tablas($bd) {

    global $db;
    $limit = 10;

    $data = null;

    
    $req = $db->prepare("     SHOW FULL TABLES FROM $bd   ");

    $req->execute(array($bd
    ));

    $data = $req->fetchall();
    return $data;
}
