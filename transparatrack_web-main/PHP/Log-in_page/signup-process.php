<?php
// signup-process.php (PDO Version - Corrected)

// Use require_once for db_connect.php
require_once 'db_connect.php'; // Now using PDO $pdo object

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Combine names
    $fullName = $firstName . ' ' . $lastName;
    $username = strtolower($firstName . "." . $lastName); // e.g., "john.doe"

    // --- VALIDATION ---
    if ($password !== $confirmPassword) {
        header("Location: signup.php?error=password_mismatch");
        exit();
    }

    // Validate institutional email
    $emailDomain = substr(strrchr($email, "@"), 1);
    if ($emailDomain !== "rtu.edu.ph") {
        header("Location: signup.php?error=invalid_email");
        exit();
    }
    // --- END VALIDATION ---


    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Default role for signups
    $userRole = "Staff";

    try {
        // Check if email already exists
        $checkEmail = $pdo->prepare("SELECT UserID FROM Users WHERE Email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->rowCount() > 0) {
            header("Location: signup.php?error=email_exists");
            exit();
        }
        
        // --- FIX: Check if username already exists ---
        $checkUsername = $pdo->prepare("SELECT UserID FROM Users WHERE Username = ?");
        $checkUsername->execute([$username]);
        
        if ($checkUsername->rowCount() > 0) {
            // If username is taken, append a random two-digit number
            $username = $username . rand(10, 99);
        }

        // Insert user record
        // (FullName, Username, PasswordHash, Email, ContactNum, UserRole)
        $stmt = $pdo->prepare("INSERT INTO Users (FullName, Username, PasswordHash, Email, ContactNum, UserRole) VALUES (?, ?, ?, ?, '', ?)");
        $stmt->execute([$fullName, $username, $passwordHash, $email, $userRole]);

        header("Location: signup-success.php");
        exit();

    } catch (\PDOException $e) {
        // Handle database errors
        error_log("Signup PDO Error: " . $e->getMessage());

        // --- FIX: Provide a more specific error for duplicate entries ---
        if ($e->getCode() == 23000) {
            // 23000 is the SQL code for Integrity Constraint Violation (e.g., duplicate key)
             header("Location: signup.php?error=duplicate_entry");
             exit();
        } else {
             // For all other errors, show the generic message
             header("Location: signup.php?error=system_error");
             exit();
        }
    }

} else {
    // Not a POST request
    header("Location: signup.php");
    exit();
}
?>