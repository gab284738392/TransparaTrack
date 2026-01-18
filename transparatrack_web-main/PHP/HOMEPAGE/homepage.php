<?php
// Start session and connect to the database
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// FIX: Use correct relative path and PDO connection file
require_once __DIR__ . '/../ADMIN PROFILE/db_connect.php'; // Provides $pdo

// --- DEFINE WEB ROOT PATH ---
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/'; 

// --- MASTER DEPARTMENT MAPPING ---
$departmentMap = [
    'Punong Barangay' => ['label' => 'Punong Barangay', 'color' => '#d81b1bff'],
    'Sangguniang Barangay' => ['label' => 'Sangguniang Barangay', 'color' => '#cab0fcff'],
    'Barangay Development Council (BDC)' => ['label' => 'Barangay Development Council (BDC)', 'color' => '#4b8fe7ff'],
    'Barangay Peace and Order Committee (BPOC)' => ['label' => 'Barangay Peace and Order Committee (BPOC)', 'color' => '#00ACC1'],
    'Barangay Disaster Risk Reduction and Management Committee (BDRRMC)' => ['label' => 'Barangay Disaster Risk Reduction and Management Committee (BDRRMC)', 'color' => '#43A047'],
    'Barangay Anti-Drug Abuse Council (BADAC)' => ['label' => 'Barangay Anti-Drug Abuse Council (BADAC)', 'color' => '#fd35c1ff'],
    'Barangay Council for the Protection of Children (BCPC)' => ['label' => 'Barangay Council for the Protection of Children (BCPC)', 'color' => '#FF8C00'],
    'Barangay Ecological Solid Waste Management Committee (BESWMC)' => ['label' => 'Barangay Ecological Solid Waste Management Committee (BESWMC)', 'color' => '#6d1f11ff'],
    'Lupon Tagapamayapa (Barangay Justice System)' => ['label' => 'Lupon Tagapamayapa (Barangay Justice System)', 'color' => '#e9aac4ff'],
    'Barangay Health Workers (BHWs)' => ['label' => 'Barangay Health Workers (BHWs)', 'color' => '#7E22CE'],
    'Barangay Public Safety Officers (BPSO)' => ['label' => 'Barangay Public Safety Officers (BPSO)', 'color' => '#3033dfff'],
    'Committee on Health and Sanitation' => ['label' => 'Committee on Health and Sanitation', 'color' => '#0D9488'],
    'Committee on Livelihood and Cooperatives' => ['label' => 'Committee on Livelihood and Cooperatives', 'color' => '#89be3dff'],
    'Committee on Infrastructure' => ['label' => 'Committee on Infrastructure', 'color' => '#FACC15'],
    'Committee on Rules and Ordinances' => ['label' => 'Committee on Rules and Ordinances', 'color' => '#f0b280ff'],
];

// --- INITIALIZE ---
$totalProjects = 0;
$totalCost = 0;

// This array correctly includes all statuses
$statusPercentages = [
    'Completed' => 0,
    'Ongoing' => 0, 
    'Delayed' => 0,
    'Cancelled' => 0,
    'Not Started' => 0,
    'On Hold' => 0
];

$categoryData = array_fill_keys([
    'Imprastraktura at Pampublikong Gawa',
    'Kalusugan, Nutrisyon, at Serbisyo Sosyal',
    'Kapayapaan, Kaayusan, at Pampublikong Kaligtasan',
    'Paghahanda at Pagtugon sa Sakuna',
    'Pamamahala sa Kapaligiran',
    'Pangkabuhayan at Pagpapaunlad ng Ekonomiya',
    'Kabataan at Pagpapaunlad ng Sports',
    'Pamamahala at Operasyon'
], 0);
$departmentCounts = array_fill_keys(array_keys($departmentMap), 0);
$weeklyProjectCounts = ['Week 1' => 0, 'Week 2' => 0, 'Week 3' => 0, 'Week 4' => 0];
$weekPercentages = ['Week 1' => 0, 'Week 2' => 0, 'Week 3' => 0, 'Week 4' => 0];
$yAxisLabels = [10, 8, 5, 2, 0]; // Default

