<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

require_once 'config/database.php';

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$database = new Database();
$pdo = $database->getConnection();

$message = '';
$course_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            try {
                $course_code = strtoupper(trim(sanitize_input($_POST['course_code'])));
                $name = sanitize_input($_POST['name']);
                $credit_hours = (int)$_POST['credit_hours'];
                $department_id = (int)$_POST['department_id'];
                $description = sanitize_input($_POST['description']);

                // Check for duplicate course code
                $dup = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
                $dup->execute([$course_code]);
                if ($dup->fetchColumn() > 0) {
                    $message = '<div class="alert alert-warning">A course with code <strong>' . htmlspecialchars($course_code) . '</strong> already exists.</div>';
                    break;
                }

                $stmt = $pdo->prepare("INSERT INTO courses (course_code, name, credit_hours, department_id, description) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$course_code, $name, $credit_hours, $department_id ?: null, $description]);
                $message = '<div class="alert alert-success">Course added successfully!</div>';
            } catch (PDOException $e) {
                // Handle duplicate key error gracefully as a fallback
                if ($e->getCode() === '23000') {
                    $message = '<div class="alert alert-warning">Duplicate course code. Please use a unique code.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            break;

        case 'update':
            if (isset($_POST['id'])) {
                try {
                    $id = (int)$_POST['id'];
                    $course_code = strtoupper(trim(sanitize_input($_POST['course_code'])));
                    $name = sanitize_input($_POST['name']);
                    $credit_hours = (int)$_POST['credit_hours'];
                    $department_id = (int)$_POST['department_id'];
                    $description = sanitize_input($_POST['description']);

                    // Ensure uniqueness of course code excluding current record
                    $dup = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ? AND id <> ?");
                    $dup->execute([$course_code, $id]);
                    if ($dup->fetchColumn() > 0) {
                        $message = '<div class="alert alert-warning">A course with code <strong>' . htmlspecialchars($course_code) . '</strong> already exists.</div>';
                        break;
                    }

                    $stmt = $pdo->prepare("UPDATE courses SET course_code = ?, name = ?, credit_hours = ?, department_id = ?, description = ? WHERE id = ?");
                    $stmt->execute([$course_code, $name, $credit_hours, $department_id ?: null, $description, $id]);
                    $message = '<div class="alert alert-success">Course updated successfully!</div>';
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        $message = '<div class="alert alert-warning">Duplicate course code. Please use a unique code.</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
            }
            break;

        case 'delete':
            if (isset($_POST['id'])) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
                    $stmt->execute([(int)$_POST['id']]);
                    $message = '<div class="alert alert-success">Course deleted successfully!</div>';
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
            }
            break;
    }
}

// Fetch courses and departments
$courses_stmt = $pdo->query("SELECT c.*, d.name as department_name FROM courses c LEFT JOIN departments d ON c.department_id = d.id ORDER BY c.name");
$courses = $courses_stmt->fetchAll();

$departments_stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
$departments = $departments_stmt->fetchAll();

$edit_course = null;
if ($course_id) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $edit_course = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Academic Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom"><i class="bi bi-building me-2"></i>AMS</div>
            <div class="list-group list-group-flush my-3">
                <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="students.php" class="nav-link"><i class="bi bi-people"></i> Students</a>
                <a href="courses.php" class="nav-link active"><i class="bi bi-book"></i> Courses</a>
                <a href="staff.php" class="nav-link"><i class="bi bi-person-badge"></i> Staff</a>
                <a href="notifications.php" class="nav-link"><i class="bi bi-bell"></i> Notifications</a>
                <a href="profile.php" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Courses</h2>
                </div>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Course Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal"><i class="fas fa-plus me-2"></i>Add New Course</button>
                </div>
                <?php echo $message; ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">All Courses</h6></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="coursesTable">
                                <thead class="table-dark">
                                    <tr><th>Code</th><th>Name</th><th>Credit Hours</th><th>Department</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($course['course_code']); ?></span></td>
                                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                                        <td><?php echo htmlspecialchars($course['credit_hours']); ?></td>
                                        <td><?php echo htmlspecialchars($course['department_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-btn" data-id="<?php echo $course['id']; ?>" data-bs-toggle="modal" data-bs-target="#courseModal"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $course['id']; ?>" data-name="<?php echo htmlspecialchars($course['name']); ?>"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Modal -->
    <div class="modal fade" id="courseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="courseForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Course Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="courseId">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Course Code *</label><input type="text" class="form-control" name="course_code" id="courseCode" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Course Name *</label><input type="text" class="form-control" name="name" id="courseName" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Credit Hours *</label><input type="number" class="form-control" name="credit_hours" id="creditHours" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Department</label><select class="form-select" name="department_id" id="departmentId"><option value="">Select Department</option><?php foreach ($departments as $dept): ?><option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option><?php endforeach; ?></select></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" id="courseDescription" rows="3"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");
        toggleButton.onclick = function () { el.classList.toggle("toggled"); };

        const courseModal = document.getElementById('courseModal');
        courseModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.classList.contains('edit-btn') ? 'update' : 'create';
            document.getElementById('formAction').value = action;
            document.querySelector('.modal-title').textContent = action === 'update' ? 'Edit Course' : 'Add New Course';

            if (action === 'update') {
                const courseId = button.dataset.id;
                fetch(`get_course.php?id=${courseId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('courseId').value = data.course.id;
                            document.getElementById('courseCode').value = data.course.course_code;
                            document.getElementById('courseName').value = data.course.name;
                            document.getElementById('creditHours').value = data.course.credit_hours;
                            document.getElementById('departmentId').value = data.course.department_id;
                            document.getElementById('courseDescription').value = data.course.description;
                        }
                    });
            } else {
                document.getElementById('courseForm').reset();
                document.getElementById('courseId').value = '';
            }
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm(`Are you sure you want to delete ${this.dataset.name}?`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${this.dataset.id}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>
