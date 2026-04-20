<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') exit;

if (isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    // Assign to first available technician for demo
    $tech = $conn->query("SELECT id FROM technicians WHERE status='Available' LIMIT 1")->fetch_assoc();
    if ($tech) {
        $stmt = $conn->prepare("UPDATE jobs SET status='Assigned', technician_id=? WHERE id=?");
        $stmt->bind_param("ii", $tech['id'], $job_id);
        $stmt->execute();
    }
}
header("Location: admin-dashboard.php");
exit;
?>