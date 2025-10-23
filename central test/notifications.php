<?php
session_start();
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
    <title>Notifications - Academic Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        // Example placeholder data fetch; wire this to your backend later
        document.addEventListener('DOMContentLoaded', () => {
            const list = document.getElementById('notifList');
            const sample = [
                { icon: 'bi-person', title: 'New student registered', time: '2h ago' },
                { icon: 'bi-book', title: 'Course CS202 updated', time: '5h ago' },
                { icon: 'bi-people', title: '3 students enrolled', time: 'Yesterday' }
            ];
            sample.forEach(n => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `<span><i class="bi ${n.icon} me-2"></i>${n.title}</span><small class="text-muted">${n.time}</small>`;
                list.appendChild(li);
            });
        });
    </script>
    <style>
    .notifications-card .list-group-item { border: none; }
    .notifications-card .list-group-item + .list-group-item { border-top: 1px solid rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div class="sidebar" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">
                <i class="bi bi-building me-2"></i>AMS
            </div>
            <div class="profile-header">
                <div class="avatar-lg"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <div class="profile-meta">
                    <div class="name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div class="subtle">Logged in</div>
                </div>
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="students.php" class="nav-link"><i class="bi bi-people"></i> Students</a>
                <a href="courses.php" class="nav-link"><i class="bi bi-book"></i> Courses</a>
                <a href="staff.php" class="nav-link"><i class="bi bi-person-badge"></i> Staff</a>
                <a href="notifications.php" class="nav-link active"><i class="bi bi-bell"></i> Notifications</a>
                <a href="profile.php" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-4 px-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list fs-4 me-3" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0">Notifications</h2>
                </div>
            </nav>
            <div class="container-fluid px-4">
                <div class="card notifications-card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Notifications</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="notifList"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var el = document.getElementById('wrapper');
        var toggleButton = document.getElementById('menu-toggle');
        toggleButton.onclick = function () { el.classList.toggle('toggled'); };
    </script>
</body>
</html>

