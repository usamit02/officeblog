<?php
$f = fopen(__DIR__.'/../../private_ini/mysql.ini','r');
$dsn = "mysql:host=".trim(fgets($f)).";charset=utf8";
$user = trim(fgets($f));
$password = trim(fgets($f));
$dbname=trim(fgets($f));
$dsn = $dsn.";dbname=".$dbname;
$mymail=trim(fgets($f));
fclose($f);
$db = new PDO($dsn, $user, $password)
?>