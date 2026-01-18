<?php
// db_connect.php (PDO Version - CORRECTED)

// 1. Connection Credentials
$host = 'localhost';         
$db   = 'TransparaTrack';    
$user = 'root';              // <-- CONFIRM THIS IS YOUR MySQL/MariaDB USERNAME
$pass = '';                  // <-- CONFIRM THIS IS YOUR MySQL/MariaDB PASSWORD (Often empty string '' for XAMPP root)
$charset = 'utf8mb4';

// 2. Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. PDO Options
$options = [
    // Throw exceptions on errors (recommended for development)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Fetch results as associative arrays by default
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
    // Disable emulation for better security (true prepared statements)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 4. Connection Attempt
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // Connection successful, $pdo object is now available
     
} catch (\PDOException $e) {
     // If connection fails, log it and prevent further script execution errors
     error_log("Database connection error: " . $e->getMessage());
     $pdo = null; // Ensure $pdo is explicitly null on failure
     
     // CRITICAL: Display a temporary message if connection fails
     // This will allow the adminprofile.php to continue and display the content
     // but the project list will still be empty due to the subsequent error check.
     // You may uncomment the line below for severe debugging:
     // die("System connection error. Check db_connect.php credentials. Error: " . $e->getMessage());
}
?>