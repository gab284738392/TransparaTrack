<?php
// db_connect.php (using PDO)
$host = 'localhost';         
$db   = 'TransparaTrack';    
$user = 'root';              // Use your actual credentials
$pass = '';                  // Use your actual credentials
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options); // Connection is stored in $pdo
} catch (\PDOException $e) {
     error_log("Database connection error: " . $e->getMessage());
     die("A critical system error occurred. Please try again later.");
}
?>