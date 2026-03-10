<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "grade_management";

// Connect to MySQL server first without selecting a database
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if database exists and select it
if (!$conn->select_db($dbname)) {
    die("Error: Database '$dbname' does not exist. Please create it and run the SQL in 'sql/schema.sql' in your MySQL manager (like phpMyAdmin).");
}
?>