<?php
// update_profile_script.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php'; // Provides $pdo

// 1. Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    $_SESSION['error_message'] = "You must be logged in to update your profile.";
    header("Location: ../Log-in_page/login.php"); 
    exit;
}

// 2. Check if this is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_id = $_SESSION['UserID'];
    
    // 3. Sanitize text inputs
    $full_name = filter_input(INPUT_POST, 'profile_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username  = filter_input(INPUT_POST, 'profile_username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email     = filter_input(INPUT_POST, 'profile_email', FILTER_SANITIZE_EMAIL);
    
    $current_profile_image_status = $_POST['current_profile_image_path'] ?? '';

    if (empty($full_name) || empty($username) || empty($email)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: adminprofile.php"); 
        exit;
    }

    // --- 4. Handle File Upload or Removal ---
    $new_file_path_for_db = null; 
    $oldPath = null;
    $should_update_image = false; // Flag to track if we need to change the DB path

    // First, get the user's current image path from the DB
    try {
        $stmt = $pdo->prepare("SELECT ProfileImagePath FROM Users WHERE UserID = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $oldPath = $stmt->fetchColumn(); 
    } catch (\PDOException $e) {
        error_log("Error fetching old profile pic path for user $user_id: " . $e->getMessage());
    }

    // Case 1: A new file has been uploaded
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            
            $profile_pic_dir = __DIR__ . '/uploads/profiles/';
            if (!is_dir($profile_pic_dir)) {
                mkdir($profile_pic_dir, 0777, true);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_name = 'user_' . $user_id . '_' . uniqid() . '.' . $ext;
            $target_file = $profile_pic_dir . $unique_name;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $new_file_path_for_db = 'uploads/profiles/' . $unique_name;
                $should_update_image = true;
            } else {
                $_SESSION['error_message'] = "Failed to move uploaded file.";
            }
        } else {
             $_SESSION['error_message'] = "Invalid file type or size (Max 5MB, JPG/PNG only).";
        }
    } 
    // Case 2: The "Remove" button was clicked
    else if ($current_profile_image_status === '__REMOVE_IMAGE__') {
        $new_file_path_for_db = null; // Set to NULL to clear in DB
        $should_update_image = true;
    }
    // Case 3: No change. $should_update_image remains false.

    // --- 5. Update the database ---
    try {
        $sql = "UPDATE Users 
                SET FullName = :fullname, 
                    Username = :username, 
                    Email = :email";
        
        $params = [
            ':fullname' => $full_name,
            ':username' => $username,
            ':email'    => $email,
            ':user_id'  => $user_id
        ];

        // Only modify ProfileImagePath if a new file was uploaded OR removal was requested
        if ($should_update_image) {
            $sql .= ", ProfileImagePath = :image_path";
            $params[':image_path'] = $new_file_path_for_db; // Will be the new path or NULL
        }
        
        $sql .= " WHERE UserID = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // --- 6. Delete old file AFTER DB update is successful ---
        if ($should_update_image && $oldPath && @file_exists(__DIR__ . '/' . $oldPath)) {
            @unlink(__DIR__ . '/' . $oldPath); // Suppress errors if file delete fails
        }

        // 7. Update session
        $_SESSION['FullName'] = $full_name;

        if (!isset($_SESSION['error_message'])) {
            $_SESSION['success_message'] = "Profile updated successfully!";
        }
        
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) { 
            $_SESSION['error_message'] = "That username or email is already taken.";
        } else {
            $_SESSION['error_message'] = "A database error occurred. Please try again.";
        }
        error_log("Profile Update Error: " . $e->getMessage());
    }

    // 8. Redirect back (This should now work)
    header("Location: adminprofile.php"); 
    exit;

} else {
    // Not a POST request, redirect
    header("Location: adminprofile.php"); 
    exit;
}
?>