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
  <title>Help (Home) — TransparaTrack</title>
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
        <h2 class="guide-title">1. Homepage Navigation</h2>

        <div class="guide-image-placeholder">
            <img src="/transparatrack_web/PHP/HELP/assets/5.jpg" alt="Login Page User Guide Screenshot" class="guide-image">
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
                    <td>Home Link</td>
                    <td>Navigates you back to the main TransparaTrack home page.</td>
                    <td>Click Home to return to the welcome screen at any time.</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Projects Link</td>
                    <td>Shows the list of ongoing and finished projects in your Local Barangay.</td>
                    <td>Click Projects to view the status, details, and history of all projects.</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Archive Link</td>
                    <td>Contains all past, finalized, or historical project data.</td>
                    <td>Click Archive to search and access records of completed projects.</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>History Link</td>
                    <td>Displays the action history and activities within the system.</td>
                    <td>Click History to review a chronological log of system actions.</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>About Us Link</td>
                    <td>Provides information regarding the system's vision, mission, and core values.</td>
                    <td>Click About Us to learn more about the TransparaTrack initiative.</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Profile/Log in Icon</td>
                    <td>Allows logged-in users to access their profile; acts as the Log in button otherwise.</td>
                    <td>Click the Profile Icon to manage your account or Log in to access restricted features.</td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Go to Projects Button</td>
                    <td>A quick action link leading directly to the Projects page.</td>
                    <td>Click Go to Projects to immediately navigate to the project list.</td>
                </tr>
            </tbody>
        </table>
    </section>

     <section class="section-help-guide">
        <h2 class="guide-title">2. Homepage Dashboard</h2>

        <div class="guide-image-placeholder">
            <img src="/transparatrack_web/PHP/HELP/assets/6.jpg" alt="Login Page User Guide Screenshot" class="guide-image">
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
                    <td>Total Projects Registered</td>
                    <td>A quick count of all projects currently registered and tracked in the system.</td>
                    <td>View the current total number of projects that have been officially logged.</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Total Cost of All Registered Projects</td>
                    <td>The combined financial budget or total expenses for all registered projects.</td>
                    <td>Review the aggregate cost for all projects to monitor total spending.</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Projects This Month Chart</td>
                    <td>A line or bar graph illustrating the number of new projects registered over the current month.</td>
                    <td>Analyze the project registration trend for the current 30-day period.</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Status Breakdown Chart</td>
                    <td>A bar graph showing the distribution of projects categorized by their current status (e.g., Ongoing, Delayed, Completed).</td>
                    <td>Check the current workload and progress distribution across all projects.</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Projects By Department Title</td>
                    <td>The heading for the section detailing project distribution across different Barangay departments or committees.</td>
                    <td>This section provides an overview of departmental involvement in projects.</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Projects By Category Title</td>
                    <td>The heading for the section detailing project distribution by category (e.g., Infrastructure, Education, Health).</td>
                    <td>This section provides an overview of project types across different categories.</td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>Status Legend Scroll List</td>
                    <td>The scrollable list detailing the colors and names for the Status Breakdown Chart, used when statuses exceed the visible chart area.</td>
                    <td>Scroll through this Legend List to identify all project statuses and their corresponding colors.</td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>Departmental Legend Scroll List</td>
                    <td>The scrollable list detailing the colors and names for the Projects By Department chart, used when departments exceed the visible chart area.</td>
                    <td>Scroll through this Legend List to identify all contributing departments and their corresponding colors.</td>
                </tr>
            </tbody>
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