<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') exit;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Bo_Electrical_Full_Report_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Monthly Summary
fputcsv($output, ['=== MONTHLY JOB SUMMARY ===']);
fputcsv($output, ['Month', 'Total Jobs']);
$monthly = $conn->query("SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as total 
                         FROM jobs 
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                         ORDER BY created_at");
while($row = $monthly->fetch_assoc()) {
    fputcsv($output, [$row['month'], $row['total']]);
}

// Technician Performance
fputcsv($output, ['']);
fputcsv($output, ['=== TECHNICIAN PERFORMANCE ===']);
fputcsv($output, ['Technician', 'Jobs Completed']);
$techs = $conn->query("SELECT t.name, COUNT(j.id) as completed 
                       FROM technicians t 
                       LEFT JOIN jobs j ON t.id = j.technician_id AND j.status = 'Completed' 
                       GROUP BY t.id");
while($row = $techs->fetch_assoc()) {
    fputcsv($output, [$row['name'], $row['completed']]);
}

// Full Job List
fputcsv($output, ['']);
fputcsv($output, ['=== ALL JOBS ===']);
fputcsv($output, ['Job ID','Customer','Service Type','Address','Date','Status']);
$jobs = $conn->query("SELECT j.id, u.full_name, j.service_type, j.service_address, j.preferred_date, j.status 
                      FROM jobs j JOIN users u ON j.customer_id = u.id");
while($row = $jobs->fetch_assoc()) {
    fputcsv($output, ['J-00'.$row['id'], $row['full_name'], $row['service_type'], $row['service_address'], $row['preferred_date'], $row['status']]);
}

fclose($output);
exit;
?>