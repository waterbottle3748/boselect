<?php
include 'db.php';
if (isset($_POST['job_id']) && isset($_POST['status'])) {
    $stmt = $conn->prepare("UPDATE jobs SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $_POST['status'], $_POST['job_id']);
    $stmt->execute();
}
header("Location: technician-dashboard.php");
?>