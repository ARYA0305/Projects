<?php
error_reporting(0);
$dbHost = 'localhost:3307';
$dbUser = 'root';
$dbPass = '';
$dbName = 'gmskolkata';
$db_con = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName)
       or die('Error Connecting to MySQL DataBase');
?>
