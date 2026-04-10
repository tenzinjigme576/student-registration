<?php
// db.php
// Database connection file (XAMPP default settings).
// Keep this file simple for lab exams.

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "student_registration";

// Create MySQLi connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Use a consistent character set
$conn->set_charset("utf8mb4");

