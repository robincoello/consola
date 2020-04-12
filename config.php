<?php

$config_server = "localhost";
$config_db = "a5";
$config_login = "root";
$config_pass = "root";
$db = new PDO("mysql:host=$config_server;dbname=$config_db", "$config_login", "$config_pass");