<?php 
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: customer-portal.php"); exit;
}

// Fetch pending jobs
$pending_jobs = $conn->query("SELECT j.id, u.full_name, j.service_type, j.service_address, j.preferred_date, j.status 
                              FROM jobs j JOIN users u ON j.customer_id = u.id 
                              WHERE j.status = 'Pending' LIMIT 10");

$technicians = $conn->query("SELECT * FROM technicians");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main Admin Dashboard - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #e2e8f0; }
        .card { background: #1e2937; border: none; }
        .technician-card { color: white !important; }   /* ← This makes text white */
    </style>
</head>
<body>
<div class="container-fluid p-4">
    <h1 class="text-center mb-4 text-white">MAIN ADMIN DASHBOARD</h1>
    
    <!-- Statistic Cards -->
    <div class="row g-3 mb-5">
        <div class="col-md-3"><div class="card p-4 text-center"><h5>Active Jobs Today</h5><h2 class="text-primary">12</h2></div></div>
        <div class="col-md-3"><div class="card p-4 text-center"><h5>Pending Appointments</h5><h2 class="text-warning">8</h2></div></div>
        <div class="col-md-3"><div class="card p-4 text-center"><h5>Revenue This Month</h5><h2 class="text-success">$24,500</h2></div></div>
        <div class="col-md-3"><div class="card p-4 text-center"><h5>Total Technicians On Duty</h5><h2 class="text-info">15</h2></div></div>
    </div>

    <div class="row">
        <!-- Pending Jobs -->
        <div class="col-lg-7">
            <h4 class="mb-3 text-white">Pending Job Requests</h4>
            <div class="card p-3">
                <table class="table table-dark table-striped">
                    <thead><tr><th>Job ID</th><th>Customer</th><th>Service</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while($job = $pending_jobs->fetch_assoc()): ?>
                        <tr>
                            <td>J-00<?php echo $job['id']; ?></td>
                            <td><?php echo htmlspecialchars($job['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($job['service_type']); ?></td>
                            <td><?php echo $job['preferred_date']; ?></td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td><a href="assign-job.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-success">Assign</a></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Assignable Technicians - FIXED WHITE TEXT -->
        <div class="col-lg-5">
            <h4 class="mb-3 text-white">Assignable Technicians</h4>
            <div class="card p-3">
                <?php while($tech = $technicians->fetch_assoc()): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 border-bottom technician-card">
                        <div>
                            <strong style="color: white;"><?php echo htmlspecialchars($tech['name']); ?></strong><br>
                            <small style="color: #cbd5e1;"><?php echo $tech['phone']; ?> - <?php echo $tech['status']; ?></small>
                        </div>
                        <button onclick="alert('Technician scheduled! (Demo)')" class="btn btn-sm btn-outline-light">Schedule</button>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="reports.php" class="btn btn-info">View Reports</a>
        <a href="payroll.php" class="btn btn-warning">Payroll Management</a>
        <a href="materials-inventory.php" class="btn btn-info">Materials & Inventory</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>
</body>
</html>