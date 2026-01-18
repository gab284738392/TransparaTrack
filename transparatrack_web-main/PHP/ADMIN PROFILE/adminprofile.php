<?php
// Start the session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: ../Log-in_page/login.php"); 
    exit;
}

// Database connection (provides $pdo object)
include 'db_connect.php'; 

// --- CRITICAL PATH: Define the base path for relative assets ---
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/'; 



// --- Get Logged-in User Data from Session ---
$loggedInUserID = $_SESSION['UserID'];
$loggedInFullName = htmlspecialchars($_SESSION['FullName'] ?? 'N/A');
$loggedInUserRole = htmlspecialchars($_SESSION['UserRole'] ?? 'N/A');

// Fetch the user's email and username from the database using their ID
try {
    if ($pdo === null) {
        throw new \PDOException("Database connection object not initialized.");
    }
    
    $stmt = $pdo->prepare("SELECT Username, Email, ContactNum, ProfileImagePath FROM Users WHERE UserID = :id");
    $stmt->execute([':id' => $loggedInUserID]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    $loggedInUsername = htmlspecialchars($userData['Username'] ?? 'N/A');
    $loggedInEmail = htmlspecialchars($userData['Email'] ?? 'N/A');
    $loggedInContactNum = htmlspecialchars($userData['ContactNum'] ?? 'N/A');
    
    // Set the profile image path
    $profileImagePath = 'assets/adminpic.svg'; // Default
    if (!empty($userData['ProfileImagePath'])) {
        $profileImagePath = $userData['ProfileImagePath'];
    }

} catch (\PDOException $e) {
    error_log("User Data Fetch Error in adminprofile: " . $e->getMessage());
    $loggedInUsername = 'Error';
    $loggedInEmail = 'Error';
    $loggedInContactNum = 'Error';
    $profileImagePath = 'assets/adminpic.svg'; // Fallback
}

// --- HEADER: decide whether to show login button or admin avatar ---
$headerRightHtml = '<a href="/transparatrack_web/PHP/Log-in_page/login.php" class="btn-login">Log in</a>';
if (isset($_SESSION['UserID'])) {
    try {
        $stmtUser = $pdo->prepare("SELECT ProfileImagePath, FullName FROM Users WHERE UserID = :id LIMIT 1");
        $stmtUser->execute([':id' => $_SESSION['UserID']]);
        $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $profilePath = $userRow['ProfileImagePath'] ?? '';

        if (!empty($profilePath)) {
            $imgSrc = $base_web_path . $profilePath; // Use base path
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
    <title>Admin Profile - TransparaTrack</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="m_style.css">
    <link rel="shortcut icon" href="assets/tplogo.svg">
    
    <link href="https://fonts.googleapis.com/css2?family=Kodchasan:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<?php
// HEADER - Use relative paths for all asset loading
$homepage_full_url = 'http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php#';

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

echo '<div class="subheader-bar">';
    echo '<span class="subheader-title">Profile</span>';
echo '</div>';

// MAIN CONTENT
echo '<main class="main-content">';

// --- DYNAMIC USER CARD ---
echo '<div class="user-details-card">';
    echo '<div class="card-top-row">';
        echo '<div class="profile-pic-container">';
            echo '<img src="' . $profileImagePath . '" alt="Profile Picture" style="width:100%; height:100%; object-fit:cover; border-radius: 50%;">';
        echo '</div>';
        echo '<div class="user-info-wrapper">';
            echo '<span class="user-name">' . $loggedInFullName . '</span>'; 
            echo '<span class="user-role">' . $loggedInUserRole . '</span>'; 
            echo '<div class="user-contact-details">';
                echo '<div class="detail-row"><img src="assets/user.svg" alt="Username"><span>' . $loggedInUsername . '</span></div>'; 
                echo '<div class="detail-row"><img src="assets/message.svg" alt="Email"><span>' . $loggedInEmail . '</span></div>'; 
            echo '</div>';
        echo '</div>';
        echo '<div class="profile-button-container">';
            echo '<a href= "../Log-in_page/logout.php" class="logout-profile-button">Log out</a>';
            echo '<a href="#" id="openProfileModalBtn" class="edit-profile-button">Edit</a>';
        echo '</div>';
    echo '</div>';
    echo '<div class="info-gradient-line"></div>';
echo '</div>';

// ADD PROJECT
echo '<div class="add-project-card">';
    echo '<span class="card-title">Add New Project</span>';
    echo '<button id="openAddProjectModalBtn" class="create-project-button"><span>Click here to Create</span></button>';
echo '</div>';

// PROJECT LIST
echo '<div class="edit-project-card">';
    echo '<span class="card-title">My Projects</span>';

$query = "SELECT 
             p.ProjectID, 
             p.ProjectName, 
             p.Description, 
             p.ProjectType, 
             p.ProjectStatus, 
             p.StartDate, 
             p.EndDate,
             b.AllocatedAmount,
             d.DeptName,
             e.FilePath AS ImagePath
          FROM Projects p
          LEFT JOIN Budget b ON p.ProjectID = b.ProjectID
          LEFT JOIN ProjectDepartment pd ON p.ProjectID = pd.ProjectID
          LEFT JOIN Departments d ON pd.DeptID = d.DeptID
          LEFT JOIN Evidence e ON e.ProjectID = p.ProjectID AND e.EvidenceCategory = 'Photo'
          WHERE p.ProjectManagerID = :manager_id 
          GROUP BY p.ProjectID
          ORDER BY p.ProjectID DESC";

$projects = []; 
$errorMessage = '';

try {
    if ($pdo === null) {
         throw new \PDOException("Database connection is not active.");
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':manager_id', $loggedInUserID, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC); 

} catch (\PDOException $e) {
    error_log("Project List Fetch Error: " . $e->getMessage());
    $errorMessage = 'Error fetching projects: Database Query Failed.';
}

if ($errorMessage) {
     echo '<p style="text-align:center; color:#e53935;">' . $errorMessage . '</p>';
}

if (!empty($projects)) {
    foreach ($projects as $row) { 
        $projectID = htmlspecialchars($row['ProjectID']);
        $projectName = htmlspecialchars($row['ProjectName']);
        $projectStatus = htmlspecialchars($row['ProjectStatus'] ?: 'N/A');
        $projectType = htmlspecialchars($row['ProjectType'] ?: 'N/A');
        $projectStart = htmlspecialchars($row['StartDate'] ?: 'N/A');
        $projectEnd = htmlspecialchars($row['EndDate'] ?: 'N/A');
        $budget = isset($row['AllocatedAmount']) ? '₱' . number_format($row['AllocatedAmount'], 2) : 'N/A';
        $department = htmlspecialchars($row['DeptName'] ?: 'Unassigned');
        
        $imagePathDB = $row['ImagePath'];
        $defaultImagePath = 'assets/default_project.jpg';

        if (!empty($imagePathDB)) {
            $imagePath = $imagePathDB; 
        } else {
            $imagePath = $defaultImagePath; 
        }

        echo '<div class="project-row">';
            echo '<div class="project-image-placeholder">';
                echo '<img src="' . $imagePath . '" alt="Project Image" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">';
            echo '</div>';
            echo '<div class="project-info">';
                echo '<span class="project-title">'.$projectName.'</span>';
                echo '<div class="project-divider-line"></div>';
                echo '<ul class="project-details-list">';
                    echo '<li><img src="assets/date.svg" alt="Date"><span>'.$projectStart.' - '.$projectEnd.'</span></li>';
                    echo '<li><img src="assets/status.svg" alt="Status"><span>'.$projectStatus.'</span></li>';
                    echo '<li><img src="assets/budget.svg" alt="Budget"><span>'.$budget.'</span></li>';
                    echo '<li><img src="assets/category.svg" alt="Category"><span>'.$projectType.'</span></li>';
                    
                    echo '<li><img src="assets/department.svg" alt="Department"><span>'.$department.'</span></li>';
                    // --- END OF FIX ---
                    
                    echo '<li><img src="assets/author.svg" alt="Author"><span>'.$loggedInFullName.'</span></li>';
                echo '</ul>';
            echo '</div>';
            echo '<button class="view-project-button openEditProjectBtn" data-project-id="'.$projectID.'" data-project-title="'.$projectName.'">Edit Project</button>';
        echo '</div>';
    }
} else {
    echo '<p style="text-align:center; color:#666;">No projects have been assigned to you yet.</p>';
}

echo '</div>'; 
echo '</main>';

// FOOTER
echo '<footer class="site-footer">';
    echo '<div class="footer-gradient-line"></div>';
    echo '<div class="footer-content-area">';
        echo '<div class="footer-column">';
            echo '<ul>';
                echo '<li><a href="'.$homepage_full_url.'">Home</a></li>';
                echo '<li><a href="../project.php">Projects</a></li>';
                echo '<li><a href="../archive.php">Archive</a></li>';
                echo '<li><a href="../history.php">History</a></li>';
                echo '<li><a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a></li>';
                
            echo '</ul>';
        echo '</div>';

        echo '<div class="footer-column">';
            echo '<ul>';
                echo '<li><a href="http://localhost/transparatrack_web/PHP/ADMIN%20PROFILE/adminprofile.php">Profile</a></li>';
                echo '<li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/terms-conditions.php">Terms and Conditions</a></li>';
                echo '<li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/privacy-policy.php">Privacy Policy</a></li>';
               
                echo '<li><a href="http://localhost/transparatrack_web/PHP/HELP/help.php">Help</a></li>';
            echo '</ul>';
        echo '</div>';

        echo '<div class="footer-logo">TransparaTrack</div>';
    echo '</div>';
echo '</footer>';

include 'm_profileedit.php'; 
include 'm_addproject.php';
include 'm_editproject.php';
include 'm_dialogs.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addProjectModal = document.getElementById('addProjectModal');
    const openAddProjectBtn = document.getElementById('openAddProjectModalBtn');
    
    if (openAddProjectBtn && addProjectModal) {
        openAddProjectBtn.addEventListener('click', function() {
            addProjectModal.style.display = 'flex';
        });
    }

    const profileModal = document.getElementById('profileEditModal');
    const openProfileBtn = document.getElementById('openProfileModalBtn');
    const closeProfileBtn = document.getElementById('profile-show-cancel-dialog-x');
    const cancelProfileBtn = document.getElementById('profile-show-cancel-dialog');
    const saveProfileBtn = document.getElementById('profile-show-save-dialog');
    
    if (openProfileBtn && profileModal) {
        openProfileBtn.addEventListener('click', function(e) { e.preventDefault(); profileModal.style.display = 'flex'; });
    }
    closeProfileBtn?.addEventListener('click', function() { profileModal.style.display = 'none'; });
    cancelProfileBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        if (window.showCancelDialog) {
            window.showCancelDialog(function(){ profileModal.style.display = 'none'; });
        } else {
            profileModal.style.display = 'none';
        }
    });
    saveProfileBtn?.addEventListener('click', function (e) {
        e.preventDefault();
        const form = document.getElementById('profileEditForm'); 
        if (window.showSaveDialog) {
            window.showSaveDialog(function(){ if(form) form.submit(); });
        } else {
            if (confirm('Save profile changes?')) form?.submit();
        }
    });

    const editProjectModal = document.getElementById('editProjectModal');
    const editProjectBtns = document.querySelectorAll('.openEditProjectBtn');
    const closeEditProjectBtn = document.getElementById('show-cancel-dialog-x');
    const cancelEditProjectBtn = document.getElementById('show-cancel-dialog');
    const hiddenProjectIdInput = document.getElementById('edit_project_id');
    
    function setFieldValue(id, value) {
        const el = document.getElementById(id);
        if (el) {
            el.value = value || ''; 
        }
    }

    editProjectBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const projectId = this.getAttribute('data-project-id');
            
            if (hiddenProjectIdInput) {
                hiddenProjectIdInput.value = projectId;
            } else {
                console.error("Could not find hidden input #edit_project_id");
                return;
            }
            
            try {
                const response = await fetch(`fetch_project_data.php?id=${projectId}`);
                const result = await response.json();

                if (result.status === 'success' && result.data) {
                    const data = result.data;
                    setFieldValue('edit_project_title', data.ProjectName);
                    setFieldValue('edit_status', data.ProjectStatus);
                    setFieldValue('edit_budget', data.Budget);
                    setFieldValue('edit_start_date', data.StartDate);
                    setFieldValue('edit_end_date', data.EndDate);
                    setFieldValue('edit_category', data.ProjectType);
                    setFieldValue('edit_department', data.Department);
                    setFieldValue('edit_description', data.Description);
                    
                    editProjectModal.style.display = 'flex';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('An error occurred while fetching project details.');
            }
        });
    });

    closeEditProjectBtn?.addEventListener('click', function() {
        editProjectModal.style.display = 'none';
    });
    cancelEditProjectBtn?.addEventListener('click', function() {
        editProjectModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === addProjectModal) {
            addProjectModal.style.display = 'none';
        }
        if (event.target === profileModal) {
            profileModal.style.display = 'none';
        }
        if (event.target === editProjectModal) {
            editProjectModal.style.display = 'none';
        }
    });
});
</script>

</body>
</html>