<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Academic Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar-wrapper" backgroundColor="green">
            <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">
                <i class="bi bi-building me-2"></i>AMS
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="students.php" class="nav-link"><i class="bi bi-people"></i> Students</a>
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
                    <h2 class="fs-2 m-0">Dashboard</h2>
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
                <!-- Stats Cards -->
                <div class="row g-3 my-2">
                    <?php
                    $database = new Database();
                    $db = $database->getConnection();
                    
                    $studentCount = $db->query("SELECT COUNT(*) as total FROM students")->fetch(PDO::FETCH_ASSOC)['total'];
                    $courseCount = $db->query("SELECT COUNT(*) as total FROM courses")->fetch(PDO::FETCH_ASSOC)['total'];
                    $staffCount = $db->query("SELECT COUNT(*) as total FROM staff")->fetch(PDO::FETCH_ASSOC)['total'];
                    ?>
                    <div class="col-md-4">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                            <div>
                                <h3 class="fs-2"><?php echo $studentCount; ?></h3>
                                <p class="fs-5">Students</p>
                            </div>
                            <i class="bi bi-people fs-1 text-primary"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                            <div>
                                <h3 class="fs-2"><?php echo $courseCount; ?></h3>
                                <p class="fs-5">Courses</p>
                            </div>
                            <i class="bi bi-book fs-1 text-success"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                            <div>
                                <h3 class="fs-2"><?php echo $staffCount; ?></h3>
                                <p class="fs-5">Staff</p>
                            </div>
                            <i class="bi bi-person-badge fs-1 text-info"></i>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row my-5">
                    <div class="col-md-6">
                         <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Student Gender Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="genderChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Student Enrollment by Department</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="departmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row my-5">
                    <div class="col">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Student Enrollment by Course</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="courseEnrollmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var el = document.getElementById("wrapper");
        var toggleButton = document.getElementById("menu-toggle");

        toggleButton.onclick = function () {
            el.classList.toggle("toggled");
        };

        <?php
        // Gender Distribution Data
        $gender_data = $db->query("SELECT gender, COUNT(*) as count FROM students GROUP BY gender")->fetchAll(PDO::FETCH_KEY_PAIR);
        $gender_labels = json_encode(array_keys($gender_data));
        $gender_values = json_encode(array_values($gender_data));

        // Department Enrollment Data
        $dept_data = $db->query("SELECT d.name, COUNT(s.id) as count FROM departments d JOIN students s ON d.id = s.department_id GROUP BY d.name")->fetchAll(PDO::FETCH_KEY_PAIR);
        $dept_labels = json_encode(array_keys($dept_data));
        $dept_values = json_encode(array_values($dept_data));

        // Course Enrollment Data
        $course_enroll_data = $db->query("SELECT c.name, COUNT(sc.student_id) as count FROM courses c JOIN student_courses sc ON c.id = sc.course_id GROUP BY c.name")->fetchAll(PDO::FETCH_KEY_PAIR);
        $course_enroll_labels = json_encode(array_keys($course_enroll_data));
        $course_enroll_values = json_encode(array_values($course_enroll_data));
        ?>

        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: <?php echo $gender_labels; ?>,
                datasets: [{ data: <?php echo $gender_values; ?>, backgroundColor: ['#36a2eb', '#ff6384', '#ffce56'] }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Department Chart
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(departmentCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $dept_labels; ?>,
                datasets: [{ label: 'Number of Students', data: <?php echo $dept_values; ?>, backgroundColor: '#4e73df' }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });

        // Course Enrollment Chart
        const courseEnrollmentCtx = document.getElementById('courseEnrollmentChart').getContext('2d');
        new Chart(courseEnrollmentCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $course_enroll_labels; ?>,
                datasets: [{ label: 'Number of Students', data: <?php echo $course_enroll_values; ?>, backgroundColor: '#1cc88a' }]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
