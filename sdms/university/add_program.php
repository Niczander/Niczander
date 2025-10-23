<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle success and error messages
$success = null;
$error = null;
$error_message = null;

if (isset($_GET['updated'])) {
    $success = "Program updated successfully!";
} elseif (isset($_GET['added'])) {
    $success = "Program added successfully!";
} elseif (isset($_GET['deleted'])) {
    $success = "Program deleted successfully!";
} elseif (isset($_GET['error'])) {
    $error = true;
    $error_message = isset($_GET['message']) ? urldecode($_GET['message']) : "An error occurred";
}

// Get departments for dropdown
$query = "SELECT * FROM department_data ORDER BY department_name";
$result = mysqli_query($conn, $query);
$departments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $program_name = trim($_POST['program_name'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    
    // Get course units data
    $course_codes = $_POST['course_code'] ?? [];
    $course_names = $_POST['course_name'] ?? [];
    $course_descriptions = $_POST['course_description'] ?? [];
    $course_credits = $_POST['course_credits'] ?? [];
    $course_semesters = $_POST['course_semester'] ?? [];
    $course_year_levels = $_POST['course_year_level'] ?? [];
    
    if (empty($program_name)) {
        header("Location: add_program.php?error=1&message=" . urlencode("Program name is required"));
        exit();
    }
    if ($department_id == 0) {
        header("Location: add_program.php?error=1&message=" . urlencode("Department must be selected"));
        exit();
    }

    // Start transaction
    mysqli_autocommit($conn, FALSE);
    
    try {
        // Insert program first
        $query = "INSERT INTO program_data (program_name, department_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            throw new Exception("Error preparing program statement: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "si", $program_name, $department_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting program: " . mysqli_stmt_error($stmt));
        }
        
        $program_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        // Insert course units if any
        if (!empty($course_codes) && is_array($course_codes)) {
            $course_query = "INSERT INTO course_units (course_code, course_name, course_description, credits, semester, year_level, program_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $course_stmt = mysqli_prepare($conn, $course_query);
            
            if (!$course_stmt) {
                throw new Exception("Error preparing course statement: " . mysqli_error($conn));
            }
            
            for ($i = 0; $i < count($course_codes); $i++) {
                $code = trim($course_codes[$i]);
                $name = trim($course_names[$i] ?? '');
                $description = trim($course_descriptions[$i] ?? '');
                $credits = intval($course_credits[$i] ?? 3);
                $semester = trim($course_semesters[$i] ?? '');
                $year_level = intval($course_year_levels[$i] ?? 1);
                
                // Skip empty course entries
                if (empty($code) || empty($name)) {
                    continue;
                }
                
                mysqli_stmt_bind_param($course_stmt, "sssisii", $code, $name, $description, $credits, $semester, $year_level, $program_id);
                
                if (!mysqli_stmt_execute($course_stmt)) {
                    throw new Exception("Error inserting course unit: " . mysqli_stmt_error($course_stmt));
                }
            }
            
            mysqli_stmt_close($course_stmt);
        }
        
        // Commit transaction
        mysqli_commit($conn);
        mysqli_autocommit($conn, TRUE);
        
        header("Location: add_program.php?added=1");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        mysqli_autocommit($conn, TRUE);
        
        header("Location: add_program.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
}

// Get all departments for the form
$query = "SELECT * FROM department_data ORDER BY department_name";
$result = mysqli_query($conn, $query);
$departments = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get all programs with course units count
$query = "SELECT p.*, d.department_name as department_name, 
          COUNT(c.course_id) as course_count
          FROM program_data p 
          LEFT JOIN department_data d ON p.department_id = d.department_id 
          LEFT JOIN course_units c ON p.program_id = c.program_id
          GROUP BY p.program_id
          ORDER BY p.program_name";
$result = mysqli_query($conn, $query);
$programs = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Handle success and error messages
$success = null;
$error = null;
$error_message = null;

if (isset($_GET['added'])) {
    $success = "Program added successfully!";
} elseif (isset($_GET['updated'])) {
    $success = "Program updated successfully!";
} elseif (isset($_GET['error'])) {
    $error = true;
    $error_message = isset($_GET['message']) ? urldecode($_GET['message']) : "An error occurred";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Management System - Add Program</title>
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
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .table td {
            vertical-align: middle;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .action-buttons a {
            margin-right: 5px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .select2-container {
            width: 100% !important;
        }
        .alert {
            margin-bottom: 20px;
        }
        .course-unit-item {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6 !important;
        }
        .course-unit-item h6 {
            color: #495057;
            font-weight: 600;
        }
        #courseUnitsContainer {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body style="display: flex;">
    <?php include 'sidebar.php'; ?>
    <div class="main-content" style="flex-grow: 1; padding: 20px; margin-left: 280px;">
        <h1 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Add Program</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Add Program Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Program Information</h5>
            </div>
            <div class="card-body">
                <form action="add_program.php" method="post" id="programForm">
                    <div class="mb-3">
                        <label for="program_name" class="form-label">Program Name</label>
                        <input type="text" class="form-control" id="program_name" name="program_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="0">Select a department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo htmlspecialchars($department['department_id']); ?>">
                                    <?php echo htmlspecialchars($department['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Course Units Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Course Units</h6>
                            <button type="button" class="btn btn-sm btn-success" onclick="addCourseUnit()">
                                <i class="fas fa-plus"></i> Add Course Unit
                            </button>
                        </div>
                        
                        <div id="courseUnitsContainer">
                            <!-- Course units will be added here dynamically -->
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Program with Course Units</button>
                </form>
            </div>
        </div>

        <!-- Programs Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Programs</h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="programSearch" class="form-control" placeholder="Search programs..." style="width: 250px;">
                        <select id="departmentFilter" class="form-select" style="width: 200px;">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="programsTable">
                        <thead>
                            <tr>
                                <th>Program Name</th>
                                <th>Department</th>
                                <th class="text-center">Course Units</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programs as $program): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($program['program_name']); ?></td>
                                    <td><?php echo htmlspecialchars($program['department_name'] ?? 'Not assigned'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?php echo $program['course_count']; ?> courses</span>
                                    </td>
                                    <td class="action-buttons text-center">
                                        <a href='edit_program.php?program_id=<?php echo $program['program_id']; ?>' 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href='delete_program.php?program_id=<?php echo $program['program_id']; ?>' 
                                           class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div id="noResults" class="text-center text-muted py-4" style="display: none;">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>No programs found matching your search criteria.</p>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let courseUnitCount = 0;
        
        function addCourseUnit() {
            courseUnitCount++;
            const container = document.getElementById('courseUnitsContainer');
            
            const courseUnitHtml = `
                <div class="course-unit-item border rounded p-3 mb-3" id="courseUnit${courseUnitCount}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Course Unit ${courseUnitCount}</h6>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeCourseUnit(${courseUnitCount})">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="course_code_${courseUnitCount}" class="form-label">Course Code</label>
                                <input type="text" class="form-control" id="course_code_${courseUnitCount}" 
                                       name="course_code[]" placeholder="e.g., CS101" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="course_name_${courseUnitCount}" class="form-label">Course Name</label>
                                <input type="text" class="form-control" id="course_name_${courseUnitCount}" 
                                       name="course_name[]" placeholder="e.g., Introduction to Programming" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="course_description_${courseUnitCount}" class="form-label">Course Description</label>
                        <textarea class="form-control" id="course_description_${courseUnitCount}" 
                                  name="course_description[]" rows="2" 
                                  placeholder="Brief description of the course"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="course_credits_${courseUnitCount}" class="form-label">Credits</label>
                                <input type="number" class="form-control" id="course_credits_${courseUnitCount}" 
                                       name="course_credits[]" min="1" max="6" value="3" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="course_semester_${courseUnitCount}" class="form-label">Semester</label>
                                <select class="form-select" id="course_semester_${courseUnitCount}" 
                                        name="course_semester[]" required>
                                    <option value="">Select Semester</option>
                                    <option value="Fall">Fall</option>
                                    <option value="Spring">Spring</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="course_year_level_${courseUnitCount}" class="form-label">Year Level</label>
                                <select class="form-select" id="course_year_level_${courseUnitCount}" 
                                        name="course_year_level[]" required>
                                    <option value="">Select Year</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', courseUnitHtml);
        }
        
        function removeCourseUnit(unitId) {
            const element = document.getElementById(`courseUnit${unitId}`);
            if (element) {
                element.remove();
            }
        }
        
        // Add one course unit by default when page loads
        document.addEventListener('DOMContentLoaded', function() {
            addCourseUnit();
            initializeSearch();
        });
        
        // Search and filter functionality
        function initializeSearch() {
            const searchInput = document.getElementById('programSearch');
            const departmentFilter = document.getElementById('departmentFilter');
            const table = document.getElementById('programsTable');
            const noResults = document.getElementById('noResults');
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedDepartment = departmentFilter.value.toLowerCase();
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                let visibleRows = 0;
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const programName = row.cells[0].textContent.toLowerCase();
                    const departmentName = row.cells[1].textContent.toLowerCase();
                    
                    const matchesSearch = programName.includes(searchTerm);
                    const matchesDepartment = selectedDepartment === '' || departmentName.includes(selectedDepartment);
                    
                    if (matchesSearch && matchesDepartment) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                }
                
                // Show/hide no results message
                if (visibleRows === 0) {
                    table.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    table.style.display = 'table';
                    noResults.style.display = 'none';
                }
            }
            
            // Add event listeners
            searchInput.addEventListener('input', filterTable);
            departmentFilter.addEventListener('change', filterTable);
        }
    </script>
</body>
</html>