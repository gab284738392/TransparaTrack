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
    <title>Privacy Policy - TransparaTrack</title>
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
        <h1>Privacy Policy</h1>

        <section>
            <h2>I. General Statement</h2>
            <p>The TransparaTrack System is committed to safeguarding the privacy and security of all information collected, processed, and stored within its digital infrastructure. This Privacy Policy outlines how TransparaTrack manages personal data and project-related information in compliance with the Data Privacy Act of 2012 (Republic Act No. 10173), its Implementing Rules and Regulations, and other applicable laws and regulations of the Republic of the Philippines.</p>
            <p>By using this system, users acknowledge that they have read, understood, and consented to the collection and processing of their personal data in accordance with this Policy.</p>
        </section>

        <section>
            <h2>II. Scope and Coverage</h2>
            <p>This Privacy Policy applies to all users of the TransparaTrack platform, including barangay officials, administrators, government personnel, and other authorized representatives who access or input data into the system. It covers all forms of data collected — whether manually submitted or automatically processed — through the platform.</p>
        </section>

        <section>
            <h2>III. Information Collected</h2>
            <p>TransparaTrack may collect the following categories of data:</p>
            <ol>
                <li>Personal Information
                    <ul>
                        <li>Full name</li>
                        <li>Position or designation</li>
                        <li>Contact details (email address, mobile number, etc.)</li>
                        <li>Account credentials (username, password, and related identifiers)</li>
                    </ul>
                </li>
                <li>Project Information
                    <ul>
                        <li>Project title, description, and location</li>
                        <li>Funding allocation and budget details</li>
                        <li>Implementation status and related documentation</li>
                        <li>Names of contractors or implementing offices</li>
                    </ul>
                </li>
                <li>System and Technical Data
                    <ul>
                        <li>Log files, timestamps, and access history</li>
                        <li>Device and browser information</li>
                        <li>IP address and geographic data (for audit and security purposes)</li>
                    </ul>
                </li>
            </ol>
        </section>

        <section>
            <h2>IV. Purpose of Data Collection</h2>
            <p>The information gathered by TransparaTrack is used exclusively for the following legitimate and official purposes:</p>
            <ol>
                <li>To facilitate project monitoring and reporting for transparency and governance;</li>
                <li>To enable communication and coordination among barangays and government agencies;</li>
                <li>To enable user authentication and account management;</li>
                <li>To generate data analytics, reports, and dashboards for policy and administrative decision-making;</li>
                <li>To uphold accountability and integrity in public project implementation; and</li>
                <li>To maintain system security, troubleshooting, and audit tracking.</li>
            </ol>
        </section>

        <section>
            <h2>V. Data Sharing and Disclosure</h2>
            <p>TransparaTrack shall not sell, lease, or disclose any personal or project data to unauthorized entities. However, information may be shared under the following circumstances:</p>
            <ol>
                <li>When required by law, court order, or legal process;</li>
                <li>When requested by authorized government agencies in line with official mandates;</li>
                <li>When necessary for data consolidation or auditing by duly authorized personnel;</li>
                <li>When essential for technical support or system maintenance by trusted service providers.</li>
            </ol>
            <p>Any data sharing shall be governed by confidentiality agreements and shall ensure compliance with relevant data protection standards.</p>
        </section>

        <section>
            <h2>VI. Data Retention and Disposal</h2>
            <p>Personal and project-related data shall be retained only for as long as necessary to fulfill the purposes stated in this Policy or as required by law and government recordkeeping regulations.</p>
            <p>Upon the expiration of the retention period, data shall be securely deleted, anonymized, or otherwise disposed of in a manner that prevents unauthorized recovery or misuse.</p>
        </section>

        <section>
            <h2>VII. Data Security Measures</h2>
            <p>TransparaTrack implements appropriate organizational, physical, and technical measures to protect stored data against accidental or unlawful destruction, loss, alteration, or unauthorized disclosure.</p>
            <p>These security measures include, but are not limited to:</p>
            <ul>
                <li>Encryption of sensitive information;</li>
                <li>Secure authentication and role-based access control;</li>
                <li>Regular system updates and vulnerability assessments;</li>
                <li>Audit trails and monitoring of access logs;</li>
                <li>Personnel training on data privacy and cybersecurity protocols.</li>
            </ul>
        </section>

        <section>
            <h2>VIII. User Rights</h2>
            <p>In accordance with the Data Privacy Act of 2012, all users of TransparaTrack are entitled to the following rights:</p>
            <ol>
                <li><strong>Right to be Informed</strong> – To know how their personal data is collected and processed.</li>
                <li><strong>Right to Access</strong> – To request and obtain a copy of personal information held by the system.</li>
                <li><strong>Right to Rectification</strong> – To request correction of inaccurate or outdated information.</li>
                <li><strong>Right to Erasure or Blocking</strong> – To request deletion or suspension of data no longer necessary for system operations.</li>
                <li><strong>Right to Object</strong> – To withhold consent from data processing for purposes outside official functions.</li>
                <li><strong>Right to Data Portability</strong> – To obtain copies of their personal data in a structured, commonly used format.</li>
            </ol>
            <p>Requests regarding these rights may be made through official TransparaTrack support channels.</p>
        </section>

        <section>
            <h2>IX. Use of Cookies and Tracking Technologies</h2>
            <p>TransparaTrack may use cookies and similar technologies to enhance system functionality and user experience.</p>
            <p>Cookies are small files stored on a user's device that enable the system to remember user preferences and improve navigation efficiency. Users may choose to disable cookies through their browser settings, but certain system functions may be limited as a result.</p>
        </section>

        <section>
            <h2>X. Policy Amendments</h2>
            <p>This Privacy Policy may be amended from time to time to reflect legal, technological, or operational changes.</p>
            <p>All significant modifications shall be communicated through official notices within the platform. Continued use of the system after such updates constitutes acceptance of the revised policy.</p>
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