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
  <title>About Us — TransparaTrack</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
  <link rel="shortcut icon" href="assets/tplogo.svg">
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

    /*
     * Vision & Mission adjustments:
     * - Headers left-aligned
     * - Divider above/below headers set to 2px x 240px per requested size
     * - Paragraph block aligns with the header and keeps the existing type styles
     */
    .about-columns .brand-divider {
      height: 3px;          /* requested height */
      width: 120px;         /* requested width */
      margin: 0 0 12px 6px; /* left offset so it lines up with heading text */
      border-radius: 2px;
      background: linear-gradient(90deg, #F3B900 0%, #CE2323 50%, #4149B7 100%);
      opacity: 0.95;
    }

    .about-columns .about-brand {
      text-align: left;
      font-size: 24px;
      margin: 6px 0 12px 6px; /* small left inset to match divider */
      color: var(--deep-blue);
    }

    /* Keep Core Values heading centered */
    .core-values-wrap .about-brand {
      text-align: center;
    }

    /* Ensure the top About (TransparaTrack) heading stays centered */
    .about-intro .about-brand {
      text-align: center;
      margin-left: 0;
      font-size: 24px;
    }

    .about-intro p {
      margin: 16px auto;
      max-width: 100%;
      color: #222;
      font-size: 16px;
      text-align: center;
    }
    .about-intro p strong { font-weight: 700; color: #111; }
    .about-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: start; margin-top: 150px; margin-bottom: 140px;}
    .about-column h3 { font-size: 20px; margin: 0 0 12px 0; position: relative; }
    .about-column .section-divider { height: 3px; width: 40px; background: linear-gradient(90deg, #F3B900 0%, #CE2323 50%, #4149B7 100%); border-radius: 2px; margin-bottom: 20px; }
    .about-column p {
      font-family: 'Inter', sans-serif;
      color: #222;
      font-size: 16px;
      margin: 20px 0 12px 6px; /* small left margin to visually align with header text and dividers */
      text-align: justify;
    }
    .core-values-wrap { margin-top: 48px; margin-bottom: 120px; }
    .core-values-heading { text-align: center; margin-bottom: 22px; }
    .core-values-heading .section-divider { height: 3px; width: 68px; background: linear-gradient(90deg, #F3B900 0%, #CE2323 50%, #4149B7 100%); border-radius: 2px; margin-bottom: 24px; }
    .core-values-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
    .value-card { background: #ffffff; border-radius: 8px; padding: 34px 30px; box-shadow: 0 4px 18px rgba(0,0,0,0.04); text-align: center; min-height: 220px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; margin-top: 20px; }
    .value-icon { width: 72px; height: 72px; margin-bottom: 16px; display: block; fill: #AD2020; }
    .value-title { font-size: 20px; color: #AD2020; margin-bottom: 12px; }
    .value-text { font-family: 'Inter', sans-serif; color: #222; font-size: 14px; text-align: center; line-height: 1.5; max-width: 320px; }

    /*
     * Commitment section:
     * - Center the heading ("Our Commitment") and paragraph text.
     * - Keep a max-width to improve readability on wide screens.
     * - On small screens revert to left-aligned paragraphs for readability.
     */
    .commitment { margin-top: 48px; background: transparent; padding: 36px 20px; text-align: center; margin-bottom: 100px; }
    .commitment .brand-divider { margin: 0 auto 12px; width: 160px; }
    .commitment .about-brand { text-align: center; margin-left: 0; font-size: 22px; }
    .commitment p { max-width: 1300px; margin: 10px auto; color: #222; font-size: 16px; text-align: center; font-family: 'Inter', sans-serif; }
    .commitment p strong { font-weight: 700; }

    /*
     * Footer-specific override: ensure only the footer logo uses Kodchasan
     * We removed .footer-logo from the Aleo group above and apply Kodchasan explicitly here.
     */
    .footer-logo {
      font-family: 'Kodchasan', sans-serif !important;
      font-weight: 700;
      color: var(--deep-blue, #27294A);
      font-size: 30px;
      text-align: right;
    }

    @media (max-width: 1000px) { .about-container { padding: 0 24px; } }
    @media (max-width: 920px) {
      .about-columns { grid-template-columns: 1fr; }
      .core-values-grid { grid-template-columns: 1fr; }
      .value-text { max-width: 100%; }
      .about-intro p { text-align: left; }
      .commitment p { text-align: left; }
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

<div class="subheader-bar" role="banner" aria-hidden="false">
  <div class="subheader-title">About Us</div>
</div>

<main class="about-container">

  <section class="about-intro" aria-labelledby="about-main-title">
    <div class="brand-divider" aria-hidden="true"></div>
    <div class="about-brand" id="about-main-title">TransparaTrack</div>
    <div class="brand-divider" aria-hidden="true" style="margin-top:8px;"></div>

    <p>
      TransparaTrack is an innovative digital transparency and monitoring system dedicated to transforming how barangay‑level projects are managed, tracked, and presented to the public. Designed to strengthen accountability and openness in local governance, TransparaTrack serves as a bridge between <strong>government institutions and the citizens they serve.</strong>
    </p>

    <p>
      The platform provides an accessible, data‑driven environment where project information—such as status, funding, implementing agencies, and timelines—can be monitored and verified in real time. By harnessing technology and digital tools, TransparaTrack aims to reduce information gaps, prevent corruption, and promote <strong>efficient and ethical project implementation</strong> at the grassroots level.
    </p>

    <p>
      Ultimately, TransparaTrack aspires to foster a culture of <strong>open governance</strong>—one that values trust, civic participation, and technological innovation in the pursuit of public service excellence.
    </p>
  </section>

  <section class="about-columns" aria-label="Vision and Mission">
    <div class="about-column">
      <div class="brand-divider" aria-hidden="true"></div>
      <div class="about-brand" id="vision-heading">Vision</div>
      <div class="brand-divider" aria-hidden="true" style="margin-top:6px;margin-left:6px;"></div>

      <p>
        To promote <strong>transparency, accountability, and accuracy</strong> in local governance by providing a digital platform that enables barangay officials and citizens to track the progress, cost, and performance of public projects with clarity and accuracy.
      </p>

      <p>
        We are dedicated to empowering communities through technology by transforming traditional monitoring methods into a <strong>real‑time, data‑driven process</strong>. Our mission extends beyond digitalization — it is about restoring public confidence, ensuring responsible governance, and making every government initiative traceable and understandable to the people it serves.
      </p>

      <p>
        Through TransparaTrack, we aim to make transparency not just a principle, but a <strong>practical and accessible tool</strong> for every barangay in the Philippines.
      </p>
    </div>

    <div class="about-column">
      <div class="brand-divider" aria-hidden="true"></div>
      <div class="about-brand" id="mission-heading">Mission</div>
      <div class="brand-divider" aria-hidden="true" style="margin-top:6px;margin-left:6px;"></div>

      <p>
        To build a <strong>digitally transparent nation</strong> where every barangay practices good governance guided by integrity, accountability, and public trust.
      </p>

      <p>
        TransparaTrack envisions a future where technology seamlessly integrates with governance to create a <strong>society of informed and empowered citizens</strong>. In this vision, transparency becomes routine rather than exceptional, and data‑driven decision‑making drives equitable development across local communities.
      </p>

      <p>
        We look forward to a Philippines where local leaders uphold honesty and openness in every action, and where every citizen has the means to witness and take part in the progress of their barangay.
      </p>
    </div>
  </section>

  <section class="core-values-wrap" aria-labelledby="values-heading">
    <div class="brand-divider" aria-hidden="true"></div>
    <div class="about-brand" id="values-heading">Core Values</div>
    <div class="brand-divider" aria-hidden="true" style="margin-top:8px;"></div>

    <div class="core-values-grid" role="list">
      <div class="value-card" role="listitem" aria-label="Transparency">
        <img class="value-icon" src="media/transparency.svg" alt="Transparency icon">
        <div class="value-title">Transparency</div>
        <div class="value-text">
          We believe that openness is the foundation of progress. By providing clear, accurate, and accessible information, we strengthen public confidence and ensure that governance remains accountable to its people.
        </div>
      </div>

      <div class="value-card" role="listitem" aria-label="Accountability">
        <img class="value-icon" src="media/accountability.svg" alt="Accountability icon">
        <div class="value-title">Accountability</div>
        <div class="value-text">
          We take pride in upholding integrity and responsibility in all our operations. Every data point, report, and update reflects our unwavering commitment to truth and accuracy in governance.
        </div>
      </div>

      <div class="value-card" role="listitem" aria-label="Innovation">
        <img class="value-icon" src="media/innovation.svg" alt="Innovation icon">
        <div class="value-title">Innovation</div>
        <div class="value-text">
          We continuously strive to improve our platform through cutting‑edge technology and creative solutions. Innovation allows us to transform complex project data into meaningful insights that drive progress and informed decision‑making.
        </div>
      </div>

      <div class="value-card" role="listitem" aria-label="Public Empowerment">
        <img class="value-icon" src="media/public_empowerment.svg" alt="Public Empowerment icon">
        <div class="value-title">Public Empowerment</div>
        <div class="value-text">
          We are committed to giving people the tools they need to participate actively in governance. Through easy access to project data, we empower communities to question, engage, and contribute to sustainable local development.
        </div>
      </div>
    </div>
  </section>

<section class="commitment" aria-labelledby="commitment-heading">
  <div class="brand-divider" aria-hidden="true"></div>
  <div class="about-brand" id="commitment-heading">Our Commitment</div>
  <div class="brand-divider" aria-hidden="true" style="margin-top:8px;"></div>

  <p>
    At TransparaTrack, we are steadfast in our commitment to <strong>building trust through transparency.</strong> Our system is more than a monitoring tool—it is a step toward transforming the culture of governance in the Philippines.
  </p>

  <p>
    We pledge to maintain the highest standards of accuracy, reliability, and inclusivity in every aspect of our service. By combining technology with integrity, <strong>TransparaTrack aims to eliminate barriers between public institutions and the people they serve.</strong>
  </p>

  <p>
    We envision a society where <strong>every citizen becomes a stakeholder in progress</strong>, where every project is visible and traceable, and where honesty is not only expected—but embedded in the system itself.
  </p>

  <p>
    Through TransparaTrack, we reaffirm our belief that <strong>transparency is not just a goal, but a way forward for a better, more accountable future.</strong>
  </p>
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