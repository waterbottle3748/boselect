<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') exit;

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bo's Electrical Services - Full Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #1e2937; }
        h1 { text-align: center; color: #0d6efd; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #333; padding: 10px; text-align: left; }
        th { background-color: #0d6efd; color: white; }
        .note { text-align: center; color: #d32f2f; font-weight: bold; margin: 30px 0; }
    </style>
</head>
<body>
    <h1>Bo's Electrical Services</h1>
    <p style="text-align:center;"><strong>Full Management Report</strong><br>
    Generated on: <?php echo date('d F Y - H:i'); ?></p>

    <h2>Monthly Job Summary</h2>
    <table>
        <tr><th>Month</th><th>Total Jobs</th></tr>
        <?php
        $monthly = $conn->query("SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as total 
                                 FROM jobs GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY MIN(created_at)");
        while($row = $monthly->fetch_assoc()):
        ?>
            <tr><td><?php echo $row['month']; ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>

    <h2>Technician Performance</h2>
    <table>
        <tr><th>Technician</th><th>Jobs Completed</th></tr>
        <?php
        $techs = $conn->query("SELECT t.name, COUNT(j.id) as completed FROM technicians t LEFT JOIN jobs j ON t.id = j.technician_id AND j.status = 'Completed' GROUP BY t.id");
        while($row = $techs->fetch_assoc()):
        ?>
            <tr><td><?php echo htmlspecialchars($row['name']); ?></td><td><?php echo $row['completed']; ?></td></tr>
        <?php endwhile; ?>
    </table>

    <div class="note">
        ✅ To save as proper PDF:<br>
        Press Command + P → Choose "Save as PDF"
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="Bo_Electrical_Full_Report_' . date('Y-m-d') . '.html"');
echo $html;
exit;
?>