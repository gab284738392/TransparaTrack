<?php
// h_loginandsignup.php — Help page for Login and Signup

// 1. Setup and Connection Check
require_once 'db_connect.php';

// --- FIX: DEFINE WEB ROOT PATH ---
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/';

/* -------------------------
   Secure session configuration & START
   ------------------------- */

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
$defaultCookieParams = [
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
];

session_set_cookie_params(
    $defaultCookieParams['lifetime'],
    $defaultCookieParams['path'],
    $defaultCookieParams['domain'],
    $isHttps,
    true
);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

session_start();

/* -------------------------
   Security headers
   ------------------------- */
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com;");

// --- HEADER: decide whether to show login button or admin avatar ---
$headerRightHtml = '<a href="/transparatrack_web/PHP/Log-in_page/login.php" class="btn-login">Log in</a>';
if (isset($_SESSION['UserID'])) {
    try {
        $stmtUser = $pdo->prepare("SELECT ProfileImagePath, FullName FROM Users WHERE UserID = :id LIMIT 1");
        $stmtUser->execute([':id' => $_SESSION['UserID']]);
        $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $profilePath = $userRow['ProfileImagePath'] ?? '';

        if (!empty($profilePath)) {
            $imgSrc = $base_web_path . $profilePath;
        } else {
            $imgSrc = '/transparatrack_web/PHP/ADMIN PROFILE/assets/profile.svg';
        }

        $headerRightHtml = '<a href="/transparatrack_web/PHP/ADMIN PROFILE/adminprofile.php" class="admin-avatar"><img src="' . htmlspecialchars($imgSrc, ENT_QUOTES) . '" alt="Admin Profile"></a>';
    } catch (\Exception $e) {
        // keep default login button on error
    }
}

/* -------------------------
   Helpers
   ------------------------- */
