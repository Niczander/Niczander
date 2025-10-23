<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if program_id is provided in URL
if (!isset($_GET['program_id'])) {
    header("Location: add_program.php");
    exit();
}

$program_id = intval($_GET['program_id']);

// Get current program data using prepared statement
$query = "SELECT p.*, d.department_name FROM program_data p 
          LEFT JOIN department_data d ON p.department_id = d.department_id 
          WHERE p.program_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $program_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: add_program.php");
    exit();
}

$program = mysqli_fetch_assoc($result);

// Get all departments for dropdown
$query = "SELECT * FROM department_data ORDER BY department_name";
$result = mysqli_query($conn, $query);
$departments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $program_name = trim($_POST['program_name'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    
    if (empty($program_name)) {
        $error = "Program name is required.";
    } elseif ($department_id == 0) {
        $error = "Department must be selected.";
    } else {
        // Update using prepared statement
        $query = "UPDATE program_data SET program_name = ?, department_id = ? WHERE program_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            $error = "Error preparing update statement: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "sii", $program_name, $department_id, $program_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Get all programs after update
                $query = "SELECT p.*, d.department_name FROM program_data p 
                          LEFT JOIN department_data d ON p.department_id = d.department_id 
                          ORDER BY p.program_name";
                $result = mysqli_query($conn, $query);
                $programs = mysqli_fetch_all($result, MYSQLI_ASSOC);
                
                // Store programs in session
                $_SESSION['programs'] = $programs;
                
                header("Location: add_program.php?updated=1");
                exit();
            } else {
                $error = "Error updating program: " . mysqli_stmt_error($stmt);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System - Edit Program</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { display: flex; }
        .main-content { flex-grow: 1; padding: 20px; margin-left: 280px; }
        .card { border: none; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
        .btn-primary { width: 100%; padding: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h1 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Program</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Program Information</h5>
            </div>
            <div class="card-body">
                <form action="edit_program.php?program_id=<?php echo $program_id; ?>" method="post">
                    <div class="mb-3">
                        <label for="program_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="program_name" name="program_name" value="<?php echo htmlspecialchars($program['program_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="0">Select a department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo htmlspecialchars($department['department_id']); ?>"
                                        <?php echo ($department['department_id'] == $program['department_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($department['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Program</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
