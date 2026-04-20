<?php 
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: customer-portal.php"); exit;
}

// Handle all actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_technician'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $rate = floatval($_POST['hourly_rate']);
        $conn->query("INSERT INTO technicians (name, phone, hourly_rate, status) VALUES ('$name', '$phone', $rate, 'Available')");
    }

    if (isset($_POST['record_attendance'])) {
        $id = intval($_POST['tech_id']);
        $hours = floatval($_POST['hours']);
        $conn->query("UPDATE technicians SET total_hours = total_hours + $hours WHERE id = $id");
    }

    if (isset($_POST['update_details'])) {
        $id = intval($_POST['tech_id']);
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $rate = floatval($_POST['hourly_rate']);
        $conn->query("UPDATE technicians SET name='$name', phone='$phone', hourly_rate=$rate WHERE id=$id");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll & Attendance Management - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #e2e8f0; }
        .card { background: #1e2937; }
        table th { background: #334155; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">Payroll & Attendance Management</h2>

    <!-- Add New Technician -->
    <div class="card p-4 mb-4">
        <h4>Add New Technician</h4>
        <form method="POST" class="row g-3">
            <input type="hidden" name="add_technician" value="1">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="hourly_rate" step="0.01" class="form-control" placeholder="Hourly Rate ($)" value="45" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Add Technician</button>
            </div>
        </form>
    </div>

    <!-- All Technicians - Editable -->
    <div class="card p-4">
        <h4>Technicians - Edit Details & Record Attendance</h4>
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Hourly Rate</th>
                    <th>Total Hours</th>
                    <th>Total Pay</th>
                    <th>Record Attendance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $result = $conn->query("SELECT * FROM technicians ORDER BY name");
            while($tech = $result->fetch_assoc()):
                $total_pay = $tech['total_hours'] * $tech['hourly_rate'];
            ?>
                <tr>
                    <!-- Edit Form -->
                    <form method="POST">
                        <input type="hidden" name="update_details" value="1">
                        <input type="hidden" name="tech_id" value="<?php echo $tech['id']; ?>">

                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($tech['name']); ?>" class="form-control form-control-sm"></td>
                        <td><input type="text" name="phone" value="<?php echo $tech['phone']; ?>" class="form-control form-control-sm"></td>
                        <td>
                            <input type="number" name="hourly_rate" step="0.01" value="<?php echo $tech['hourly_rate']; ?>" class="form-control form-control-sm" style="width:100px">
                        </td>
                        <td><?php echo $tech['total_hours']; ?> hrs</td>
                        <td><strong>$<?php echo number_format($total_pay, 2); ?></strong></td>

                        <!-- Record Attendance -->
                        <td>
                            <input type="hidden" name="record_attendance" value="1">
                            <input type="number" name="hours" step="0.5" placeholder="Hours today" class="form-control form-control-sm" style="width:110px">
                            <button type="submit" class="btn btn-sm btn-primary mt-1">Record</button>
                        </td>

                        <td>
                            <button type="submit" class="btn btn-sm btn-warning">Save Changes</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="admin-dashboard.php" class="btn btn-secondary">← Back to Admin Dashboard</a>
        <a href="export-payroll-pdf.php" class="btn btn-success">Export Payroll PDF</a>
        <a href="export-payroll-excel.php" class="btn btn-success">Export Payroll Excel</a>
    </div>
</div>
</body>
</html>