function esc(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

// --- DATA STRUCTURE: SEPARATED BY SECTION ---
// This allows us to loop and print distinct tables for Login, Signup, and Reset.
$helpSections = [
    'login' => [
        'section_title' => '1. Login',
        'image' => '/transparatrack_web/PHP/HELP/assets/2.png', // Replace with your actual screenshot filename
        'rows' => [
            [
                'num' => '1',
                'title' =>'Email Address',
                'desc' => 'The main access point requiring your registered Email Address.',
                'action' => 'Enter your registered email address in the email field.'
            ],
            [
                'num' => '2',
                'title' => 'Password',
                'desc' => 'Your secure password for account access.',
                'action' => 'Enter your password in the password field.'
            ],
            [
                'num' => '3',
                'title' => 'Login Button',
                'desc' => 'Button to submit your login credentials.',
                'action' => 'Click the Login button to access your account.'
            ]
        ]
    ],
    'signup' => [
        'section_title' => '2. Sign-up',
        'image' => '/transparatrack_web/PHP/HELP/assets/3.png', // Replace with your actual screenshot filename
        'rows' => [
            [
                'num' => '1',
                'title' => 'First Name',
                'desc' => 'Your given name for account registration.',
                'action' => 'Enter your first name in the first name field.'
            ],
            [
                'num' => '2',
                'title' => 'Last Name',
                'desc' => 'Your family name for account registration.',
                'action' => 'Enter your last name in the last name field.'
            ],
            [
                'num' => '3',
                'title' => 'Email Address',
                'desc' => 'Your institutional email address for account verification.',
                'action' => 'Enter your valid email address in the email field.'
            ],
            [
                'num' => '4',
                'title' => 'Password',
                'desc' => 'Create a secure password for your account.',
                'action' => 'Enter a strong password in the password field.'
            ],
            [
                'num' => '5',
                'title' => 'Confirm Password',
                'desc' => 'Re-enter your password to confirm it matches.',
                'action' => 'Re-type your password in the confirm password field.'
            ],
            [
                'num' => '6',
                'title' => 'Terms and Conditions',
                'desc' => 'Agreement to the platform terms and conditions.',
                'action' => 'Check the box to agree to the terms and conditions.'
            ],
            [
                'num' => '7',
                'title' => 'Privacy Policy',
                'desc' => 'Agreement to the platform privacy policy.',
                'action' => 'Check the box to agree to the privacy policy.'
            ],
            [
                'num' => '8',
                'title' => 'Sign-up Button',
                'desc' => 'Button to submit your registration information.',
                'action' => 'Click the Sign-up button to create your account.'
            ]
        ]
    ],
    'reset' => [
        'section_title' => '3. Reset Password',
        'image' => '/transparatrack_web/PHP/HELP/assets/4.png', // Replace with your actual screenshot filename
        'rows' => [
            [
                'num' => '1',
                'title' => 'Email Address',
                'desc' => 'Your registered email address for password reset.',
                'action' => 'Enter your registered email address in the email field.'
            ],
            [
                'num' => '2',
                'title' => 'Reset Password Button',
                'desc' => 'Button to submit your password reset request.',
                'action' => 'Click the Reset Password button to receive reset instructions.'
            ]
        ]
    ]
];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Help (Login & Signup) — TransparaTrack</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" type="text/css" href="/transparatrack_web/PHP/assets/style.css">
  <link rel="stylesheet" href="style.css">
<link rel="shortcut icon" href="../assets/tplogo.svg">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Aleo:wght@700&family=Kodchasan:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

 <?php
    echo '<div class="header-bar">';
        echo '<div class="header-top">';
            echo '<div class="icon-container">
                    <img src="/transparatrack_web/PHP/ADMIN PROFILE/assets/tplogo.svg" alt="TransparaTrack Logo">
                  </div>';
            echo '<div class="title-wrapper">';
                echo '<span class="header-title">TransparaTrack</span>';
                echo '<span class="header-subtitle">See the process, Start the progress</span>';
            echo '</div>';
            echo '<div class="admin-icon-container">';
                echo $headerRightHtml;
            echo '</div>';
        echo '</div>';
        
        echo '<div class="gradient-line"></div>';
        
        echo '<nav class="nav-container">';
        echo '  <ul>';
        echo '    <li><a href="/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/project.php">Projects</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/archive.php">Archive</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/history.php">History</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/about_us.php">About Us</a></li>';
        echo '  </ul>';
        echo '</nav>';
    echo '</div>';
    ?>

<div class="subheader-bar" role="banner" aria-hidden="false">
  <div class="subheader-title">Help</div>
</div>

<main>

<div class="help-content-container">

    <?php foreach ($helpSections as $key => $section): ?>
        <h2 class="guide-title"><?php echo esc($section['section_title']); ?></h2>

        <div class="guide-image-placeholder">
            <img src="<?php echo esc($section['image']); ?>" 
                 alt="<?php echo esc($section['section_title']); ?> Screenshot" 
                 class="guide-image">
        </div>
        
        <table class="guide-table">
            <thead>
                <tr>
                    <th class="col-number">Number</th>
                    <th class="col-title">Title</th>
                    <th class="col-description">Description</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($section['rows'] as $row): ?>
                <tr>
                    <td class="col-number" data-label="Number"><?php echo esc($row['num']); ?></td>
                    <td class="col-title" data-label="Title"><?php echo esc($row['title']); ?></td>
                    <td class="col-description" data-label="Description"><?php echo esc($row['desc']); ?></td>
                    <td class="col-action" data-label="Action"><?php echo esc($row['action']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

</div>

</main>

<footer class="site-footer">
  <div class="footer-gradient-line"></div>
  <div class="footer-content-area">
    <div class="footer-column">
      <ul>
        <li><a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/history.php">History</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a></li>
      </ul>
    </div>
    <div class="footer-column">
      <ul>
        <li><a href="http://localhost/transparatrack_web/PHP/ADMIN%20PROFILE/adminprofile.php">Profile</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/terms-conditions.php">Terms and Conditions</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/privacy-policy.php">Privacy Policy</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/HELP/help.php">Help</a></li>
      </ul>
    </div>
    <div class="footer-logo">TransparaTrack</div>
  </div>
</footer>

</body>
</html>