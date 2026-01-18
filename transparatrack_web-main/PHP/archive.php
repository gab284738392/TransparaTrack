<?php
// archive.php — Archive page showing completed projects by year
include('db_connect.php');

// Check if a specific year is requested
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : null;

// If a year is selected, redirect to projects page with filters
if ($selected_year) {
    header("Location: project.php?year=" . $selected_year . "&status[]=Completed");
    exit;
}

// Ensure session is started so we can show login/avatar
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- FIX: DEFINE WEB ROOT PATH ---
// This path is necessary for the profile picture to load correctly from the web root.
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/'; 

// Build header right-side HTML (login button or admin avatar)
$headerRightHtml = '<a href="Log-in_page/login.php" class="btn-login">Log in</a>';
if (isset($_SESSION['UserID'])) {
    try {
        $stmtUser = $pdo->prepare("SELECT ProfileImagePath, FullName FROM Users WHERE UserID = :id LIMIT 1");
        $stmtUser->execute([':id' => $_SESSION['UserID']]);
        $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $profilePath = $userRow['ProfileImagePath'] ?? '';
        
        // FIX: Ensure $imgSrc uses $base_web_path
        if (!empty($profilePath)) {
            $imgSrc = $base_web_path . $profilePath; // Use the corrected base path
        } else {
            $imgSrc = '/transparatrack_web/PHP/ADMIN PROFILE/assets/profile.svg';
        }
        $headerRightHtml = '<a href="/transparatrack_web/PHP/ADMIN PROFILE/adminprofile.php" class="admin-avatar"><img src="' . htmlspecialchars($imgSrc, ENT_QUOTES) . '" alt="Admin Profile"></a>';
    } catch (\Exception $e) {
        // keep default login button on error
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive — TransparaTrack</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/tplogo.svg">
    <style>
        /* Archive specific styles */
        body {
            background-color: #EBE9E9;
        }
        .archive-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            padding: 60px 150px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .archive-card {
            background: #ffffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .archive-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .archive-year-label {
            font-family: 'Kodchasan', sans-serif;
            font-size: 16px;
            font-weight: 500;
            color: #C62828;
            margin-bottom: 8px;
        }

        .archive-year-separator {
            width: 190px;
            height: 3px;
            background: #F3B900;
            border-radius: 2px;
            margin: 8px 0 16px 0;
        }

        .archive-year {
            font-family: 'Aleo', serif;
            font-size: 64px;
            font-weight: 700;
            color: #27294A;
            margin-bottom: 24px;
            line-height: 1;
        }

        .archive-button {
            background: #C62828;
            color: #FFFFFF;
            font-family: 'Kodchasan', sans-serif;
            font-size: 15px;
            font-weight: 600;
            padding: 12px 40px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s ease, transform 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .archive-button:hover {
            background: #B71C1C;
            transform: translateY(-2px);
        }

        @media (max-width: 968px) {
            .archive-grid {
                grid-template-columns: 1fr;
                padding: 40px 80px;
                gap: 30px;
            }
        }

        @media (max-width: 568px) {
            .archive-grid {
                padding: 30px 20px;
                gap: 24px;
            }

            .archive-card {
                padding: 30px 20px;
            }

            .archive-year {
                font-size: 48px;
            }
        }
        
        /* Add margin after the grid to match the top margin */
        main {
            margin-bottom: 60px;
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

<div class="subheader-bar" role="banner">
    <div class="subheader-title">Archive</div>
</div>

<main>
    <div class="archive-grid">
        <?php
        // Get distinct years from completed projects (original behavior)
        $sql = "SELECT DISTINCT YEAR(StartDate) as year 
                FROM Projects 
                WHERE ProjectStatus = 'Completed' 
                AND StartDate IS NOT NULL
                ORDER BY year DESC";
        $stmt = $pdo->query($sql);
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Only display if there are actual completed projects
        if (!empty($years)):
            foreach ($years as $year):
                // Count completed projects for this year
                $count_sql = "SELECT COUNT(*) FROM Projects 
                             WHERE ProjectStatus = 'Completed' 
                             AND YEAR(StartDate) = ?";
                $count_stmt = $pdo->prepare($count_sql);
                $count_stmt->execute([$year]);
                $project_count = $count_stmt->fetchColumn();
        ?>
        <div class="archive-card">
            <div class="archive-year-label">Archives of</div>
            <div class="archive-year-separator"></div>
            <div class="archive-year"><?php echo htmlspecialchars($year); ?></div>
            <a href="?year=<?php echo $year; ?>" class="archive-button">
                View Completed Projects
            </a>
        </div>
        <?php endforeach; else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #666;">
            <p style="font-family: 'Kodchasan', sans-serif; font-size: 18px;">No completed projects found in the archive.</p>
        </div>
        <?php endif; ?>
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