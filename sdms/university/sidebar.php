<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 280px;
        height: 100vh;
        background: linear-gradient(to bottom, #1e3c72, #2a5298);
    }
    .sidebar .nav-link {
        color: #e0e0e0;
        transition: background-color 0.2s, color 0.2s;
    }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }
    .sidebar .dropdown-menu {
        background-color: #2a5298;
    }
    .sidebar .dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>
<?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
<div class="d-flex flex-column flex-shrink-0 p-3 text-white sidebar">
    <a href="home.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="fas fa-university fa-2x me-2"></i>
        <span class="fs-4">University</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li>
            <a href="dashboard.php" class="nav-link text-white <?php if ($currentPage == 'dashboard.php') echo 'active'; ?>">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="home.php" class="nav-link text-white <?php if ($currentPage == 'home.php') echo 'active'; ?>" aria-current="page">
                <i class="fas fa-home me-2"></i>
                Add Student
            </a>
        </li>
        <li>
            <a href="add_program.php" class="nav-link text-white <?php if ($currentPage == 'add_program.php') echo 'active'; ?>">
                <i class="fas fa-plus-circle me-2"></i>
                Add Program
            </a>
        </li>
        <li>
            <a href="add_department.php" class="nav-link text-white <?php if ($currentPage == 'add_department.php') echo 'active'; ?>">
                <i class="fas fa-plus-circle me-2"></i>
                Add Department
            </a>
        </li>
        <!-- <li>
            <a href="add_staffu.php" class="nav-link text-white <?php if ($currentPage == 'add_staff.php') echo 'active'; ?>">
                <i class="fas fa-user-plus me-2"></i>
                Add Staff
            </a>
        </li> -->
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
        </ul>
    </div>
</div>
