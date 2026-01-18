<?php

require_once 'db_connect.php'; 

// --- FIX: DEFINE WEB ROOT PATH ---
// This path is necessary for the profile picture to load correctly from the web root.
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/';

// Decide if we are on HTTPS (best-effort check).
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';

// Set cookie params before starting the session.
// Note: session_get_cookie_params() can only be called if a session is NOT active.
// Since we want to set params BEFORE session_start(), we define default params.
$defaultCookieParams = [
    'lifetime' => 0, // default browser lifetime
    'path'     => '/',
    'domain'   => '',
];

// PHP 7.3+ has session_set_cookie_params(array $options) - safer to use older signature
session_set_cookie_params(
    $defaultCookieParams['lifetime'],
    $defaultCookieParams['path'],
    $defaultCookieParams['domain'],
    $isHttps,
    true // httponly
);
// Explicitly set samesite for added security (Lax)
ini_set('session.cookie_samesite', 'Lax');

// Enforce strict mode (helps prevent session fixation on some setups)
ini_set('session.use_strict_mode', '1');

// START THE SESSION ONCE
session_start();

/* -------------------------
   Security headers
   ------------------------- */
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com;");

// --- HEADER: decide whether to show login button or admin avatar ---
// This section remains intact but will now display the login button if the user
// is not logged in, instead of forcing a redirect.
$headerRightHtml = '<a href="/transparatrack_web/PHP/Log-in_page/login.php" class="btn-login">Log in</a>';
if (isset($_SESSION['UserID'])) {
    try {
        $stmtUser = $pdo->prepare("SELECT ProfileImagePath, FullName FROM Users WHERE UserID = :id LIMIT 1");
        $stmtUser->execute([':id' => $_SESSION['UserID']]);
        $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $profilePath = $userRow['ProfileImagePath'] ?? '';

        if (!empty($profilePath)) {
            // $base_web_path is now defined above, ensuring correct path resolution
            $imgSrc = $base_web_path . $profilePath; // Use the corrected base path
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
    if ($s === null) {
        return '';
    }
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function getLoggedInName(): ?string {
    $keys = [
        'loggedInFullName',
        'userFullName',
        'FullName',
        'full_name',
        'name',
        'loggedInUser',
        'username',
        'user',
    ];
    foreach ($keys as $k) {
        if (!empty($_SESSION[$k]) && is_string($_SESSION[$k])) {
            $val = trim($_SESSION[$k]);
            if ($val !== '') {
                return $val;
            }
        }
    }
    return null;
}

if (empty($_SESSION['csrf_token'])) {
    // This now runs *after* session_start() successfully
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

$loggedName = getLoggedInName();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - TransparaTrack</title>
    <link rel="stylesheet" href="privacy-policy.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="/transparatrack_web/PHP/assets/style.css">
    <link rel="shortcut icon" href="../assets/tplogo.svg">
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

    <main class="privacy-content">
        <h1>Terms and Condition</h1>

        <section>
            <h2>I. General Provisions</h2>
            <p>These Terms and Conditions govern the access to and use of TransparaTrack, a digital platform developed to enhance transparency, accountability, and efficiency in the monitoring of barangay and government infrastructure projects.</p>
            <p>By accessing or using the TransparaTrack system, users expressly acknowledge that they have read, understood, and agreed to be bound by the provisions set forth herein. Non-acceptance of these Terms and Conditions shall preclude the user from accessing or utilizing any part of the system.</p>
        </section>

        <section>
            <h2>II. Authorized Users</h2>
            <p>Access to TransparaTrack is strictly limited to authorized barangay officials, administrative personnel, and designated government representatives duly recognized by their respective agencies or local government units.</p>
            <p>Unauthorized access, sharing of credentials, or misrepresentation of identity for the purpose of gaining system entry shall constitute a violation of these Terms and may result in disciplinary, administrative, or legal action, as appropriate.</p>
        </section>

        <section>
            <h2>III. User Responsibilities</h2>
            <p>All registered users are expected to exercise the highest standards of integrity, diligence, and confidentiality when utilizing the system. Specifically, users shall:</p>
            <ol type="a">
                <li>Ensure that all data encoded, uploaded, or submitted are accurate, truthful, and updated;</li>
                <li>Use the system exclusively for legitimate official purposes related to project monitoring and governance reporting;</li>
                <li>Maintain the confidentiality of system credentials, and promptly report any suspected breach or unauthorized access;</li>
                <li>Refrain from the transmission or introduction of malicious software, misleading information, or inappropriate content that may compromise system security or public trust.</li>
            </ol>
        </section>

        <section>
            <h2>IV. Data Accuracy and Limitation of Liability</h2>
            <p>TransparaTrack endeavors to maintain accurate and up-to-date information based on data submitted by partner agencies and barangay offices. However, the system does not guarantee absolute accuracy, completeness, or timeliness of all information displayed.</p>
            <p>The administrators shall not be held liable for any loss, damage, or misinterpretation resulting from the reliance on data obtained through the system. Verification through official channels is advised where necessary.</p>
        </section>

        <section>
            <h2>V. System Access and Availability</h2>
            <p>The availability of the TransparaTrack platform may be subject to interruptions due to scheduled maintenance, system updates, or unforeseen technical difficulties.</p>
            <p>The administrators and developers shall not be liable for any temporary inaccessibility or technical disruption that may affect system use or data retrieval.</p>
        </section>

        <section>
            <h2>VI. Data Privacy and Security</h2>
            <p>All data collected, stored, or processed within TransparaTrack shall be handled in accordance with the provisions of the Data Privacy Act of 2012 (Republic Act No. 10173) and other applicable laws.</p>
            <p>The system shall employ appropriate technical and organizational measures to safeguard personal and project-related information against unauthorized access, alteration, disclosure, or destruction.</p>
        </section>

        <section>
            <h2>VII. Intellectual Property Rights</h2>
            <p>All intellectual property rights pertaining to the design, content, source code, documentation, and other system components of TransparaTrack are owned by its developers, partner institutions, and authorized governing agencies.</p>
            <p>Any unauthorized reproduction, modification, or redistribution of system materials, in whole or in part, is strictly prohibited and may subject the offender to civil and criminal liability under applicable laws.</p>
        </section>

        <section>
            <h2>VIII. Prohibited Acts</h2>
            <p>The following acts are expressly prohibited:</p>
            <ol>
                <li>Attempting to gain unauthorized access to the system, servers, or networks connected thereto;</li>
                <li>Reverse-engineering, decompiling, or tampering with the system's source code or security features;</li>
                <li>Uploading or disseminating false, defamatory, or politically motivated content;</li>
                <li>Using the platform for personal gain, propaganda, or activities contrary to public interest.</li>
            </ol>
        </section>

        <section>
            <h2>IX. Account Suspension and Termination</h2>
            <p>The TransparaTrack administration reserves the right to suspend, restrict, or terminate user access without prior notice upon determination of any violation of these Terms and Conditions or upon discovery of activities that may compromise system integrity or public trust.</p>
        </section>

        <section>
            <h2>X. Amendments</h2>
            <p>These Terms and Conditions may be modified or amended periodically to reflect system improvements, legal updates, or administrative directives. Users shall be duly notified of substantial revisions through official system announcements or correspondence.</p>
            <p>Continued use of the system following such amendments shall constitute acceptance of the updated Terms and Conditions.</p>
        </section>

        <section>
            <h2>XI. Governing Law</h2>
            <p>These Terms and Conditions shall be governed by and construed in accordance with the laws of the Republic of the Philippines. Any dispute arising from or related to the use of TransparaTrack shall be subject to the jurisdiction of the appropriate courts of law in the Philippines.</p>
        </section>

        <section class="contact-box">
            <h2>XII. Contact Information</h2>
            <p>For clarifications, technical assistance, or reports regarding system misuse, users may contact the TransparaTrack administrators through the official support channels of their respective local government units or agencies.</p>
        </section>
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