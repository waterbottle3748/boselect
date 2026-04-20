<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') exit;

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bo's Electrical - Payroll Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; color: #0d6efd; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { background-color: #0d6efd; color: white; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bo's Electrical Services</h1>
        <p><strong>Payroll Report - October 2026</strong><br>Generated on: <?php echo date('d F Y'); ?></p>
    </div>

    <table>
        <tr>
            <th>Technician</th>
            <th>Hours Worked</th>
            <th>Hourly Rate</th>
            <th>Total Pay</th>
            <th>Status</th>
        </tr>
        <?php
        $techs = $conn->query("SELECT * FROM technicians");
        while($tech = $techs->fetch_assoc()):
            $hours = rand(140, 180);
            $rate  = 45;
            $total = $hours * $rate;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($tech['name']); ?></td>
                <td><?php echo $hours; ?> hrs</td>
                <td>$<?php echo $rate; ?></td>
                <td>$<?php echo number_format($total); ?></td>
                <td>Paid</td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p style="text-align:center; margin-top:40px; font-size:12px;">
        Confidential • Bo's Electrical Services • Trinidad & Tobago
    </p>
</body>
</html>
<?php
$html = ob_get_clean();

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="Bo_Electrical_Payroll_' . date('Y-m-d') . '.pdf"');
echo $html;
exit;
?>