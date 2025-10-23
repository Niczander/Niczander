<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

require_once 'config/database.php';

$database = new Database();
$pdo = $database->getConnection();

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Handle CRUD operations
$message = '';
$student_id = $_POST['id'] ?? ($_GET['id'] ?? null);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    $first_name = sanitize_input($_POST['first_name']);
                    $last_name = sanitize_input($_POST['last_name']);
                    $reg_number = strtoupper(trim(sanitize_input($_POST['reg_number'])));
                    $gender = sanitize_input($_POST['gender']);
                    $email = sanitize_input($_POST['email']);
                    $phone = sanitize_input($_POST['phone']);
                    $year = (int)$_POST['year_of_study'];

                    // Duplicate check for reg_number
                    $dup = $pdo->prepare("SELECT COUNT(*) FROM students WHERE reg_number = ?");
                    $dup->execute([$reg_number]);
                    if ($dup->fetchColumn() > 0) {
                        $message = '<div class="alert alert-warning">Registration number <strong>' . htmlspecialchars($reg_number) . '</strong> already exists.</div>';
                        break;
                    }

                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, reg_number, gender, email, phone, year_of_study) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$first_name, $last_name, $reg_number, $gender, $email, $phone, $year]);
                    $newStudentId = (int)$pdo->lastInsertId();

                    if (!empty($_POST['courses']) && is_array($_POST['courses'])) {
                        $ins = $pdo->prepare("INSERT IGNORE INTO student_courses (student_id, course_id, semester, academic_year) VALUES (?, ?, 1, YEAR(CURDATE()))");
                        foreach ($_POST['courses'] as $cid) {
                            $ins->execute([$newStudentId, (int)$cid]);
                        }
                    }

                    $pdo->commit();

                    $message = '<div class="alert alert-success">Student added successfully!</div>';
                } catch (PDOException $e) {
                    if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
                    if ($e->getCode() === '23000') {
                        $message = '<div class="alert alert-warning">Duplicate registration number. Please use a unique value.</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                break;

            case 'update':
                if ($student_id) {
                    try {
                        $first_name = sanitize_input($_POST['first_name']);
                        $last_name = sanitize_input($_POST['last_name']);
                        $reg_number = strtoupper(trim(sanitize_input($_POST['reg_number'])));
                        $gender = sanitize_input($_POST['gender']);
                        $email = sanitize_input($_POST['email']);
                        $phone = sanitize_input($_POST['phone']);
                        $year = (int)$_POST['year_of_study'];

                        // Ensure reg_number uniqueness excluding current record
                        $dup = $pdo->prepare("SELECT COUNT(*) FROM students WHERE reg_number = ? AND id <> ?");
                        $dup->execute([$reg_number, (int)$student_id]);
                        if ($dup->fetchColumn() > 0) {
                            $message = '<div class="alert alert-warning">Registration number <strong>' . htmlspecialchars($reg_number) . '</strong> already exists.</div>';
                            break;
                        }

                        $pdo->beginTransaction();

                        $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, reg_number = ?, gender = ?, email = ?, phone = ?, year_of_study = ? WHERE id = ?");
                        $stmt->execute([$first_name, $last_name, $reg_number, $gender, $email, $phone, $year, (int)$student_id]);

                        // Sync enrollments
                        $selected = [];
                        if (!empty($_POST['courses']) && is_array($_POST['courses'])) {
                            $selected = array_map('intval', $_POST['courses']);
                        }

                        if (empty($selected)) {
                            $delAll = $pdo->prepare("DELETE FROM student_courses WHERE student_id = ?");
                            $delAll->execute([(int)$student_id]);
                        } else {
                            // Delete unselected
                            $placeholders = implode(',', array_fill(0, count($selected), '?'));
                            $params = array_merge([(int)$student_id], $selected);
                            $del = $pdo->prepare("DELETE FROM student_courses WHERE student_id = ? AND course_id NOT IN ($placeholders)");
                            $del->execute($params);

                            // Insert missing
                            $existing = $pdo->prepare("SELECT course_id FROM student_courses WHERE student_id = ?");
                            $existing->execute([(int)$student_id]);
                            $existingIds = array_map('intval', array_column($existing->fetchAll(PDO::FETCH_ASSOC), 'course_id'));
                            $toInsert = array_diff($selected, $existingIds);
                            if (!empty($toInsert)) {
                                $ins = $pdo->prepare("INSERT INTO student_courses (student_id, course_id, semester, academic_year) VALUES (?, ?, 1, YEAR(CURDATE()))");
                                foreach ($toInsert as $cid) { $ins->execute([(int)$student_id, (int)$cid]); }
                            }
                        }

                        $pdo->commit();

                        $message = '<div class="alert alert-success">Student updated successfully!</div>';
                    } catch (PDOException $e) {
                        if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
                        if ($e->getCode() === '23000') {
                            $message = '<div class="alert alert-warning">Duplicate registration number. Please use a unique value.</div>';
                        } else {
                            $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                    }
                }
                break;

            case 'delete':
                if ($student_id) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
                        $stmt->execute([(int)$student_id]);
                        $message = '<div class="alert alert-success">Student deleted successfully!</div>';
                        $student_id = null; // Reset to show main list
                    } catch (PDOException $e) {
                        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                    }
                }
                break;
        }
    }
}

