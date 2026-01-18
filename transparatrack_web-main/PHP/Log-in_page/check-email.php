<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Email - TransparaTrack</title>
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

        <!-- Right Side - Check Email Message -->
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
                    <h1>A password reset request has been processed.</h1>
                    <p>Please <span class="highlight">check your registered email address</span> for further instructions.</p>
                    <p class="redirect-message">Redirecting to login in <span id="countdown">3</span> seconds...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let seconds = 3;
        const countdownElement = document.getElementById('countdown');
        
        const interval = setInterval(() => {
            seconds--;
            if (countdownElement) {
                countdownElement.textContent = seconds;
            }
            
            if (seconds === 0) {
                clearInterval(interval);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
