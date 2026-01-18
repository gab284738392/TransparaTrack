<?php
// history.php — History page showing project action history

// 1. Setup and Connection Check
// IMPORTANT: The session must be started FIRST, before any output AND 
// before setting session parameters. The security block below now handles the first start.

// IMPORTANT: Ensure the path to db_connect.php is correct. 
// If history.php is not in the same directory as db_connect.php, adjust the path:
// e.g., require_once '../db_connect.php'; 
require_once 'db_connect.php'; 

// --- FIX: DEFINE WEB ROOT PATH ---
// This path is necessary for the profile picture to load correctly from the web root.
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/';

// --- REMOVED LOGIN CHECK BLOCK ---
// The following block is commented out to make the page accessible to everyone.
/*
if (!isset($_SESSION['UserID'])) {
    // Corrected path assumption for login page
    header("Location: Log-in_page/login.php");
    exit;
}
*/

/**
 * about_us.php - Refined styling for Vision & Mission headings (small colored dividers
 * above and below, left-aligned) and Core Values centered. Other structure and security
 * improvements retained.
 *
 * Notes:
 * - Session/security helpers unchanged from previous version.
 * - Consider moving the large <style> block into assets/style.css for maintainability.
 */

/* -------------------------
   Secure session configuration & START
   This block now contains the ONLY session_start() call.
   ------------------------- */

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
        // Note: $base_web_path is now defined above
        $profilePath = $userRow['ProfileImagePath'] ?? '';

        if (!empty($profilePath)) {
            // Check if $base_web_path is defined, otherwise assume root path
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
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Help (History) — TransparaTrack</title>
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
    
    <section class="section-help-guide">
        <h2 class="guide-title">1. History</h2>

        <div class="guide-image-placeholder">
                        <?php
                            $histPathWeb = '/transparatrack_web/PHP/HELP/assets/history.png';
                            $histPathFs = __DIR__ . '/assets/history.png';
                            $histVer = is_file($histPathFs) ? filemtime($histPathFs) : time();
                        ?>
                        <img src="<?php echo $histPathWeb . '?v=' . $histVer; ?>" alt="History Page" class="guide-image">
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
                <tr>
                    <td>1</td>
                    <td>Name Column</td>
                    <td>Displays the full name of the user who performed the activity.</td>
                    <td>View only; used for identifying who made the action.</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>System Role Column</td>
                    <td>Shows the system role assigned to the user, such as Staff.</td>
                    <td>View only; helps understand the user’s permission level.</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Action Column</td>
                    <td>Indicates the specific activity or operation carried out by the user.</td>
                    <td>View only; used for tracking what action was performed.</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Action Time Column</td>
                    <td>Records the exact date and time when the activity occurred.</td>
                    <td>View only; used for monitoring the sequence of events.</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Details Column</td>
                    <td>Displays the project title involved.</td>
                    <td>Click to view more detailed information.</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Project Link</td>
                    <td>Contains a clickable link that directs to the associated project.</td>
                    <td>Click to open and review the full project page.</td>
                </tr>
            </tbody>
        </table>
    </section>
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