<?php
// history.php — History page showing project action history

// 1. Setup and Connection Check
session_start(); 
require_once 'db_connect.php'; 

// Define Web Root Path for Images
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/'; 

// 2. Database Query (Optimized for Audit Logs)
$history_records = [];
$error_message = null;

// This query joins the AuditLog with Users and Projects to get real names instead of IDs
$sql = "SELECT 
            a.ActionTimestamp, 
            a.AuditAction, 
            a.UserRole AS AuditUserRole, 
            p.ProjectName, 
            p.ProjectID,
            u.FullName 
        FROM AuditLog a
        LEFT JOIN Users u ON a.PerformedBy = u.UserID
        LEFT JOIN Projects p ON a.ProjectID = p.ProjectID
        ORDER BY a.ActionTimestamp DESC
        LIMIT 50";

try {
    $stmt = $pdo->query($sql);
    if ($stmt) {
        $history_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (\PDOException $e) {
    error_log("History DB Error: " . $e->getMessage());
    $error_message = "Database connection error.";
}

// --- HEADER LOGIC (Login Button vs Admin Profile) ---
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
        // Fallback to login button
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History — TransparaTrack</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="shortcut icon" href="assets/tplogo.svg">
    <style>
    /* Internal Styles matching your design */
    body { background-color: #EBE9E9; }
    .history-container { padding: 40px 10px; margin: 60px auto; max-width: 1200px; } /* Added max-width for better look */
    .history-table { 
        width: 100%; border-collapse: collapse; margin-top: 0; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border-radius: 8px; 
        overflow: hidden; background-color: #ffffff; 
    }
    .history-table th { 
        background-color: #4149B7; color: white; text-align: left; 
        padding: 16px 20px; font-family: 'Kodchasan', sans-serif; font-weight: 600; 
    }
    .history-table td { 
        padding: 16px 20px; border-bottom: 1px solid #eee; 
        font-family: 'Kodchasan', sans-serif; color: #333;
    }
    .history-table tr:hover { background-color: #f5f5f5; }
    .project-link { color: #4149B7; text-decoration: none; font-weight: 600; }
    .project-link:hover { text-decoration: underline; color: #C62828; }
    .no-history { text-align: center; padding: 40px; color: #666; }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .history-table th, .history-table td { padding: 10px; font-size: 13px; }
        .history-container { margin: 20px auto; }
    }
    </style>
</head>
<body>

    <div class="header-bar">
        <div class="header-top">
            <div class="icon-container">
                <img src="/transparatrack_web/PHP/ADMIN PROFILE/assets/tplogo.svg" alt="TransparaTrack Logo">
            </div>
            <div class="title-wrapper">
                <span class="header-title">TransparaTrack</span>
                <span class="header-subtitle">See the process, Start the progress</span>
            </div>
            <div class="admin-icon-container">
                <?php echo $headerRightHtml; ?>
            </div>
        </div>
        <div class="gradient-line"></div>
        <nav class="nav-container">
          <ul>
            <li><a href="/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>
            <li><a href="/transparatrack_web/PHP/project.php">Projects</a></li>
            <li><a href="/transparatrack_web/PHP/archive.php">Archive</a></li>
            <li><a href="/transparatrack_web/PHP/history.php">History</a></li>
            <li><a href="/transparatrack_web/PHP/about_us.php">About Us</a></li>
          </ul>
        </nav>
    </div>

<div class="subheader-bar" role="banner">
    <div class="subheader-title">History Log</div>
</div>

<main>
    <div class="history-container">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Actor</th>
                    <th>Role</th>
                    <th>Activity Description</th>
                    <th>Timestamp</th>
                    <th>Affected Project</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($error_message): ?>
                    <tr><td colspan="5" class="no-history" style="color: red;"><?php echo $error_message; ?></td></tr>
                <?php elseif (!empty($history_records)): ?>
                    <?php foreach ($history_records as $log): ?>
                        <tr>
                            <td>
                                <?php 
                                    // If FullName exists, show it. If not, check if it was the System Automator.
                                    echo !empty($log['FullName']) ? htmlspecialchars($log['FullName']) : 'System Automator'; 
                                ?>
                            </td>

                            <td><?php echo htmlspecialchars($log['AuditUserRole'] ?? 'System'); ?></td>

                            <td><?php echo htmlspecialchars($log['AuditAction']); ?></td>

                            <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($log['ActionTimestamp']))); ?></td>

                            <td>
                                <?php if ($log['ProjectID']): ?>
                                    <a href="project.php?id=<?php echo htmlspecialchars($log['ProjectID']); ?>" class="project-link">
                                        <?php echo htmlspecialchars($log['ProjectName'] ?? 'Deleted Project'); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #999;">System Action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-history">No history records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="site-footer">
    <div class="footer-gradient-line"></div>
    <div class="footer-content-area">
        <div class="footer-column">
            <ul>
                <li><a href="/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>
                <li><a href="/transparatrack_web/PHP/project.php">Projects</a></li>
                <li><a href="/transparatrack_web/PHP/archive.php">Archive</a></li>
                <li><a href="/transparatrack_web/PHP/history.php">History</a></li>
                <li><a href="/transparatrack_web/PHP/about_us.php">About Us</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <ul>
                <li><a href="/transparatrack_web/PHP/ADMIN%20PROFILE/adminprofile.php">Profile</a></li>
                <li><a href="/transparatrack_web/PHP/Log-in_page/terms-conditions.php">Terms and Conditions</a></li>
                <li><a href="/transparatrack_web/PHP/Log-in_page/privacy-policy.php">Privacy Policy</a></li>
                <li><a href="/transparatrack_web/PHP/HELP/help.php">Help</a></li>
            </ul>
        </div>
        <div class="footer-logo">TransparaTrack</div>
    </div>
</footer>

</body>
</html>