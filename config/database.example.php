<?php
$host = "localhost";
$user = "your_user";
$password = "your_password";
$database = "grade_management";

// Connect to MySQL server first without selecting a database
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if database exists and select it
if (!$conn->select_db($database)) {
    die("Error: Database '$database' does not exist. Please create it and run the SQL in 'sql/schema.sql'.");
}
?>