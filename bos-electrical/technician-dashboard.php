<?php 
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'technician') {
    header("Location: customer-portal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Technician Dashboard - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #e2e8f0; }
        .card { background: #1e2937; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> (Technician)</h2>
    <p class="lead">Your Assigned Jobs</p>

    <div class="card p-4 mb-4">
        <h4>Current Jobs</h4>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Job ID</th>
                    <th>Service Type</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $jobs = $conn->query("SELECT * FROM jobs WHERE technician_id IS NOT NULL LIMIT 10");
            if ($jobs->num_rows == 0) {
                echo "<tr><td colspan='6' class='text-center'>No jobs assigned yet.</td></tr>";
            }
            while($job = $jobs->fetch_assoc()):
            ?>
                <tr>
                    <td>J-00<?php echo $job['id']; ?></td>
                    <td><?php echo htmlspecialchars($job['service_type']); ?></td>
                    <td><?php echo htmlspecialchars($job['service_address']); ?></td>
                    <td><?php echo $job['preferred_date']; ?></td>
                    <td><?php echo $job['status']; ?></td>
                    <td>
                        <select onchange="updateStatus(<?php echo $job['id']; ?>, this.value)" class="form-select form-select-sm">
                            <option value="In Progress" <?php if($job['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                            <option value="Completed" <?php if($job['status']=='Completed') echo 'selected'; ?>>Mark Completed</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<script>
function updateStatus(jobId, status) {
    alert('Job ' + jobId + ' updated to ' + status + ' (Demo)');
    // In real version this would update the database
}
</script>
</body>
</html>