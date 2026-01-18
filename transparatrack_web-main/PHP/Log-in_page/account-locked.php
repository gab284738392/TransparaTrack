<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Locked - TransparaTrack</title>
    <link rel="stylesheet" href="auth-styles.css">
    <link rel="shortcut icon" href="../assets/tplogo.svg">
</head>
<body>
    <div class="container">
        <!-- Left Side - Gradient with Message -->
        <div class="left-side">
            <div class="message-box">
                <h2>Mabuhay, Ka-Barangay!</h2>
                <h1>Para sa Tapat na Pamumuno.</h1>
                <p>Etsekalado para sa mga barangay leaders na nagsusulong ng tapat na serbisyo.</p>
            </div>
        </div>

        <!-- Right Side - Account Locked Message -->
        <div class="right-side">
            <nav class="top-nav">
                <a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a>
                <a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a>
                <a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a>
                <a href="http://localhost/transparatrack_web/PHP/history.php">History</a>
                <a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a>
            </nav>

            <div class="form-container center-content">
                <div class="locked-message">
                    <h1>For security reasons, your account has been <span class="highlight-red">temporarily locked</span> after multiple unsuccessful login attempts.</h1>
                    
                    <p class="retry-message">You may try again after 5 minutes.</p>
                    
                    <a href="login.php" class="btn-secondary" style="display: inline-block; margin-top: 20px;">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
