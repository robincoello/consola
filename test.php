<?php
include './config.php';
include './exportar-backup.php';

echo var_dump(bdd_referencias("test", "contact_id"));