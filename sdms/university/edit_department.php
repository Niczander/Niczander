<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include 'db.php';

// Check if department_id is provided
if (!isset($_GET['department_id'])) {
    header("Location: add_department.php");
    exit();
}

$department_id = intval($_GET['department_id']);

// Get current department data using prepared statement
$query = "SELECT * FROM department_data WHERE department_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $department_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: add_department.php");
    exit();
}

$department = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $department_name = trim($_POST['department_name'] ?? '');
    
    if (empty($department_name)) {
        header("Location: edit_department.php?department_id=" . $department_id . "&error=1&message=" . urlencode("Department name is required"));
        exit();
    }

    // Update using prepared statement
    $query = "UPDATE department_data SET department_name = ? WHERE department_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        header("Location: edit_department.php?department_id=" . $department_id . "&error=1&message=" . urlencode(mysqli_error($conn)));
        exit();
    }

    mysqli_stmt_bind_param($stmt, "si", $department_name, $department_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Get all departments after update
        $query = "SELECT * FROM department_data ORDER BY department_name";
        $result = mysqli_query($conn, $query);
        $departments = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Store departments in session
        $_SESSION['departments'] = $departments;
        
        header("Location: add_department.php?updated=1");
        exit();
    } else {
        header("Location: edit_department.php?department_id=" . $department_id . "&error=1&message=" . urlencode(mysqli_stmt_error($stmt)));
        exit();
    }
}

// Handle success and error messages
$success = null;
$error = null;
$error_message = null;

if (isset($_GET['error'])) {
    $error = true;
    $error_message = isset($_GET['message']) ? urldecode($_GET['message']) : "An error occurred";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System - Edit Department</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .dashboard-container {
            padding: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .card-title {
            margin-bottom: 0;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body style="display: flex;">
    <?php include 'sidebar.php'; ?>
    <div class="main-content" style="flex-grow: 1; padding: 20px; margin-left: 280px;">
        <h1 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Department</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Edit Department Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Department Information</h5>
            </div>
            <div class="card-body">
                <form action="edit_department.php?department_id=<?php echo $department_id; ?>" method="post">
                    <div class="mb-3">
                        <label for="department_name" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="department_name" name="department_name" 
                               value="<?php echo htmlspecialchars($department['department_name']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Department</button>
                </form>
            </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
