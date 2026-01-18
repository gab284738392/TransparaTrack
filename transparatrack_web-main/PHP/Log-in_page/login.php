<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — TransparaTrack</title>
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

        <!-- Right Side - Login Form -->
        <div class="right-side">
            <nav class="top-nav">
                <a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a>
                <a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a>
                <a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a>
                <a href="http://localhost/transparatrack_web/PHP/history.php">History</a>
                <a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a>
            </nav>

            <div class="form-container">
                <h1>Login</h1>
                
                <form id="loginForm" action="login-process.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>   
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-password" id="togglePasswordBtn" onclick="togglePassword()">
                                <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <!-- Closed Eye (hidden password) -->
                                    <path class="eye-closed" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-5.07 5.93m-2.12-3.81a3 3 0 1 1-4.24-4.24"></path>
                                    <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                    <!-- Open Eye (visible password) -->
                                    <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" style="display:none;"></path>
                                    <circle class="eye-open" cx="12" cy="12" r="3" style="display:none;"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="form-links">
                        <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                    </div>

                    <div id="errorMessage" class="error-message" style="display: none;">
                        <span id="errorText"></span>
                    </div>

                    <div id="attemptsWarning" class="attempts-warning" style="display: none;">
                        Attempts remaining: <span id="attemptsCount">5</span>
                    </div>

                    <button type="submit" class="btn-primary">Login</button>
                </form>

                <p class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </p>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>