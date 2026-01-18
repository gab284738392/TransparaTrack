<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - TransparaTrack</title>
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

        <!-- Right Side - Signup Form -->
        <div class="right-side">
            <nav class="top-nav">
                <a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a>
                <a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a>
                <a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a>
                <a href="http://localhost/transparatrack_web/PHP/history.php">History</a>
                <a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a>
            </nav>

            <div class="form-container">
                <h1>Sign up</h1>
                
                <?php
                // Display error messages if any
                if (isset($_GET['error'])) {
                    $errorMessages = [
                        'password_mismatch' => 'Passwords do not match!',
                        'invalid_email' => 'Only institutional emails (@rtu.edu.ph) are allowed for signup.',
                        'email_exists' => 'Email already registered! Please use another email.',
                        'duplicate_entry' => 'Error: That email or username is already taken. Please try a different name or email.',
                        'system_error' => 'Error during registration: A system error occurred.'
                    ];
                    $error = $_GET['error'];
                    if (isset($errorMessages[$error])) {
                        echo '<div class="error-message">' . htmlspecialchars($errorMessages[$error]) . '</div>';
                    }
                }
                ?>
                
                <form id="signupForm" action="signup-process.php" method="POST">
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" required>
                        </div>
                        <div class="form-group half">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-password" onclick="togglePassword('password','eyeIconSignup')">
                                <svg id="eyeIconSignup" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <!-- Closed Eye (hidden password) -->
                                    <path class="eye-closed" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-5.07 5.93m-2.12-3.81a3 3 0 1 1-4.24-4.24"></path>
                                    <line class="eye-closed" x1="1" y1="1" x2="23" y2="23"></line>
                                    <!-- Open Eye (visible password) -->
                                    <path class="eye-open" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" style="display:none;"></path>
                                    <circle class="eye-open" cx="12" cy="12" r="3" style="display:none;"></circle>
                                </svg>
                            </span>
                        </div>
                        <div class="password-requirements" id="passwordRequirements" style="display: none;">
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
                            <span class="toggle-password" onclick="togglePassword('confirmPassword','eyeIconConfirmSignup')">
                                <svg id="eyeIconConfirmSignup" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                            I agree with <a href="terms-conditions.php" target="_blank">Terms and Conditions</a> and <a href="privacy-policy.php" target="_blank">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary">Sign up</button>
                </form>

                <p class="signup-link">
                    Already have an account? <a href="login.php">Log in</a>
                </p>
            </div>
        </div>
    </div>

    <script src="signup.js"></script>
</body>
</html>