// Fetch students data
$stmt = $pdo->query("
    SELECT s.*, GROUP_CONCAT(c.course_code) as enrolled_courses
    FROM students s
    LEFT JOIN student_courses sc ON s.id = sc.student_id
    LEFT JOIN courses c ON sc.course_id = c.id
    GROUP BY s.id
    ORDER BY s.first_name, s.last_name
");
$students = $stmt->fetchAll();

// Fetch student for editing
$edit_student = null;
if ($student_id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([(int)$student_id]);
    $edit_student = $stmt->fetch();
}

// Fetch all courses for enrollment dropdown
$stmt = $pdo->query("SELECT id, course_code as code, name FROM courses ORDER BY course_code");
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Academic Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">
                <i class="bi bi-building me-2"></i>AMS
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="students.php" class="nav-link active"><i class="bi bi-people"></i> Students</a>
                <a href="courses.php" class="nav-link"><i class="bi bi-book"></i> Courses</a>
                <a href="staff.php" class="nav-link"><i class="bi bi-person-badge"></i> Staff</a>
                <a href="notifications.php" class="nav-link"><i class="bi bi-bell"></i> Notifications</a>
                <a href="profile.php" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Students</h2>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-users me-2 text-primary"></i>
            Student Management
        </h1>
        <button class="btn btn-primary" id="addStudentBtn" data-bs-toggle="modal" data-bs-target="#studentModal">
            <i class="fas fa-plus me-2"></i>Add New Student
        </button>
    </div>

    <?php echo $message ?? ''; ?>

    <!-- Students Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Students</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="studentSearch" class="form-control" 
                                   placeholder="Search students by name, reg no, or email...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="genderFilter" class="form-select">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="courseFilter" class="form-select">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['code']; ?>"><?php echo $course['code']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead class="table-dark">
                                    <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Registration No</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Year</th>
                            <th>Courses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td>
                                <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($student['reg_number']); ?></span>
                            </td>
                            <td>
                                <span class="badge <?php 
                                    echo $student['gender'] == 'Male' ? 'bg-info' : 
                                         ($student['gender'] == 'Female' ? 'bg-pink' : 'bg-secondary');
                                ?>">
                                    <?php echo $student['gender']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo $student['year_of_study']; ?></td>
                            <td>
                                <?php if ($student['enrolled_courses']): ?>
                                    <div class="small">
                                        <?php 
                                        $courses_list = explode(',', $student['enrolled_courses']);
                                        foreach (array_slice($courses_list, 0, 3) as $course_code) {
                                            echo '<span class="badge bg-success me-1 mb-1">' . trim($course_code) . '</span>';
                                        }
                                        if (count($courses_list) > 3) {
                                            echo '<span class="badge bg-secondary">+' . (count($courses_list) - 3) . '</span>';
                                        }
                                        ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No courses</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary edit-btn" 
                                            data-id="<?php echo $student['id']; ?>" 
                                            data-bs-toggle="modal" data-bs-target="#studentModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary view-courses-btn" 
                                            data-id="<?php echo $student['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>">
                                        <i class="fas fa-book-open"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-btn" 
                                            data-id="<?php echo $student['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student Modal -->
    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>
                        <?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="studentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" value="">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?php echo $edit_student['first_name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?php echo $edit_student['last_name'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration Number *</label>
                                <input type="text" class="form-control" name="reg_number" 
                                       value="<?php echo $edit_student['reg_number'] ?? ''; ?>" required>
                            </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender *</label>
                                <select class="form-select" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($edit_student['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($edit_student['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($edit_student['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year of Study *</label>
                                <select class="form-select" name="year_of_study" required>
                                    <option value="">Select Year</option>
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($edit_student['year_of_study'] ?? 2) == $i ? 'selected' : ''; ?>>
                                        Year <?php echo $i; ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo $edit_student['email'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo $edit_student['phone'] ?? ''; ?>">
                            </div>
                        </div>

                        <!-- Enrolled Courses -->
                        <div class="mb-3">
                            <label class="form-label">Enrolled Courses</label>
                            <select class="form-select" name="courses[]" multiple size="4">
                                <?php foreach ($courses as $course): ?>
                                <?php 
                                // Check if student is enrolled in this course
                                $enrolled = false;
                                if ($edit_student) {
                                    $stmt = $pdo->prepare("SELECT * FROM student_courses WHERE student_id = ? AND course_id = ?");
                                    $stmt->execute([$edit_student['id'], $course['id']]);
                                    $enrolled = $stmt->rowCount() > 0;
                                }
                                ?>
                                <option value="<?php echo $course['id']; ?>" 
                                        <?php echo $enrolled ? 'selected' : ''; ?>>
                                    <?php echo $course['code'] . ' - ' . $course['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple courses</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveStudentBtn">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $edit_student ? 'Update Student' : 'Add Student'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Courses Modal -->
<div class="modal fade" id="viewCoursesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book-open me-2"></i>Enrolled Courses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <label class="input-group-text" for="addCourseSelect">Add Course</label>
                            <select class="form-select" id="addCourseSelect"></select>
                            <button class="btn btn-primary" id="addCourseBtn"><i class="fas fa-plus me-1"></i>Add</button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div id="coursesAlert" class="alert alert-info py-2 px-3 d-none mb-0">Loading...</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Code</th>
                                <th>Course Name</th>
                                <th>Semester</th>
                                <th>Year</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="enrolledCoursesBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Real-time search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const genderFilter = document.getElementById('genderFilter');
    const courseFilter = document.getElementById('courseFilter');
    const table = document.getElementById('studentsTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedGender = genderFilter.value;
        const selectedCourse = courseFilter.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const genderCell = row.cells[5].textContent.trim();
            const coursesCell = row.cells[9].textContent.toLowerCase();
            
            const matchesSearch = text.includes(searchTerm);
            const matchesGender = !selectedGender || genderCell === selectedGender;
            const matchesCourse = !selectedCourse || coursesCell.includes(selectedCourse.toLowerCase());
            
            if (matchesSearch && matchesGender && matchesCourse) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add event listeners for real-time filtering
    searchInput.addEventListener('keyup', filterTable);
    genderFilter.addEventListener('change', filterTable);
    courseFilter.addEventListener('change', filterTable);

    // Edit button functionality
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.id;
            const form = document.getElementById('studentForm');
            // Force update mode
            form.querySelector('input[name="action"]').value = 'update';
            form.querySelector('input[name="id"]').value = studentId;

            // AJAX call to populate modal with student data
            fetch(`get_student.php?id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate form fields
                        form.querySelector('input[name="first_name"]').value = data.student.first_name;
                        form.querySelector('input[name="last_name"]').value = data.student.last_name;
                        form.querySelector('input[name="reg_number"]').value = data.student.reg_number;
                        form.querySelector('select[name="gender"]').value = data.student.gender;
                        form.querySelector('input[name="email"]').value = data.student.email;
                        form.querySelector('input[name="phone"]').value = data.student.phone || '';
                        form.querySelector('select[name="year_of_study"]').value = data.student.year_of_study;

                        // Update modal title
                        document.querySelector('.modal-title').textContent = `Edit Student: ${data.student.first_name} ${data.student.last_name}`;

                        // Handle courses selection
                        const coursesSelect = form.querySelector('select[name="courses[]"]');
                        Array.from(coursesSelect.options).forEach(option => {
                            option.selected = data.enrolled_courses.includes(parseInt(option.value));
                        });
                    }
                });
        });
    });

    // View Courses
    const viewCoursesModalEl = document.getElementById('viewCoursesModal');
    const viewCoursesModal = new bootstrap.Modal(viewCoursesModalEl);
    document.querySelectorAll('.view-courses-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.id;
            // Store current student id on modal for later actions
            viewCoursesModalEl.dataset.studentId = studentId;
            const studentName = this.dataset.name;
            document.querySelector('#viewCoursesModal .modal-title').innerHTML = `<i class="fas fa-book-open me-2"></i>Enrolled Courses - ${studentName}`;

            const tbody = document.getElementById('enrolledCoursesBody');
            const alertBox = document.getElementById('coursesAlert');
            tbody.innerHTML = '';
            alertBox.classList.remove('d-none');
            alertBox.classList.replace('alert-danger', 'alert-info');
            alertBox.textContent = 'Loading...';

            // Load courses list for adding
            fetch('get_courses_list.php').then(r => r.json()).then(list => {
                const sel = document.getElementById('addCourseSelect');
                sel.innerHTML = '';
                if (list.success) {
                    list.courses.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id; opt.textContent = `${c.code} - ${c.name}`;
                        sel.appendChild(opt);
                    });
                }
            });

            fetch(`get_student_courses.php?id=${studentId}`)
                .then(r => r.json())
                .then(data => {
                    alertBox.classList.add('d-none');
                    tbody.innerHTML = '';
                    if (data.success && data.enrollments.length) {
                        data.enrollments.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><span class="badge bg-primary">${row.course_code}</span></td>
                                <td>${row.course_name}</td>
                                <td>${row.semester}</td>
                                <td>${row.academic_year}</td>
                                <td>${row.grade ?? '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger unenroll-btn" data-course-id="${row.course_id ?? ''}" data-code="${row.course_code}"><i class="fas fa-times"></i></button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        const tr = document.createElement('tr');
                        tr.innerHTML = '<td colspan="6" class="text-center text-muted">No enrollments found</td>';
                        tbody.appendChild(tr);
                    }
                })
                .catch(() => {
                    alertBox.classList.remove('d-none');
                    alertBox.classList.replace('alert-info', 'alert-danger');
                    alertBox.textContent = 'Failed to load enrollments.';
                });

            viewCoursesModal.show();
        });
    });

    // Add course to student
    document.getElementById('addCourseBtn').addEventListener('click', function() {
        const sel = document.getElementById('addCourseSelect');
        const courseId = sel.value;
        const studentId = parseInt(viewCoursesModalEl.dataset.studentId || '0');
        if (!studentId || !courseId) return;
        const alertBox = document.getElementById('coursesAlert');
        alertBox.classList.remove('d-none');
        alertBox.classList.replace('alert-danger', 'alert-info');
        alertBox.textContent = 'Saving...';
        fetch('enroll_course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `student_id=${studentId}&course_id=${courseId}`
        }).then(r => r.json()).then(data => {
            if (data.success) {
                // Refresh list
                document.querySelector(`.view-courses-btn[data-id="${studentId}"]`).click();
            } else {
                alertBox.classList.replace('alert-info', 'alert-danger');
                alertBox.textContent = data.message || 'Failed to add course';
            }
        }).catch(() => {
            alertBox.classList.replace('alert-info', 'alert-danger');
            alertBox.textContent = 'Failed to add course';
        });
    });

    // Unenroll course
    document.getElementById('enrolledCoursesBody').addEventListener('click', function(e) {
        if (e.target.closest('.unenroll-btn')) {
            const btn = e.target.closest('.unenroll-btn');
            const courseId = parseInt(btn.dataset.courseId || '0');
            const code = btn.dataset.code;
            const studentId = parseInt(viewCoursesModalEl.dataset.studentId || '0');
            if (!studentId || !courseId) return;
            if (!confirm(`Remove ${code} from this student?`)) return;
            const alertBox = document.getElementById('coursesAlert');
            alertBox.classList.remove('d-none');
            alertBox.classList.replace('alert-danger', 'alert-info');
            alertBox.textContent = 'Removing...';
            fetch('unenroll_course.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `student_id=${studentId}&course_id=${courseId}`
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    document.querySelector(`.view-courses-btn[data-id="${studentId}"]`).click();
                } else {
                    alertBox.classList.replace('alert-info', 'alert-danger');
                    alertBox.textContent = data.message || 'Failed to remove course';
                }
            }).catch(() => {
                alertBox.classList.replace('alert-info', 'alert-danger');
                alertBox.textContent = 'Failed to remove course';
            });
        }
    });

    // Ensure create mode for Add New
    const studentModalEl = document.getElementById('studentModal');
    studentModalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const form = document.getElementById('studentForm');
        if (!button || !button.classList.contains('edit-btn')) {
            form.reset();
            form.querySelector('input[name="action"]').value = 'create';
            form.querySelector('input[name="id"]').value = '';
            document.querySelector('#studentModal .modal-title').innerHTML = '<i class="fas fa-user-plus me-2"></i>Add New Student';
            // Clear multi-select
            const coursesSelect = form.querySelector('select[name="courses[]"]');
            Array.from(coursesSelect.options).forEach(o => o.selected = false);
        }
    });

    // AJAX save for student
    const studentForm = document.getElementById('studentForm');
    studentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('saveStudentBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';

        const formData = new FormData(studentForm);
        fetch('save_student.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to save student');
                }
            })
            .catch(() => alert('Failed to save student'))
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save';
            });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.id;
            const studentName = this.dataset.name;
            
            if (confirm(`Are you sure you want to delete ${studentName}? This action cannot be undone.`)) {
                // AJAX delete
                fetch('delete_student.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${studentId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting student: ' + data.message);
                    }
                });
            }
        });
    });
});
</script>

<style>
.bg-pink {
    background-color: #e91e63 !important;
    color: white;
}
.avatar-sm {
    width: 40px;
    height: 40px;
}
</style>

</div>
</div>
</div>

    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");

        toggleButton.onclick = function () {
            el.classList.toggle("toggled");
        };
    </script>
</body>
</html>