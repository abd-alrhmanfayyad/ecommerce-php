<?php


$db_host    = 'localhost';
$db_name    = 'ecommerce_php';   
$db_user    = 'root';
$db_pass    = '';   
$db_charset = 'utf8mb4';   

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";



// Options: how PDO should behave
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // (1) on an error: throw an exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,    // (2) rows come back as ['name' => 'Wireless Mechanical Keyboard', ...]
    PDO::ATTR_EMULATE_PREPARES   => false,                // (3) real prepared statements, not emulated
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());         
    die('Database connection failed.');  
}
