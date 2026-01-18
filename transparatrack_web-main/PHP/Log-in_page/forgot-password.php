
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - TransparaTrack</title>
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
            <nav class="top-nav">
                <a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a>
                <a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a>
                <a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a>
                <a href="http://localhost/transparatrack_web/PHP/history.php">History</a>
                <a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a>
            </nav>

            <a href="login.php" class="return-login">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Return to Log In
            </a>

            <div class="form-container">
                <h1>Forgot Password</h1>
                <p class="subtitle">Enter your RTU email to receive a password reset link.</p>
                
                <?php
                // Display error messages if any
                if (isset($_GET['error'])) {
                    $errorMessages = [
                        'invalid_token' => 'Invalid or expired token. Please request a new password reset link.',
                        'no_token' => 'Invalid access. No token found.',
                        'invalid_email' => 'Only RTU email addresses are allowed.',
                        'email_not_found' => 'Email not found in our records.',
                        'mailer_error' => 'Failed to send email. Please try again later.',
                        'missing_email' => 'Please enter your email address.'
                    ];
                    $error = $_GET['error'];
                    if (isset($errorMessages[$error])) {
                        echo '<div class="error-message">' . htmlspecialchars($errorMessages[$error]) . '</div>';
                    }
                }
                ?>
                
                <form action="send-reset-link.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn-primary">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

