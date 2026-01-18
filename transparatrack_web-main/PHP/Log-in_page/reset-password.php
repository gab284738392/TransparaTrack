<?php
include('db_connect.php');
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE reset_token = ? 
          AND token_expiry > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: forgot-password.php?error=invalid_token");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        if ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match. Please try again.";
        } elseif (strlen($newPassword) < 8) {
            $error = "Password must be at least 8 characters.";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET PasswordHash = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
            $update->execute([$hashedPassword, $token]);

            header("Location: reset-success.php");
            exit();
        }
    }
} else {
    header("Location: forgot-password.php?error=no_token");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - TransparaTrack</title>
    <link rel="stylesheet" href="auth-styles.css">
<link rel="shortcut icon" href="../assets/tplogo.svg">
</head>
<body>
<div class="container">
    <div class="left-side">
        <div class="message-box">
            <h2>Mabuhay, Ka-Barangay!</h2>
            <h1>Para sa Tapat na Pamumuno.</h1>
            <p>Etsekalado para sa mga barangay leaders na nagsusulong ng tapat na serbisyo.</p>
        </div>
    </div>

    <div class="right-side">
        <div class="form-container">
            <h1>Reset Password</h1>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" id="resetPasswordForm">
                <div class="form-group">
                    <label for="newPassword">Enter New Password</label>
                    <div class="password-input">
                        <input type="password" id="newPassword" name="newPassword" required>
                        <span class="toggle-password" onclick="togglePassword('newPassword','eyeIconResetNew')">
                            <svg id="eyeIconResetNew" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <!-- Closed Eye (hidden password) -->
                                <path class="eye-closed" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                <!-- Open Eye (visible password) -->
                                <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" style="display:none;"></path>
                                <circle class="eye-open" cx="12" cy="12" r="3" style="display:none;"></circle>
                            </svg>
                        </span>
                    </div>

                    <div class="password-requirements" id="passwordRequirements">
                        <ul>
                            <li id="req-length">At least 8 characters</li>
                            <li id="req-uppercase" style="display: none;">At least one uppercase letter</li>
                            <li id="req-lowercase" style="display: none;">At least one lowercase letter</li>
                            <li id="req-number" style="display: none;">At least one number</li>
                            <li id="req-special" style="display: none;">At least one special character</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="password-input">
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <span class="toggle-password" onclick="togglePassword('confirmPassword','eyeIconResetConfirm')">
                            <svg id="eyeIconResetConfirm" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <!-- Closed Eye (hidden password) -->
                                <path class="eye-closed" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                <!-- Open Eye (visible password) -->
                                <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" style="display:none;"></path>
                                <circle class="eye-open" cx="12" cy="12" r="3" style="display:none;"></circle>
                            </svg>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Reset Password</button>
            </form>
        </div>
    </div>
</div>

<script src="reset-password.js"></script>
</body>
</html>
