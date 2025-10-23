<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($username) || empty($password)) {
        echo "<div style='color: red;'>Username and password are required.</div>";
        include 'index.php';
        exit();
    }

    // Debug output
    echo "<div style='color: blue;'>Debug Info:</div>";
    echo "<div style='color: blue;'>Attempting to login with username: " . htmlspecialchars($username) . "</div>";

    // Query to check if user exists
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        echo "<div style='color: red;'>Error preparing statement: " . mysqli_error($conn) . "</div>";
        include 'index.php';
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $num_rows = mysqli_num_rows($result);
        echo "<div style='color: blue;'>Found " . $num_rows . " rows in database.</div>";

        if ($num_rows == 1) {
            $user = mysqli_fetch_assoc($result);
            
            echo "<div style='color: blue;'>Found user in database.</div>";
            echo "<div style='color: blue;'>Checking password...</div>";
            
            // Check if password matches
            $password_matches = password_verify($password, $user['password']);
            
            if ($password_matches) {
                echo "<div style='color: green;'>Password matches! Logging in...</div>";
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $username;
                header("Location: home.php");
                exit();
            } else {
                echo "<div style='color: red;'>Password does not match the stored hash.</div>";
            }
        } else {
            echo "<div style='color: red;'>Username not found in database.</div>";
        }
    } else {
        echo "<div style='color: red;'>Error executing query: " . mysqli_error($conn) . "</div>";
    }
} else {
    // Show login form if not POST request
    include 'index.php';
}
?>
