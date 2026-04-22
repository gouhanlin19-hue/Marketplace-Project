<?php
$hostname="localhost";
$dbname="omnesmarketplace";
$dbuser="root";
$dbpassword="";
try{
	$pdo = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpassword); // create connection

	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // get data from query
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // use real prepared statements, app secure
} catch (PDOException $e) {
	die ("Connection failed: " . $e->getMessage()); //with echo the script keep running
}
