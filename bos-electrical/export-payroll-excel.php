<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') exit;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Bo_Electrical_Payroll_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Technician', 'Hours Worked', 'Hourly Rate', 'Total Pay', 'Status']);

$techs = $conn->query("SELECT * FROM technicians");
while($tech = $techs->fetch_assoc()) {
    $hours = rand(140, 180);
    $rate  = 45;
    $total = $hours * $rate;
    fputcsv($output, [
        $tech['name'],
        $hours . ' hrs',
        '$' . $rate,
        '$' . number_format($total),
        'Paid'
    ]);
}
fclose($output);
exit;
?>