<?php
// fetch_project_data.php

header('Content-Type: application/json');
session_start();
include 'db_connect.php'; // Provides $pdo

// Check if user is logged in and a project ID is provided
if (!isset($_SESSION['UserID']) || !isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authorized or no ID provided.']);
    exit;
}

$project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$user_id = $_SESSION['UserID'];

try {
    // Query to get all project details, including budget and department
    $query = "SELECT 
                 p.ProjectName, 
                 p.Description, 
                 p.ProjectType, 
                 p.ProjectStatus, 
                 p.StartDate, 
                 p.EndDate,
                 b.AllocatedAmount AS Budget,
                 d.DeptName AS Department
              FROM Projects p
              LEFT JOIN Budget b ON p.ProjectID = b.ProjectID
              LEFT JOIN ProjectDepartment pd ON p.ProjectID = pd.ProjectID
              LEFT JOIN Departments d ON pd.DeptID = d.DeptID
              WHERE p.ProjectID = :project_id 
                AND p.ProjectManagerID = :manager_id
              LIMIT 1"; // Ensure we only get one row

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':project_id' => $project_id,
        ':manager_id' => $user_id // Security check: user can only fetch their own projects
    ]);
    
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($project) {
        echo json_encode(['status' => 'success', 'data' => $project]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Project not found or you do not have permission.']);
    }

} catch (\PDOException $e) {
    error_log("Fetch Project Data Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred.']);
}
?>