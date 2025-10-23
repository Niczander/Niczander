<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// Get student ID from URL
$students_id = intval($_GET['students_id'] ?? 0);

// Validate student ID
if ($students_id <= 0) {
    header("Location: home.php");
    exit();
}

// Get student data with program and department information
$query = "SELECT s.*, p.program_name, d.department_name 
          FROM students_data s 
          LEFT JOIN program_data p ON s.program_id = p.program_id 
          LEFT JOIN department_data d ON p.department_id = d.department_id 
          WHERE s.students_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $students_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: home.php");
    exit();
}

$student = mysqli_fetch_assoc($result);

// Get all programs for dropdown
$query = "SELECT p.*, d.department_name 
          FROM program_data p 
          LEFT JOIN department_data d ON p.department_id = d.department_id 
          ORDER BY p.program_name";
$result = mysqli_query($conn, $query);
$programs = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $program_id = intval($_POST['program_id'] ?? 0);

    // Validate input
    if (empty($name)) {
        $error = "Name is required.";
    } elseif (empty($email)) {
        $error = "Email is required.";
    } elseif (empty($phone)) {
        $error = "Phone is required.";
    } elseif ($program_id <= 0) {
        $error = "Program must be selected.";
    } else {
        // Update using prepared statement
        $query = "UPDATE students_data SET name = ?, email = ?, phone = ?, program_id = ? WHERE students_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            $error = "Error preparing update statement: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $program_id, $students_id);
            
            if (mysqli_stmt_execute($stmt)) {
                header("Location: home.php?updated=1");
                exit();
            } else {
                $error = "Error updating student: " . mysqli_stmt_error($stmt);
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
    <title>University Management System - Edit Student</title>
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
        <h1 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Student</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Student Information</h5>
            </div>
            <div class="card-body">
                <form action="edit_student.php?students_id=<?php echo $students_id; ?>" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="program_id" class="form-label">Program</label>
                        <select class="form-select" id="program_id" name="program_id" required>
                            <option value="0">Select a program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo htmlspecialchars($program['program_id']); ?>"
                                        <?php echo ($program['program_id'] == $student['program_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($program['program_name']); ?> (<?php echo htmlspecialchars($program['department_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Student</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
