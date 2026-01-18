<?php
/**
 * db_connect.php
 *
 * PDO MySQL connection for local XAMPP (place this file in PHP/db_connect.php).
 *
 * Usage:
 * - This file creates a $pdo variable (PDO instance) for use by your pages.
 * - Adjust DB_NAME, DB_USER and DB_PASS below to match your local database.
 *
 * Notes:
 * - Typical XAMPP defaults: host=127.0.0.1, user=root, password=''
 * - For production, do NOT enable display_errors and keep credentials out of source code.
 */

declare(strict_types=1);

// Toggle when developing locally to see detailed DB errors (false in production)
if (!defined('DEBUG')) {
    define('DEBUG', true);
}

// Database credentials — change DB_NAME to your actual DB
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'transparatrack'); // <- change this if your DB uses a different name
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', ''); // XAMPP default is empty password

// Charset and DSN
$charset = 'utf8mb4';
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset={$charset}";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // PDO::ATTR_PERSISTENT => true, // enable only if you understand persistent connections
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log the error for debugging
    error_log('db_connect.php: PDO connection failed: ' . $e->getMessage());

    // Show helpful error in development, generic message in production
    if (DEBUG) {
        // In development show the error so you can fix credentials/database quickly
        echo '<pre style="color:#900;background:#fff3f3;padding:12px;border-radius:6px;">';
        echo 'Database connection error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE);
        echo "\n\nDSN: " . htmlspecialchars($dsn, ENT_QUOTES | ENT_SUBSTITUTE);
        echo '</pre>';
        exit;
    } else {
        // Production: do not reveal DB details
        http_response_code(500);
        echo 'A server error occurred. Please contact the administrator.';
        exit;
    }
}