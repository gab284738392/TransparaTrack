<?php
// add_project_script.php - FINAL FIXED VERSION WITH SMART TRIGGERS

// 1. Start Session and Database Connection
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php'; 

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    $_SESSION['error_message'] = "You must be logged in to create projects.";
    header("Location: ../Log-in_page/login.php"); 
    exit;
}

// Set the current user ID
$project_manager_id = $_SESSION['UserID']; 
$current_user_role = $_SESSION['UserRole'] ?? 'Staff'; // Default to Staff if missing

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: adminprofile.php"); 
    exit;
}

// --- Configuration for File Uploads ---
$upload_dir = __DIR__ . '/uploads/'; 
$photo_dir = $upload_dir . 'photos/';
$attachment_dir = $upload_dir . 'attachments/';

if (!is_dir($photo_dir)) mkdir($photo_dir, 0777, true);
if (!is_dir($attachment_dir)) mkdir($attachment_dir, 0777, true);

// 2. Sanitize and Validate Inputs
$title = filter_input(INPUT_POST, 'project_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$allowedStatuses = ['Not Started', 'Ongoing', 'Delayed', 'Completed', 'On Hold', 'Cancelled'];
$status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!in_array($status, $allowedStatuses)) {
    $status = 'Not Started'; 
}

$budget = filter_input(INPUT_POST, 'budget', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS); 
$dept_name = filter_input(INPUT_POST, 'department', FILTER_SANITIZE_FULL_SPECIAL_CHARS); 
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$title || !$start_date) {
    $_SESSION['error_message'] = "Error: Project Title and Start Date are required.";
    header("Location: adminprofile.php"); 
    exit;
}

// 3. Begin Transaction
$project_id = null;
$transaction_active = false;

try {
    $pdo->beginTransaction();
    $transaction_active = true;
    
    // --- [IMPORTANT FIX] PASS CONTEXT TO SMART TRIGGERS ---
    // This tells the Database Trigger who is logged in right now.
    $stmt_context = $pdo->prepare("SET @current_user_id = :uid, @current_user_role = :role");
    $stmt_context->execute([':uid' => $project_manager_id, ':role' => $current_user_role]);
    // -------------------------------------------------------

    // --- A. Find/Insert Department ---
    $stmt_dept = $pdo->prepare("SELECT DeptID FROM Departments WHERE DeptName = :name");
    $stmt_dept->execute([':name' => $dept_name]);
    $dept_id = $stmt_dept->fetchColumn();

    if (!$dept_id && !empty($dept_name)) {
        $stmt_insert_dept = $pdo->prepare("INSERT INTO Departments (DeptName) VALUES (:name)");
        $stmt_insert_dept->execute([':name' => $dept_name]);
        $dept_id = $pdo->lastInsertId();
    }

    // --- B. Insert into Projects Table ---
    // The Smart Trigger will catch this INSERT and log it automatically using the context above.
    $sql_project = "INSERT INTO Projects (ProjectName, Description, ProjectType, StartDate, EndDate, ProjectStatus, ProjectManagerID)
                    VALUES (:name, :desc, :type, :start_date, :end_date, :status, :manager_id)";
    
    $stmt_project = $pdo->prepare($sql_project);
    $stmt_project->execute([
        ':name' => $title,
        ':desc' => $description,
        ':type' => $category,
        ':start_date' => $start_date,
        ':end_date' => $end_date ?: null,
        ':status' => $status,
        ':manager_id' => $project_manager_id 
    ]);
    
    $project_id = $pdo->lastInsertId();

    // --- C. Insert into ProjectDepartment Table ---
    if ($project_id && $dept_id) {
        $sql_pd = "INSERT INTO ProjectDepartment (ProjectID, DeptID, Role) VALUES (:project_id, :dept_id, 'Lead')";
        $stmt_pd = $pdo->prepare($sql_pd);
        $stmt_pd->execute([':project_id' => $project_id, ':dept_id' => $dept_id]);
    }

    // --- D. Insert into Budget Table ---
    if ($project_id && $budget !== false && $budget >= 0) {
        $year = date("Y", strtotime($start_date));
        $sql_budget = "INSERT INTO Budget (ProjectID, AllocatedAmount, FiscalYear) 
                       VALUES (:id, :budget, :year)";
        $stmt_budget = $pdo->prepare($sql_budget);
        $stmt_budget->execute([
            ':id' => $project_id, 
            ':budget' => $budget, 
            ':year' => $year
        ]);
    }
    
    // --- E. Handle File Uploads ---
    function get_files($file_array, $category) {
        $files = []; 
        if (isset($file_array['name']) && is_array($file_array['name'])) {
            foreach ($file_array['name'] as $i => $name) {
                if ($file_array['error'][$i] === UPLOAD_ERR_OK) {
                    $files[] = [
                        'name' => $name,
                        'type' => $file_array['type'][$i],
                        'tmp_name' => $file_array['tmp_name'][$i],
                        'size' => $file_array['size'][$i],
                        'category' => $category
                    ];
                }
            }
        }
        return $files; 
    }

    $files_to_process = array_merge(
        get_files($_FILES['photos'] ?? [], 'Photo'),
        get_files($_FILES['attachments'] ?? [], 'Attachment')
    );

    $uploaded_evidence = [];

    foreach ($files_to_process as $file) {
        $is_photo = ($file['category'] === 'Photo');
        $target_dir = $is_photo ? $photo_dir : $attachment_dir;
        $db_path_folder = $is_photo ? 'uploads/photos/' : 'uploads/attachments/'; 
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_name = uniqid($project_id . '_') . '.' . $ext; 
        $target_file_path = $target_dir . $unique_name;

        if (move_uploaded_file($file['tmp_name'], $target_file_path)) {
            $uploaded_evidence[] = [
                ':project_id' => $project_id,
                ':file_path' => $db_path_folder . $unique_name,
                ':file_type' => $file['type'],
                ':file_size' => $file['size'],
                ':category' => $file['category'],
                ':uploaded_by' => $project_manager_id
            ];
        } else {
            error_log("Failed to move uploaded file: " . $file['name']);
        }
    }
    
    // Final Commit
    $pdo->commit(); 
    $transaction_active = false;
    
    // --- F. Insert Evidence Records ---
    if (!empty($uploaded_evidence)) {
        $sql_evidence = "INSERT INTO Evidence (ProjectID, FilePath, FileType, FileSize, EvidenceCategory, UploadedBy)
                         VALUES (:project_id, :file_path, :file_type, :file_size, :category, :uploaded_by)";
        $stmt_evidence = $pdo->prepare($sql_evidence);
        foreach ($uploaded_evidence as $data) {
            $stmt_evidence->execute($data);
        }
    }

    // --- [REMOVED] MANUAL AUDIT LOG INSERT ---
    // We deleted the lines that manually inserted into AuditLog.
    // The Database Trigger handled it at Step B.

    // 4. Success Redirect
    $_SESSION['success_message'] = "Project **" . htmlspecialchars($title) . "** successfully created!";
    header("Location: adminprofile.php"); 
    exit;

} catch (\Throwable $e) { 
    if ($transaction_active) {
        $pdo->rollBack(); 
    }
    error_log("Project Insert Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: adminprofile.php"); 
    exit;
}
?>