<?php
// history.php — History page showing project action history (converted to "cards list" layout)

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

/* -------------------------
   Secure session configuration & START
   ------------------------- */

// Decide if we are on HTTPS (best-effort check).
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';

// Set cookie params before starting the session.
$defaultCookieParams = [
    'lifetime' => 0, // default browser lifetime
    'path'     => '/',
    'domain'   => '',
];

session_set_cookie_params(
    $defaultCookieParams['lifetime'],
    $defaultCookieParams['path'],
    $defaultCookieParams['domain'],
    $isHttps,
    true // httponly
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
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}

$loggedName = getLoggedInName();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Help — TransparaTrack</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/transparatrack_web/PHP/assets/style.css">
  <link rel="stylesheet" type="text/css" href="/transparatrack_web/PHP/assets/style.css">
  <link rel="shortcut icon" href="../assets/tplogo.svg">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Aleo:wght@700&family=Kodchasan:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --deep-blue: #27294A;
    }

    body {
      background-color: #EBE9E9;
    }

    .subheader-title,
    .about-brand,
    .about-column h3,
    .value-title,
    .core-values-heading h3,
    #commitment-heading {
      font-family: 'Aleo', serif;
      font-weight: 700;
      color: var(--deep-blue, #27294A);
    }
    .header-title {
      font-family: 'Kodchasan', sans-serif;
      font-weight: 700;
      color: var(--deep-blue, #27294A);
    }
    .about-container {
      width: 100%;
      margin: 28px auto;
      padding: 0 150px;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
      color: #222;
      line-height: 1.6;
      max-width: none;
    }
    .about-intro {
      text-align: center;
      padding: 36px 24px;
      margin-bottom: 120px;
      background: transparent;
    }

    /* Generic brand divider (used in several places) */
    .brand-divider {
      height: 3px;
      width: 240px;
      margin: 0 auto 12px;
      border-radius: 2px;
      background: linear-gradient(90deg, #F3B900 0%, #CE2323 50%, #4149B7 100%);
      opacity: 0.9;
    }

    .about-brand {
      font-size: 22px;
      margin: 0 0 10px 0;
      letter-spacing: 0.2px;
    }

    .footer-logo {
      font-family: 'Kodchasan', sans-serif !important;
      font-weight: 700;
      color: var(--deep-blue, #27294A);
      font-size: 30px;
      text-align: right;
    }

    /* Help list styles (cards displayed in a vertical list) */
    .help-container {
      width: 100%;
      margin: 28px auto;
      /* ADJUSTED: reduce horizontal padding from 150px -> 120px so the list can be a bit wider and align better
         with other site containers while keeping consistent gutters */
      padding: 20px 120px;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
      color: #222;
      line-height: 1.6;
      max-width: none;
    }

    /* New: semantic list wrapper */
    .help-list {
      list-style: none;
      padding: 0;
      margin: 18px auto 0 auto;
      /* ADJUSTED: increased max-width so the cards become a little wider and visually align with other pages */
      max-width: 1220px; /* widened from 1200px */
      display: block;
    }

    .help-list-item {
      margin-bottom: 14px;
    }

    /* Reuse .help-card but adapt it to horizontal layout inside a list item */
    .help-card {
      display: flex;
      align-items: center;
      gap: 16px;
      background: #fff;
      border-radius: 10px;
      padding: 16px 18px;
      text-decoration: none;
      color: var(--deep-blue);
      box-shadow: 0 6px 18px rgba(0,0,0,0.04);
      font-weight: 600;
      transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
      border: 1px solid rgba(39,41,74,0.04);
      width: 100%; /* allow cards to use the full available width of .help-list */
    }
    .help-card:hover,
    .help-card:focus {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(0,0,0,0.08);
      outline: none;
      background: #fbfdff;
    }

    /* Small icon/leading block to identify each card */
    .card-icon {
      flex: 0 0 56px;
      height: 56px;
      border-radius: 8px;
      display: inline-block;
      box-shadow: 0 4px 12px rgba(65,73,183,0.12);
    }

    .card-content {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 3px;
    }

    .card-title {
      font-size: 18px;
      color: var(--deep-blue);
      margin: 0;
      font-weight: 700;
    }
    .card-desc {
      font-size: 14px;
      color: #0c0c0cff;
      margin: 0;
      font-weight: 500;
    }

    /* keep your existing rules for the icon itself */
    .card-chevron svg,
    .card-chevron img {
      width: 12px;
      height: 12px;
      display: block;
    }

    /* Responsive adjustments */
    @media (max-width: 1000px) { .help-container { padding: 20px 24px; } .help-list { max-width: 760px; } }
    @media (max-width: 600px) { 
      .help-list { max-width: 100%; }
      .help-card { padding: 12px; gap: 12px; }
      .card-icon { flex: 0 0 44px; height: 44px; }
      .footer-logo { text-align: center; } 
    }
  </style>
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

<!--
  NOTE: per your instruction I will NOT change the page body color or the size/styling of the "Help" heading,
  and I will NOT change the footer. The block below updates the card layout only.
-->

<style>
  /* card-layout-only overrides — do NOT change body background or .subheader-title */
  :root {
    --accent-red: #b72323;
    --muted-pink: #f3dada;
    --gold: #e5b840;
  }

  /* list container */
  .help-list {
    /* ADJUSTED: increased max-width here as well for consistency with the first definition above */
    max-width: 1220px; /* increased width so cards can be wider and align with other site content */
    margin: 18px auto 0;
    padding: 0;
    list-style: none;
  }

  .help-list-item {
    margin-bottom: 18px;
  }

  /* Card base - updated layout to match reference (left icon, red title, gold underline, chevron to right)
     Increased padding and larger icon to take advantage of the wider canvas */
  .help-card {
    display: flex;
    align-items: center;
    gap: 24px; /* larger gap for a more spacious layout */
    background: #ffffff;
    border-radius: 8px;
    padding: 20px 28px; /* increased horizontal padding to make card feel wider */
    text-decoration: none;
    color: #222;
    border: none;
    position: relative;
    width: 100%;
  }

  .help-card:hover,
  .help-card:focus {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.08);
    outline: none;
  }

  /* Left icon square - slightly larger to match the card width */
  .card-icon {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    background: var(--muted-pink);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 72px;
  }

  .card-icon img,
  .card-icon svg {
    width: 40px;
    height: 40px;
    display: block;
  }

  /* Content */
  .card-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    flex: 1 1 auto; /* allow content to grow and use extra width */
  }

  .card-title {
    color: var(--accent-red);
    font-weight: 700;
<<<<<<< HEAD
    font-size: 16px;
    margin: 0;
    font-family: 'Kodchasan', sans-serif
=======
    font-size: 20px; /* keep title readable at larger sizes */
    margin-bottom: -2px;
    font-family: 'Kodchasan', serif;
>>>>>>> d25d70b3bf89ff919ceb93d0fc248ef31d16566d
  }

  /* Decorative gold underline under title (stretches across content area) */
  .title-underline {
    height: 2px;
    background: linear-gradient(90deg, var(--gold), #d8a93a);
    width: 100%;
    border-radius: 2px;
    margin-top: 1px;
    margin-bottom: 6px;
    opacity: 0.95;
  }

  .card-desc {
    color: #0c0c0cff;
    font-size: 14px;
    margin-top: 1px;
    font-weight: 500;
<<<<<<< HEAD
    line-height: 1.4;
    font-family: 'Kodchasan', sans-serif
=======
    line-height: 1.5; /* slightly more breathing room for longer lines */
>>>>>>> d25d70b3bf89ff919ceb93d0fc248ef31d16566d
  }

  /* Right chevron / affordance */
  .card-chevron {
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

    .card-chevron svg,
    .card-chevron img {
      width: 20px;
      height: 20px;
      display: block;
    }

  /* Responsive tweaks */
  @media (max-width: 1200px) {
    .help-list { max-width: 1000px; }
  }
  @media (max-width: 1000px) {
    .help-container { padding-left: 24px; padding-right: 24px; }
    .help-list { max-width: 760px; }
    .help-card { padding: 16px; gap: 16px; }
    .card-icon { width: 64px; height: 64px; flex: 0 0 64px; }
  }
  @media (max-width: 600px) {
    .card-icon { width: 52px; height: 52px; flex: 0 0 52px; border-radius: 10px; }
    .help-card { padding: 12px; gap: 12px; }
    .card-title { font-size: 15px; }
    .card-desc { font-size: 12px; }
  }
</style>

<div class="subheader-bar" role="banner" aria-hidden="false">
  <div class="subheader-title">Help</div>
</div>

<main class="help-container" aria-labelledby="help-heading">
  <ul class="help-list" role="list" aria-label="Help topics list">
    <li class="help-list-item">
      <!-- Added target and rel so the card opens in a new tab securely -->
      <a class="help-card" href="/transparatrack_web/PHP/HELP/h_loginandsignup.php" aria-label="Login and Signup help" target="_blank" rel="noopener noreferrer">
        <span class="card-icon" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/login_icon.svg" alt="">
        </span>

        <span class="card-content">
          <span class="card-title">Login & Sign up</span>
          <span class="title-underline" aria-hidden="true"></span>
          <span class="card-desc">Access your account securely or create a new one. Learn how to log in, register, and recover your password to ensure seamless access to the system.</span>
        </span>

        <span class="card-chevron svg" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/next_icon.svg" alt="">
        </span>
      </a>
    </li>

    <li class="help-list-item">
      <a class="help-card" href="/transparatrack_web/PHP/HELP/h_home.php" aria-label="Home help" target="_blank" rel="noopener noreferrer">
        <span class="card-icon" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/home_icon.svg" alt="">
        </span>

        <span class="card-content">
          <span class="card-title">Home</span>
          <span class="title-underline" aria-hidden="true"></span>
          <span class="card-desc">Your central dashboard for an overview of all activities. Understand how to navigate the system, view key project updates, and monitor progress at a glance.</span>
        </span>

        <span class="card-chevron svg" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/next_icon.svg" alt="">
        </span>
      </a>
    </li>

    <li class="help-list-item">
      <a class="help-card" href="/transparatrack_web/PHP/HELP/h_projects.php" aria-label="Projects help" target="_blank" rel="noopener noreferrer">
         <span class="card-icon" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/projects_icon.svg" alt="">
        </span>

        <span class="card-content">
          <span class="card-title">Projects</span>
          <span class="title-underline" aria-hidden="true"></span>
          <span class="card-desc">Discover how to create, manage, and track projects efficiently. Learn to assign tasks, update statuses, and monitor project progress in real time.</span>
        </span>

        <span class="card-chevron svg" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/next_icon.svg" alt="">
        </span>
      </a>
    </li>

    <li class="help-list-item">
      <a class="help-card" href="/transparatrack_web/PHP/HELP/h_archive.php" aria-label="Archive help" target="_blank" rel="noopener noreferrer">
         <span class="card-icon" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/archive_icon.svg" alt="">
        </span>

        <span class="card-content">
          <span class="card-title">Archive</span>
          <span class="title-underline" aria-hidden="true"></span>
          <span class="card-desc">Learn how completed or inactive projects are stored. Access instructions on viewing, restoring, or permanently managing archived projects.</span>
        </span>

        <span class="card-chevron svg" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/next_icon.svg" alt="">
        </span>
      </a>
    </li>

    <li class="help-list-item">
      <a class="help-card" href="/transparatrack_web/PHP/HELP/h_history.php" aria-label="History help" target="_blank" rel="noopener noreferrer">
         <span class="card-icon" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/history_icon.svg" alt="">
        </span>

        <span class="card-content">
          <span class="card-title">History</span>
          <span class="title-underline" aria-hidden="true"></span>
          <span class="card-desc">Review detailed activity logs and audit trails. Understand how actions within the system are recorded for transparency and accountability.</span>
        </span>

        <span class="card-chevron svg" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/next_icon.svg" alt="">
        </span>
      </a>
    </li>

    <li class="help-list-item">
      <a class="help-card" href="/transparatrack_web/PHP/HELP/h_profile.php" aria-label="Profile help" target="_blank" rel="noopener noreferrer">
        <span class="card-icon" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/profile_icon.svg" alt="">
        </span>

        <span class="card-content">
          <span class="card-title">Profile</span>
          <span class="title-underline" aria-hidden="true"></span>
          <span class="card-desc">Manage your personal account information, update your avatar, and adjust your settings to personalize your TransparaTrack experience.</span>
        </span>

        <span class="card-chevron svg" aria-hidden="true">
          <img src="/transparatrack_web/PHP/media/next_icon.svg" alt="">
        </span>
      </a>
    </li>

  </ul>
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