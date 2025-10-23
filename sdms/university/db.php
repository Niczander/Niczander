<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "university_database";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to utf8mb4
mysqli_set_charset($conn, "utf8mb4");
?>
