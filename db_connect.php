<?php
$host = 'localhost';
$dbname = 'dbflay4krug2x5';
$username = 'udjcaze4o5oac';
$password = 'eosj4sdfvnqs';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

session_start();
?>
