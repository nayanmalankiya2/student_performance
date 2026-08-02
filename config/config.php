<?php
$server = "localhost";
$uname = "root";
$pwd = "";
$dbname = "student_performance";

$conn = mysqli_connect($server, $uname, $pwd, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();

// Base URL for absolute path references (fixes sidebar links in subdirectories)
$base_url = '/student_performance';
?>

