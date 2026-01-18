<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Success - TransparaTrack</title>
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

        <!-- Right Side - Success Message -->
        <div class="right-side">
            <nav class="top-nav">
                <a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a>
                <a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a>
                <a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a>
                <a href="http://localhost/transparatrack_web/PHP/history.php">History</a>
                <a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a>
            </nav>

            <div class="form-container center-content">
                <div class="success-message">
                    <div class="success-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    </div>
                    
                    <h1>You have <span class="highlight-green">successfully logged in!</span></h1>
                    <p>Welcome back to TransparaTrack.</p>
                    
                    <p class="redirect-message">Redirecting to your profile in <span id="countdown">3</span> seconds...</p>
                    
                    <a href="../ADMIN PROFILE/adminprofile.php" class="btn-primary" style="display: inline-block; margin-top: 25px;">Go to Profile</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto redirect after 3 seconds
        let seconds = 3;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = '../ADMIN PROFILE/adminprofile.php';
            }
        }, 1000);
    </script>
</body>
</html>
