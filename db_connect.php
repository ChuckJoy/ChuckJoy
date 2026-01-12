<?php
// db_connect.php

// LOCALHOST SETTINGS (Default for XAMPP/WAMP)
$host = 'localhost';
$db   = 'tunetolight';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// IONOS / PRODUCTION SETTINGS (Uncomment and fill when deploying)
// $host = 'db5000.ionos.com'; 
// $db   = 'dbs1234567';
// $user = 'dbu1234567';
// $pass = 'YourSecurePassword!';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Let exceptions bubble up to api.php for clean handling
$pdo = new PDO($dsn, $user, $pass, $options);
?>