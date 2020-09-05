<?php
include './config.php';
include './v2-backup.php';
include './campos.php';

echo var_dump(bdd_columnas_segun_tabla("bacs"));