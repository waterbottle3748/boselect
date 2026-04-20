<?php 
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: customer-portal.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports Page - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #0f172a; color: #e2e8f0; }
        .card { background: #1e2937; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="text-center mb-4">Reports Page</h2>
    
    <!-- Export Buttons -->
    <div class="text-end mb-4">
        <a href="export-pdf.php" class="btn btn-success me-2">Export Full Report as PDF</a>
        <a href="export-excel.php" class="btn btn-success">Export Full Report as Excel</a>
    </div>

    <!-- Monthly Job Summary Chart -->
    <div class="card p-4 mb-5">
        <h4>Monthly Job Summary</h4>
        <canvas id="monthlyChart" height="120"></canvas>
    </div>

    <script>
        // Fixed query data will be loaded here
        const ctx = document.getElementById('monthlyChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Total Jobs',
                    data: [45,52,38,67,55,72,48,61,50,78,65,82],   // You can make this dynamic later
                    backgroundColor: '#3b82f6'
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>

    <!-- Technician Job Report -->
    <div class="card p-4 mb-5">
        <h4>Technician Job Report</h4>
        <table class="table table-dark table-striped">
            <thead><tr><th>Technician</th><th>Jobs Completed</th><th>Hours Worked (est.)</th><th>Rating</th></tr></thead>
            <tbody>
            <?php
            $tech_report = $conn->query("SELECT t.name, COUNT(j.id) as jobs_completed 
                                         FROM technicians t 
                                         LEFT JOIN jobs j ON t.id = j.technician_id AND j.status = 'Completed' 
                                         GROUP BY t.id");
            while($row = $tech_report->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['jobs_completed']; ?></td>
                    <td>120</td>
                    <td>4.8 / 5</td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Customer Service History -->
    <div class="card p-4">
        <h4>Customer Service History</h4>
        <table class="table table-dark table-striped">
            <thead><tr><th>Customer</th><th>Service</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php
            $history = $conn->query("SELECT u.full_name, j.service_type, j.preferred_date, j.status 
                                     FROM jobs j 
                                     JOIN users u ON j.customer_id = u.id 
                                     ORDER BY j.created_at DESC LIMIT 15");
            while($row = $history->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                    <td><?php echo $row['preferred_date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>