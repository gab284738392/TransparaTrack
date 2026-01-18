<?php
session_start();
// Include the new PDO connection file
require_once 'db_connect.php'; // This provides the $pdo object

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Password is used for verification later, no general sanitization

    $response = [];

    try {
        // 1. Prepare and execute the query to fetch user data by email
        // We use $pdo->prepare() and $stmt->execute()
        $stmt = $pdo->prepare("SELECT UserID, FullName, UserRole, PasswordHash FROM Users WHERE Email = :email");
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch();

        if ($user) {
            // 2. Verify password hash
            // The password_verify function is used the same way for PDO and MySQLi
            if (password_verify($password, $user['PasswordHash'])) {
                // Successful login
                
                // 3. Store critical information in the session
                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['FullName'] = $user['FullName'];
                $_SESSION['UserRole'] = $user['UserRole'];

                $response = [
                    "status" => "success",
                    "message" => "Login successful!"
                ];
            } else {
                $response = [
                    "status" => "error",
                    "message" => "Incorrect password."
                ];
            }
        } else {
            $response = [
                "status" => "error",
                "message" => "No account found with that email."
            ];
        }

    } catch (\PDOException $e) {
        // Handle database execution errors
        error_log("Login PDO Error: " . $e->getMessage());
        $response = [
            "status" => "error",
            "message" => "A system error occurred during login."
        ];
    }

    // Send JSON response to the JavaScript
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
// If not a POST request, redirect to the login page
header("Location: login.php");
exit();
?>