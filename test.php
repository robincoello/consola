<?php
include './config.php';
include './exportar-backup.php';
include './campos.php';

echo var_dump(bdd_columnas_segun_tabla("bacs"));