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
  <title>Help (Archive) — TransparaTrack</title>
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
        <h2 class="guide-title">1. Archive</h2>

        <div class="guide-image-placeholder">
            <img src="/transparatrack_web/PHP/HELP/assets/archive.png" alt="Archive Page" class="guide-image">
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
                    <td>View Completed Projects Button</td>
                    <td>Shows all projects marked as completed.</td>
                    <td>Click to open the list of completed projects.</td>
                </tr>
            </tbody>
        </table>
    </section>
    </div>
    
    <h2 class="guide-title">2. Archive by Year</h2>

    <div class="guide-image-placeholder">
        <img src="/transparatrack_web/PHP/HELP/assets/archive2.png" alt="Archive Page" class="guide-image">
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
                    <td>Search Bar (Project Title)</td>
                    <td>Allows you to find specific projects by entering keywords from the project's name.</td>
                    <td>Type the project title you are searching for and click Search.</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Search Button</td>
                    <td>Executes the search query entered in the search bar.</td>
                    <td>Click Search after inputting the project title to filter results.</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Sort Dropdown</td>
                    <td>A menu used to organize the project list based on criteria like date, budget, or status.</td>
                    <td>Click the Sort Dropdown to change the order of the displayed project cards.</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>Year Filter</td>
                    <td>Allows you to filter the project list based on the year the project was initiated or registered.</td>
                    <td>Select a specific year from the dropdown to see projects from that period.</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>Budget Filter</td>
                    <td>Allows you to filter the project list based on the budget range of the project.</td>
                    <td>Select a budget range from the dropdown to see relevant projects.</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>Status Checkboxes</td>
                    <td>A set of checkboxes used to filter projects based on their current progress (e.g., Ongoing, Completed).</td>
                    <td>Check the desired Status boxes to view only those projects.</td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>More Button (Filters)</td>
                    <td>Expands or collapses the list of available filters for a category, useful for managing screen space.</td>
                    <td>Click More to display additional filtering options for the related criteria.</td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>Category Checkboxes</td>
                    <td>A set of checkboxes used to filter projects based on their type (e.g., Infrastructure, Education).</td>
                    <td>Check the desired Category boxes to narrow the project results.</td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>Department Checkboxes</td>
                    <td>A set of checkboxes used to filter projects based on the implementing department or committee.</td>
                    <td>Check the desired Department boxes to see projects handled by specific committees.</td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>Apply Filters Button</td>
                    <td>Applies all the selected filter criteria to the project list.</td>
                    <td>Click Apply Filters after making all your selections to update the project cards.</td>
                </tr>
                <tr>
                    <td>11</td>
                    <td>Reset Button</td>
                    <td>Clears all currently selected filter criteria, restoring the default list view.</td>
                    <td>Click Reset to quickly remove all applied filters.</td>
                </tr>
                <tr>
                    <td>12</td>
                    <td>Project Card</td>
                    <td>A summary block displaying key information (title, status, budget, department) for a single project.</td>
                    <td>Review the Project Card for a quick overview, or click View Project to see details.</td>
                </tr>
            </tbody>
        </table>
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