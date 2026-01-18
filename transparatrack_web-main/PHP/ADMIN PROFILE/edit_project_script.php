<?php
// edit_project_script.php - FINAL FIXED VERSION (With File Uploads)

// 1. Start Session and Database Connection
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php'; 

// Check Authorization
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION['UserID'])) {
    $_SESSION['error_message'] = "Unauthorized access or session expired. Please log in.";
    header("Location: login.php"); 
    exit;
}

$uploaded_by_user_id = $_SESSION['UserID']; 
$user_role = $_SESSION['UserRole'] ?? 'Staff';

// --- Configuration for File Uploads ---
$upload_dir = __DIR__ . '/uploads/'; 
$photo_dir = $upload_dir . 'photos/';
$attachment_dir = $upload_dir . 'attachments/';

if (!is_dir($photo_dir)) mkdir($photo_dir, 0777, true);
if (!is_dir($attachment_dir)) mkdir($attachment_dir, 0777, true);

// 2. Sanitize and Validate Inputs
$project_id     = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_NUMBER_INT);
$title          = filter_input(INPUT_POST, 'project_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$status         = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$start_date     = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$end_date       = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$budget         = filter_input(INPUT_POST, 'budget', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$description    = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$department     = filter_input(INPUT_POST, 'department', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$category       = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$project_id) {
    $_SESSION['error_message'] = "Error: Project ID was missing.";
    header("Location: adminprofile.php"); 
    exit;
}

// 3. Database Transaction
$transaction_active = false; 

try {
    $pdo->beginTransaction();
    $transaction_active = true;

    // --- PASS CONTEXT TO SMART TRIGGERS ---
    $stmt_context = $pdo->prepare("SET @current_user_id = :uid, @current_user_role = :role");
    $stmt_context->execute([':uid' => $uploaded_by_user_id, ':role' => $user_role]);
    // --------------------------------------------------

    // A. Update Projects Table
    $sql_project = "UPDATE Projects 
                    SET ProjectName = :name, 
                        ProjectStatus = :status, 
                        Description = :description, 
                        ProjectType = :category, 
                        StartDate = :start_date, 
                        EndDate = :end_date 
                    WHERE ProjectID = :id";
    
    $stmt_project = $pdo->prepare($sql_project);
    $stmt_project->execute([
        ':name' => $title, 
        ':status' => $status, 
        ':description' => $description, 
        ':category' => $category,
        ':start_date' => $start_date ?: null, 
        ':end_date' => $end_date ?: null, 
        ':id' => $project_id
    ]);
    
    // B. Update Budget Table
    if ($budget !== false && $budget !== null) {
        $fiscal_year = date("Y", strtotime($start_date));
        $sql_budget = "INSERT INTO Budget (ProjectID, AllocatedAmount, FiscalYear) 
                       VALUES (:id, :budget, :year) 
                       ON DUPLICATE KEY UPDATE 
                       AllocatedAmount = VALUES(AllocatedAmount), 
                       FiscalYear = VALUES(FiscalYear)";
        $stmt_budget = $pdo->prepare($sql_budget);
        $stmt_budget->execute([':id' => $project_id, ':budget' => $budget, ':year' => $fiscal_year]);
    }

    // C. Update Department
    if (!empty($department)) {
        $stmt_dept = $pdo->prepare("SELECT DeptID FROM Departments WHERE DeptName = :name");
        $stmt_dept->execute([':name' => $department]);
        $dept_id = $stmt_dept->fetchColumn();

        if (!$dept_id) {
            $stmt_new_dept = $pdo->prepare("INSERT INTO Departments (DeptName) VALUES (:name)");
            $stmt_new_dept->execute([':name' => $department]);
            $dept_id = $pdo->lastInsertId();
        }

        $sql_pd = "INSERT INTO ProjectDepartment (ProjectID, DeptID, Role) 
                   VALUES (:pid, :did, 'Lead') 
                   ON DUPLICATE KEY UPDATE DeptID = VALUES(DeptID)";
        $stmt_pd = $pdo->prepare($sql_pd);
        $stmt_pd->execute([':pid' => $project_id, ':did' => $dept_id]);
    }

    // D. Handle File Uploads (THIS WAS MISSING BEFORE)
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
                ':uploaded_by' => $uploaded_by_user_id
            ];
        } else {
            error_log("Failed to move uploaded file: " . $file['name']);
        }
    }

    // Commit changes
    $pdo->commit();
    $transaction_active = false; 

    // E. Insert Evidence Records (Safely after commit)
    if (!empty($uploaded_evidence)) {
        $sql_evidence = "INSERT INTO Evidence (ProjectID, FilePath, FileType, FileSize, EvidenceCategory, UploadedBy)
                         VALUES (:project_id, :file_path, :file_type, :file_size, :category, :uploaded_by)";
        $stmt_evidence = $pdo->prepare($sql_evidence);
        foreach ($uploaded_evidence as $data) {
            $stmt_evidence->execute($data);
        }
    }

    // 4. Success Redirect
    $_SESSION['success_message'] = "Project **" . htmlspecialchars($title) . "** successfully updated!";
    header("Location: adminprofile.php"); 
    exit;

} catch (\PDOException $e) {
    if ($transaction_active) {
        $pdo->rollBack(); 
    }
    error_log("Project Update Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error updating project: " . $e->getMessage();
    header("Location: adminprofile.php"); 
    exit;
}
?>