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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            try {
                $staff_id = sanitize_input($_POST['staff_id']);
                $first_name = sanitize_input($_POST['first_name']);
                $last_name = sanitize_input($_POST['last_name']);
                $email = sanitize_input($_POST['email']);
                $phone = sanitize_input($_POST['phone']);
                $department_id = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
                $position = sanitize_input($_POST['position']);

                // If department provided, ensure it exists
                if ($department_id !== null) {
                    $chk = $pdo->prepare("SELECT 1 FROM departments WHERE id = ?");
                    $chk->execute([$department_id]);
                    if (!$chk->fetchColumn()) {
                        $message = '<div class="alert alert-warning">Selected department does not exist. Please choose a valid department.</div>';
                        break;
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO staff (staff_id, first_name, last_name, email, phone, department_id, position) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$staff_id, $first_name, $last_name, $email, $phone, $department_id, $position]);
                $message = '<div class="alert alert-success">Staff member added successfully!</div>';
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $message = '<div class="alert alert-warning">Unable to save. Ensure email/IDs are unique and department is valid.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            break;

        case 'update':
            if (isset($_POST['id'])) {
                try {
                    $id = (int)$_POST['id'];
                    $staff_id = sanitize_input($_POST['staff_id']);
                    $first_name = sanitize_input($_POST['first_name']);
                    $last_name = sanitize_input($_POST['last_name']);
                    $email = sanitize_input($_POST['email']);
                    $phone = sanitize_input($_POST['phone']);
                    $department_id = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
                    $position = sanitize_input($_POST['position']);

                    if ($department_id !== null) {
                        $chk = $pdo->prepare("SELECT 1 FROM departments WHERE id = ?");
                        $chk->execute([$department_id]);
                        if (!$chk->fetchColumn()) {
                            $message = '<div class="alert alert-warning">Selected department does not exist. Please choose a valid department.</div>';
                            break;
                        }
                    }

                    $stmt = $pdo->prepare("UPDATE staff SET staff_id = ?, first_name = ?, last_name = ?, email = ?, phone = ?, department_id = ?, position = ? WHERE id = ?");
                    $stmt->execute([$staff_id, $first_name, $last_name, $email, $phone, $department_id, $position, $id]);
                    $message = '<div class="alert alert-success">Staff member updated successfully!</div>';
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        $message = '<div class="alert alert-warning">Unable to save. Ensure email/IDs are unique and department is valid.</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
            }
            break;

        case 'delete':
            if (isset($_POST['id'])) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
                    $stmt->execute([(int)$_POST['id']]);
                    $message = '<div class="alert alert-success">Staff member deleted successfully!</div>';
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
            }
            break;
    }
}

// Fetch staff and departments
$staff_stmt = $pdo->query("SELECT s.*, d.name as department_name FROM staff s LEFT JOIN departments d ON s.department_id = d.id ORDER BY s.first_name, s.last_name");
$staff_members = $staff_stmt->fetchAll();

$departments_stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
$departments = $departments_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Academic Management System</title>
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
                <a href="courses.php" class="nav-link"><i class="bi bi-book"></i> Courses</a>
                <a href="staff.php" class="nav-link active"><i class="bi bi-person-badge"></i> Staff</a>
                <a href="notifications.php" class="nav-link"><i class="bi bi-bell"></i> Notifications</a>
                <a href="profile.php" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Staff</h2>
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
                    <h1 class="h3 mb-0">Staff Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staffModal"><i class="fas fa-plus me-2"></i>Add New Staff</button>
                </div>
                <?php echo $message; ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">All Staff</h6></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="staffTable">
                                <thead class="table-dark">
                                    <tr><th>Staff ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Position</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff_members as $staff): ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($staff['staff_id']); ?></span></td>
                                        <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($staff['department_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($staff['position']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-btn me-1" data-id="<?php echo $staff['id']; ?>" data-bs-toggle="modal" data-bs-target="#staffModal"><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $staff['id']; ?>" data-name="<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>"><i class="fas fa-trash"></i></button>
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

    <!-- Staff Modal -->
    <div class="modal fade" id="staffModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="staffForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Staff Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="staffId">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Staff ID *</label><input type="text" class="form-control" name="staff_id" id="staffIdentifier" required></div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Position *</label>
                                <select class="form-select" name="position" id="staffPosition" required>
                                    <option value="">Select Position</option>
                                    <option value="Professor">Professor</option>
                                    <option value="Senior Lecturer">Senior Lecturer</option>
                                    <option value="Lecturer">Lecturer</option>
                                    <option value="Retainee">Retainee</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">First Name *</label><input type="text" class="form-control" name="first_name" id="firstName" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Last Name *</label><input type="text" class="form-control" name="last_name" id="lastName" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" id="staffEmail" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" id="staffPhone"></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Department</label><select class="form-select" name="department_id" id="departmentId"><option value="">Select Department</option><?php foreach ($departments as $dept): ?><option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Staff</button>
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

        const staffModal = document.getElementById('staffModal');
        staffModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button && button.classList.contains('edit-btn') ? 'update' : 'create';
            document.getElementById('formAction').value = action;
            document.querySelector('#staffModal .modal-title').textContent = action === 'update' ? 'Edit Staff' : 'Add New Staff';

            if (action === 'update') {
                const staffId = button.dataset.id;
                fetch(`get_staff.php?id=${staffId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('staffId').value = data.staff.id;
                            document.getElementById('staffIdentifier').value = data.staff.staff_id;
                            document.getElementById('firstName').value = data.staff.first_name;
                            document.getElementById('lastName').value = data.staff.last_name;
                            document.getElementById('staffEmail').value = data.staff.email;
                            document.getElementById('staffPhone').value = data.staff.phone;
                            document.getElementById('departmentId').value = data.staff.department_id;
                            document.getElementById('staffPosition').value = data.staff.position;
                        }
                    });
            } else {
                document.getElementById('staffForm').reset();
                document.getElementById('staffId').value = '';
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
