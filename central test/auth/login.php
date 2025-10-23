<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Basic input validation
    if (empty($username) || empty($password)) {
        header("Location: ../index.php?error=Please fill in all fields");
        exit();
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Prepare a select statement
        $query = "SELECT id, username, password, role FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        
        // Execute the statement
        $stmt->execute();
        
        // Check if username exists
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Password is correct, start a new session
                session_start();
                
                // Store data in session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $row['id'];
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $row['role'];
                
                // Redirect user to dashboard
                header("location: ../dashboard.php");
                exit();
            } else {
                // Display an error message if password is not valid
                header("Location: ../index.php?error=Invalid username or password");
                exit();
            }
        } else {
            // Username doesn't exist
            header("Location: ../index.php?error=Invalid username or password");
            exit();
        }
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: ../index.php?error=An error occurred. Please try again.");
        exit();
    }
} else {
    // If someone tries to access this file directly
    header("Location: ../index.php");
    exit();
}
?>