try {
    if ($pdo === null) {
        throw new \PDOException("Database connection object not initialized.");
    }
    
    // 1. Get Total Projects
    $totalProjects = (int) $pdo->query("SELECT COUNT(ProjectID) FROM Projects")->fetchColumn();

    // 2. Get Total Cost
    $totalCost = (float) $pdo->query("SELECT SUM(AllocatedAmount) FROM Budget")->fetchColumn();

    if ($totalProjects > 0) {
        // 3. Get Status Breakdown
        $stmt = $pdo->query("SELECT ProjectStatus, COUNT(*) AS count FROM Projects GROUP BY ProjectStatus");
        $statusResults = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($statusPercentages as $status => $value) {
            $statusPercentages[$status] = isset($statusResults[$status])
                ? round(($statusResults[$status] / $totalProjects) * 100)
                : 0;
        }

        // Build raw counts for each status so legends can show exact numbers
        $statusCounts = array_fill_keys(array_keys($statusPercentages), 0);
        foreach ($statusCounts as $s => &$c) {
            $c = isset($statusResults[$s]) ? (int)$statusResults[$s] : 0;
        }
        unset($c);

        // 4. Get Category Breakdown
        $stmt = $pdo->query("SELECT ProjectType, COUNT(*) AS count FROM Projects GROUP BY ProjectType");
        $categoryCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($categoryData as $cat => $value) {
            if (isset($categoryCounts[$cat])) {
                $categoryData[$cat] = $categoryCounts[$cat];
            }
        }

        // 5. Get Department Breakdown
        $stmt_dept = $pdo->query("
            SELECT d.DeptName, COUNT(p.ProjectID) AS count
            FROM Departments d
            LEFT JOIN ProjectDepartment pd ON pd.DeptID = d.DeptID
            LEFT JOIN Projects p ON p.ProjectID = pd.ProjectID
            GROUP BY d.DeptName
        ");
        $dbDepartmentCounts = $stmt_dept->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($departmentCounts as $dept => $value) {
            if (isset($dbDepartmentCounts[$dept])) {
                $departmentCounts[$dept] = $dbDepartmentCounts[$dept];
            }
        }
        
        // 6. Get Weekly Project Counts for This Month
        $stmt_weekly = $pdo->query("
            SELECT 
                CASE
                    WHEN DAYOFMONTH(StartDate) BETWEEN 1 AND 7 THEN 'Week 1'
                    WHEN DAYOFMONTH(StartDate) BETWEEN 8 AND 14 THEN 'Week 2'
                    WHEN DAYOFMONTH(StartDate) BETWEEN 15 AND 21 THEN 'Week 3'
                    WHEN DAYOFMONTH(StartDate) >= 22 THEN 'Week 4'
                END as week_group,
                COUNT(ProjectID) as count
            FROM Projects
            WHERE 
                MONTH(StartDate) = MONTH(CURRENT_DATE()) AND YEAR(StartDate) = YEAR(CURRENT_DATE())
            GROUP BY week_group
        ");
        
        $weeklyResults = $stmt_weekly->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($weeklyResults as $week => $count) {
            if (isset($weeklyProjectCounts[$week])) {
                $weeklyProjectCounts[$week] = (int)$count;
            }
        }
        
        $maxWeekCount = max(10, ...array_values($weeklyProjectCounts)); 
        $weekPercentages['Week 1'] = round(($weeklyProjectCounts['Week 1'] / $maxWeekCount) * 100);
        $weekPercentages['Week 2'] = round(($weeklyProjectCounts['Week 2'] / $maxWeekCount) * 100);
        $weekPercentages['Week 3'] = round(($weeklyProjectCounts['Week 3'] / $maxWeekCount) * 100);
        $weekPercentages['Week 4'] = round(($weeklyProjectCounts['Week 4'] / $maxWeekCount) * 100);
        
        $yAxisLabels = [$maxWeekCount, round($maxWeekCount * 0.75), round($maxWeekCount * 0.5), round($maxWeekCount * 0.25), 0];
    }

} catch (\PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $totalProjects = 0;
    $totalCost = 0;
}

// --- HELPER FUNCTIONS (No change) ---
function generateCategoryChartStyle($data, $totalProjects) {
    $colors = [
        'Imprastraktura at Pampublikong Gawa' => '#E53935', 
        'Kalusugan, Nutrisyon, at Serbisyo Sosyal' => '#FF8C00',
        'Kapayapaan, Kaayusan, at Pampublikong Kaligtasan' => '#FDD835',
        'Paghahanda at Pagtugon sa Sakuna' => '#43A047',
        'Pamamahala sa Kapaligiran' => '#00ACC1',
        'Pangkabuhayan at Pagpapaunlad ng Ekonomiya' => '#1E88E5',
        'Kabataan at Pagpapaunlad ng Sports' => '#5E35B1',
        'Pamamahala at Operasyon' => '#D81B60'
    ];
    if ($totalProjects == 0) return 'background-image: conic-gradient(#E0E0E0 0% 100%);';
    $gradient = "background-image: conic-gradient(";
    $cumulative = 0;
    foreach ($data as $name => $count) {
        if ($count > 0) {
            $percent = ($count / $totalProjects) * 100;
            $color = $colors[$name] ?? '#9E9E9E'; 
            $gradient .= "$color $cumulative% " . ($cumulative + $percent) . "%, ";
            $cumulative += $percent;
        }
    }
    if ($cumulative < 100) $gradient .= "#E0E0E0 $cumulative% 100%";
    $gradient = rtrim($gradient, ', ');
    $gradient .= ");";
    return $gradient;
}
function generateDepartmentChartStyle($data, $map, $totalProjects) {
    if ($totalProjects == 0) return 'background-image: conic-gradient(#E0E0E0 0% 100%);';
    $gradient = "background-image: conic-gradient(";
    $cumulative = 0;
    foreach ($map as $name => $details) {
        $count = $data[$name] ?? 0;
        if ($count > 0) {
            $percent = ($count / $totalProjects) * 100;
            $color = (is_array($details) && isset($details['color'])) ? $details['color'] : '#9E9E9E'; 
            $gradient .= "$color $cumulative% " . ($cumulative + $percent) . "%, ";
            $cumulative += $percent;
        }
    }
    if ($cumulative < 100) $gradient .= "#E0E0E0 $cumulative% 100%";
    $gradient = rtrim($gradient, ', ');
    $gradient .= ");";
    return $gradient;
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
    <title>Homepage - TransparaTrack</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="/transparatrack_web/PHP/assets/style.css">
    <link rel="shortcut icon" href="../assets/tplogo.svg">
    
    <style>
        .status-bar.onhold-bar { background-color: #7E57C2; } /* Purple */
        .legend-color.onhold-color { background-color: #7E57C2; }
        
        .status-bar.notstarted-bar { background-color: #9E9E9E; } /* Gray */
        .legend-color.notstarted-color { background-color: #9E9E9E; }
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
    
    // HERO SECTION 
    echo '<div class="hero-section">';
        echo '<div class="hero-content-wrapper">'; 
            echo '<div class="hero-text-content">';
                echo '<p class="hero-greeting">Mabuhay, Ka-Barangay!</p>';
                echo '<h1 class="hero-main-title">Bantayan ang bawat<br>proyekto sa inyong <span class="local-barangay"><br>Local Barangay!</span></h1>';
                echo '<p class="hero-subtitle">Ang pagiging bukas at malinaw sa pamamahala ay susi sa pagbuo ng tiwala at aktibong pakikilahok ng mamamayan.</p>';
            echo '</div>'; 
            
            echo '<div class="hero-callout-box">';
                echo '<p class="callout-text">Maging bahagi ng bawat proyekto—tingnan ang progreso ng ating barangay!</p>';
                echo '<a href="/transparatrack_web/PHP/project.php" class="callout-link">Go to Projects ➔</a>';
            echo '</div>'; 
            
        echo '</div>'; 
    echo '</div>'; 

    // START OF DASHBOARD SECTION
    echo '<div class="dashboard-section">';
        echo '<div class="content-container">';
            
            echo '<div class="dashboard-title-area">';
                echo '<h2>Project Dashboard</h2>';
            echo '</div>'; 
            
            echo '<div class="dashboard-grid">';
                
                // Row 1: Stat Cards
                echo '<div class="stat-card-grid">';
                    
                    echo '<div class="stat-card total-projects">';
                        echo '<p class="stat-label">Total Projects Registered</p>';
                        echo '<p class="stat-value">' . number_format($totalProjects) . '</p>';
                    echo '</div>';
                    
                    echo '<div class="stat-card total-cost">';
                        echo '<p class="stat-label">Total Cost of All Registered Projects</p>';
                        echo '<p class="stat-value">₱' . number_format($totalCost, 2) . '</p>';
                    echo '</div>';
                
                echo '</div>'; // Closes .stat-card-grid

                // Row 2: Chart Cards
                echo '<div class="chart-card-grid">';
                    
                    // --- CARD 3: PROJECTS THIS MONTH (DYNAMIC) ---
                    echo '<div class="chart-card projects-october">';
                        echo '<p class="chart-title">Projects This Month</p>'; 
                        echo '<div class="bar-chart-container">';
                            echo '<div class="y-axis-labels">';
                                echo '<span>' . $yAxisLabels[0] . '</span>';
                                echo '<span>' . $yAxisLabels[1] . '</span>';
                                echo '<span>' . $yAxisLabels[2] . '</span>';
                                echo '<span>' . $yAxisLabels[3] . '</span>';
                                echo '<span>' . $yAxisLabels[4] . '</span>';
                            echo '</div>';
                            echo '<div class="bar-area">';
                                echo '<div class="bar-group">
                                        <div class="chart-bar" style="height: ' . $weekPercentages['Week 1'] . '%;"></div>
                                        <span class="x-label">Week 1</span>
                                      </div>';
                                echo '<div class="bar-group">
                                        <div class="chart-bar" style="height: ' . $weekPercentages['Week 2'] . '%;"></div>
                                        <span class="x-label">Week 2</span>
                                      </div>';
                                echo '<div class="bar-group">
                                        <div class="chart-bar" style="height: ' . $weekPercentages['Week 3'] . '%;"></div>
                                        <span class="x-label">Week 3</span>
                                      </div>';
                                echo '<div class="bar-group">
                                        <div class="chart-bar" style="height: ' . $weekPercentages['Week 4'] . '%;"></div>
                                        <span class="x-label">Week 4</span>
                                      </div>';
                            echo '</div>'; 
                        echo '</div>'; 
                        echo '<div class="chart-legend">
                                <div class="legend-item">
                                    <span class="legend-color project-color"></span>
                                    <span class="legend-label">Projects</span>
                                </div>
                              </div>';
                    echo '</div>';
                    
                    // --- CARD 4: STATUS BREAKDOWN (FIXED) ---
                    echo '<div class="chart-card status-breakdown">';
                        echo '<p class="chart-title">Status Breakdown</p>';
                        echo '<div class="horizontal-bar-chart-container">';
                            echo '<div class="h-chart-body">';
        
                                echo '<div class="status-bar-group">
                                        <div class="status-label complete-label">Complete</div>
                                        <div class="bar-wrapper">
                                            <div class="status-bar complete-bar" style="width: ' . $statusPercentages['Completed'] . '%;"></div> 
                                        </div>
                                      </div>';
                                
                                echo '<div class="status-bar-group">
                                        <div class="status-label ongoing-label">Ongoing</div>
                                        <div class="bar-wrapper">
                                            <div class="status-bar ongoing-bar" style="width: ' . $statusPercentages['Ongoing'] . '%;"></div> 
                                        </div>
                                      </div>';
                                
                                echo '<div class="status-bar-group">
                                        <div class="status-label delayed-label">Delayed</div>
                                        <div class="bar-wrapper">
                                            <div class="status-bar delayed-bar" style="width: ' . $statusPercentages['Delayed'] . '%;"></div> 
                                        </div>
                                      </div>';
                                
                                echo '<div class="status-bar-group">
                                        <div class="status-label cancelled-label">Cancelled</div>
                                        <div class="bar-wrapper">
                                            <div class="status-bar cancelled-bar" style="width: ' . $statusPercentages['Cancelled'] . '%;"></div> 
                                        </div>
                                      </div>';
                                
                                // --- ADDED: "On Hold" and "Not Started" ---
                                echo '<div class="status-bar-group">
                                        <div class="status-label onhold-label">On Hold</div>
                                        <div class="bar-wrapper">
                                            <div class="status-bar onhold-bar" style="width: ' . $statusPercentages['On Hold'] . '%;"></div> 
                                        </div>
                                      </div>';
                                
                                echo '<div class="status-bar-group">
                                        <div class="status-label notstarted-label">Not Started</div>
                                        <div class="bar-wrapper">
                                            <div class="status-bar notstarted-bar" style="width: ' . $statusPercentages['Not Started'] . '%;"></div> 
                                        </div>
                                      </div>';
                                // --- END ADDED ---
                                
                                echo '<div class="x-axis-labels">
                                        <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
                                      </div>';
                            echo '</div>'; 
                        echo '</div>'; 
    
                        echo '<div class="chart-legend status-legend">';
                            echo '<div class="legend-item"><span class="legend-color complete-color"></span><span class="legend-label">Complete (' . ($statusCounts["Completed"] ?? 0) . ')</span></div>';
                            echo '<div class="legend-item"><span class="legend-color ongoing-color"></span><span class="legend-label">Ongoing (' . ($statusCounts["Ongoing"] ?? 0) . ')</span></div>';
                            echo '<div class="legend-item"><span class="legend-color delayed-color"></span><span class="legend-label">Delayed (' . ($statusCounts["Delayed"] ?? 0) . ')</span></div>';
                            echo '<div class="legend-item"><span class="legend-color cancelled-color"></span><span class="legend-label">Cancelled (' . ($statusCounts["Cancelled"] ?? 0) . ')</span></div>';
                            // --- ADDED: Legend items for new statuses ---
                            echo '<div class="legend-item"><span class="legend-color onhold-color"></span><span class="legend-label">On Hold (' . ($statusCounts["On Hold"] ?? 0) . ')</span></div>';
                            echo '<div class="legend-item"><span class="legend-color notstarted-color"></span><span class="legend-label">Not Started (' . ($statusCounts["Not Started"] ?? 0) . ')</span></div>';
                        echo '</div>';
                    echo '</div>';
                
                echo '</div>'; // Closes .chart-card-grid
                
                // --- LAYOUT FIX: Pie charts are now stacked vertically ---
                
                // --- CARD 5: PROJECTS BY DEPARTMENT ---
                echo '<div class="chart-card projects-department" style="margin-top: 20px;">'; 
                    echo '<p class="chart-title">Projects by Department</p>';
                    echo '<div class="pie-chart-container">';
                        
                        $dept_style = generateDepartmentChartStyle($departmentCounts, $departmentMap, $totalProjects);
                        echo '<div class="department-pie-chart" style="' . $dept_style . '"></div>'; 
                        
                        echo '<div class="department-legend">';
                            
                            foreach($departmentMap as $fullName => $details) {
                                $shortLabel = (is_array($details) && isset($details['label'])) ? $details['label'] : $fullName;
                                $count = $departmentCounts[$fullName];
                                $perc = $totalProjects > 0 ? round($count / $totalProjects * 100) : 0;
                                $color = (is_array($details) && isset($details['color'])) ? $details['color'] : '#9E9E9E';
                                
                                echo '<div class="legend-item">';
                                echo '  <span class="legend-color" style="background-color: '.$color.'"></span>';
                                echo '  <span class="legend-label">'.$shortLabel.' (' . $perc . '%)</span>';
                                echo '</div>';
                            }

                        echo '</div>'; // Closes .department-legend
                    echo '</div>'; // Closes .pie-chart-container
                echo '</div>'; // Closes .chart-card projects-department
                            
                // --- CARD 6: PROJECTS BY CATEGORY ---
                echo '<div class="chart-card projects-category" style="margin-top: 20px;">';
                     echo '<p class="chart-title">Projects by Category</p>'; 
                     echo '<div class="pie-chart-container">';
                        
                        $category_style = generateCategoryChartStyle($categoryData, $totalProjects);
                        echo '<div class="pie-chart" style="' . $category_style . '"></div>'; 
                        
                        echo '<div class="category-legend">';
                            
                            $cat_infra_perc = $totalProjects > 0 ? round($categoryData['Imprastraktura at Pampublikong Gawa'] / $totalProjects * 100) : 0;
                            $cat_health_perc = $totalProjects > 0 ? round($categoryData['Kalusugan, Nutrisyon, at Serbisyo Sosyal'] / $totalProjects * 100) : 0;
                            $cat_peace_perc = $totalProjects > 0 ? round($categoryData['Kapayapaan, Kaayusan, at Pampublikong Kaligtasan'] / $totalProjects * 100) : 0;
                            $cat_drrm_perc = $totalProjects > 0 ? round($categoryData['Paghahanda at Pagtugon sa Sakuna'] / $totalProjects * 100) : 0;
                            $cat_env_perc = $totalProjects > 0 ? round($categoryData['Pamamahala sa Kapaligiran'] / $totalProjects * 100) : 0;
                            $cat_livelihood_perc = $totalProjects > 0 ? round($categoryData['Pangkabuhayan at Pagpapaunlad ng Ekonomiya'] / $totalProjects * 100) : 0;
                            $cat_sk_perc = $totalProjects > 0 ? round($categoryData['Kabataan at Pagpapaunlad ng Sports'] / $totalProjects * 100) : 0;
                            $cat_gov_perc = $totalProjects > 0 ? round($categoryData['Pamamahala at Operasyon'] / $totalProjects * 100) : 0;

                            echo '<div class="legend-item">
                                    <span class="legend-color cat-infra-color"></span>
                                    <span class="legend-label">Imprastraktura (' . $cat_infra_perc . '%)</span>
                                  </div>';
                            echo '<div class="legend-item">
                                    <span class="legend-color cat-health-color"></span>
                                    <span class="legend-label">Kalusugan, Nutrisyon (' . $cat_health_perc . '%)</span>
                                  </div>';
                            echo '<div class="legend-item">
                                    <span class="legend-color cat-peace-color"></span>
                                    <span class="legend-label">Kapayapaan, Kaayusan (' . $cat_peace_perc . '%)</span>
                                  </div>';
                            echo '<div class="legend-item">
                                    <span class="legend-color cat-drrm-color"></span>
                                    <span class="legend-label">Paghahanda sa Sakuna (' . $cat_drrm_perc . '%)</span>
                                  </div>';
                            // --- FIX: Corrected typo class_name() to class= ---
                            echo '<div class="legend-item">
                                    <span class="legend-color cat-env-color"></span>
                                    <span class="legend-label">Pamamahala sa Kapaligiran (' . $cat_env_perc . '%)</span>
                                  </div>';
                            echo '<div class="legend-item">
                                    <span class="legend-color cat-livelihood-color"></span>
                                    <span class="legend-label">Pangkabuhayan (' . $cat_livelihood_perc . '%)</span>
                                  </div>';
                            echo '<div class="legend-item">
                                    <span class="legend-color cat-sk-color"></span>
                                    <span class="legend-label">Kabataan at Sports (' . $cat_sk_perc . '%)</span>
                                  </div>';
                            echo '<div class="legend-item">
                                    <span class="legend-color cat-gov-color"></span>
                                    <span class="legend-label">Pamamahala (' . $cat_gov_perc . '%)</span>
                                  </div>';

                        echo '</div>'; 
                     echo '</div>'; 
                echo '</div>'; 
                
            echo '</div>'; // Closes .dashboard-grid
        echo '</div>'; // Closes .content-container
    echo '</div>'; // Closes .dashboard-section

    // FOOTER
    echo '<footer class="site-footer">';
        echo '<div class="footer-gradient-line"></div>';
        echo '<div class="footer-content-area">';
            echo '<div class="footer-column"><ul>';
                echo '  <li><a href="/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>';
                echo '  <li><a href="/transparatrack_web/PHP/project.php">Projects</a></li>';
                echo '  <li><a href="/transparatrack_web/PHP/archive.php">Archive</a></li>';
                echo '  <li><a href="/transparatrack_web/PHP/history.php">History</a></li>';
                echo '  <li><a href="/transparatrack_web/PHP/about_us.php">About Us</a></li>';
            echo '</ul></div>'; 
            
            echo '<div class="footer-column"><ul>';
                echo '  <li><a href="/transparatrack_web/PHP/ADMIN PROFILE/adminprofile.php">Profile</a></li>';
                echo '  <li><a href="/transparatrack_web/PHP/Log-in_page/terms-conditions.php">Terms and Conditions</a></li>';
                echo '  <li><a href="/transparatrack_web/PHP/Log-in_page/privacy-policy.php">Privacy Policy</a></li>';
                echo '  <li><a href="http://localhost/transparatrack_web/PHP/HELP/help.php">Help</a></li>';
            echo '</ul></div>'; 

            echo '<div class="footer-logo">TransparaTrack</div>';
        echo '</div>'; 
    echo '</footer>';
    ?>

</body>
</html>