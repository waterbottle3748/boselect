<?php
// MAMP Settings
$servername = "localhost";
$username   = "root";
$password   = "root";
$dbname     = "bos_electrical";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ← THIS LINE FIXES THE ERROR
$conn->query("SET SESSION sql_mode = ''");

?>