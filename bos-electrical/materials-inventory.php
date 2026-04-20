
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: customer-portal.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = $error_msg = '';

// ====================== HANDLE ALL POST ACTIONS ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

    // Add Building
    if (isset($_POST['add_building'])) {
        $name = trim($_POST['building_name']);
        if ($name) {
            $stmt = $conn->prepare("INSERT INTO buildings (building_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute() ? $success_msg = "Building added successfully!" : $error_msg = "Error adding building.";
        }
    }

    // Delete Building
    if (isset($_POST['delete_building'])) {
        $id = (int)$_POST['building_id'];
        $stmt = $conn->prepare("DELETE FROM buildings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute() ? $success_msg = "Building deleted." : $error_msg = "Cannot delete building.";
    }

    // Add Apartment
    if (isset($_POST['add_apartment'])) {
        $bid = (int)$_POST['building_id'];
        $apt = trim($_POST['apt_number']);
        if ($bid && $apt) {
            $stmt = $conn->prepare("INSERT INTO apartments (building_id, apt_number) VALUES (?, ?)");
            $stmt->bind_param("is", $bid, $apt);
            $stmt->execute() ? $success_msg = "Apartment added!" : $error_msg = "Error adding apartment.";
        }
    }

    // Delete Apartment
    if (isset($_POST['delete_apartment'])) {
        $id = (int)$_POST['apartment_id'];
        $stmt = $conn->prepare("DELETE FROM apartments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute() ? $success_msg = "Apartment deleted." : $error_msg = "Error deleting apartment.";
    }

    // Add Material
    if (isset($_POST['add_material'])) {
        $name = trim($_POST['material_name']);
        $code = trim($_POST['material_code']);
        $stock = (int)$_POST['current_stock'];
        $req = (int)$_POST['required_stock'];
        if ($name && $code) {
            $stmt = $conn->prepare("INSERT INTO materials (material_name, material_code, current_stock, required_stock, total_used) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssii", $name, $code, $stock, $req);
            $stmt->execute() ? $success_msg = "Material added!" : $error_msg = "Error adding material.";
        }
    }

    // Update Stock
    if (isset($_POST['update_stock'])) {
        $id = (int)$_POST['material_id'];
        $cur = (int)$_POST['current_stock'];
        $req = (int)$_POST['required_stock'];
        $stmt = $conn->prepare("UPDATE materials SET current_stock = ?, required_stock = ? WHERE id = ?");
        $stmt->bind_param("iii", $cur, $req, $id);
        $stmt->execute();
        $success_msg = "Stock updated successfully!";
    }

    // Delete Material
    if (isset($_POST['delete_material'])) {
        $id = (int)$_POST['material_id'];
        $stmt = $conn->prepare("DELETE FROM materials WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute() ? $success_msg = "Material removed from list." : $error_msg = "Cannot delete material.";
    }

    // Issue Material
    if (isset($_POST['issue_material'])) {
        $apt_id = (int)$_POST['apartment_id'];
        $mat_id = (int)$_POST['material_id'];
        $qty = (int)$_POST['quantity'];
        $notes = trim($_POST['notes'] ?? '');

        if ($apt_id > 0 && $mat_id > 0 && $qty > 0) {
            $check = $conn->prepare("SELECT current_stock FROM materials WHERE id = ?");
            $check->bind_param("i", $mat_id);
            $check->execute();
            $row = $check->get_result()->fetch_assoc();

            if ($row && $row['current_stock'] >= $qty) {
                $stmt = $conn->prepare("INSERT INTO material_issues (apartment_id, material_id, quantity, notes, issued_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiisi", $apt_id, $mat_id, $qty, $notes, $_SESSION['user_id']);
                $stmt->execute();

                $update = $conn->prepare("UPDATE materials SET current_stock = current_stock - ?, total_used = total_used + ? WHERE id = ?");
                $update->bind_param("iii", $qty, $qty, $mat_id);
                $update->execute();

                $success_msg = "Material issued successfully!";
            } else {
                $error_msg = "Not enough stock available!";
            }
        }
    }

    // Request More Materials
    if (isset($_POST['request_more'])) {
        $mat_id = (int)$_POST['material_id'];
        $qty = (int)$_POST['request_qty'];
        $reason = trim($_POST['reason']);
        if ($mat_id && $qty && $reason) {
            $notes = "REQUEST MORE: " . $reason;
            $stmt = $conn->prepare("INSERT INTO material_issues (apartment_id, material_id, quantity, notes, issued_by) VALUES (NULL, ?, ?, ?, ?)");
            $stmt->bind_param("iisi", $mat_id, $qty, $notes, $_SESSION['user_id']);
            $stmt->execute();
            $success_msg = "Request submitted successfully!";
        }
    }
}

// ====================== VIEW LOGIC ======================
$view = 'main';
$building_id = isset($_GET['building_id']) ? (int)$_GET['building_id'] : 0;
$apartment_id = isset($_GET['apartment_id']) ? (int)$_GET['apartment_id'] : 0;

if ($apartment_id > 0) {
    $view = 'apartment_detail';
} elseif ($building_id > 0) {
    $view = 'building_apartments';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Materials & Property Management - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #e2e8f0; }
        .card { background: #1e2937; border: none; }
        h2, h4, h5, label, th, td, a { color: #ffffff !important; }
        .low-stock { background: #7f1d1d !important; }
        .form-control, .form-select { background: #334155; color: #ffffff; border: 1px solid #475569; }
        .clickable:hover { text-decoration: underline; cursor: pointer; }
        .alert { margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container py-5">

    <h2 class="mb-4">Bo's Electrical - Admin Dashboard</h2>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if ($view === 'apartment_detail'): ?>
        <!-- ====================== APARTMENT DETAIL VIEW ====================== -->
        <?php
        $apt_info = $conn->prepare("SELECT a.apt_number, b.building_name, b.id as building_id 
                                    FROM apartments a 
                                    JOIN buildings b ON a.building_id = b.id 
                                    WHERE a.id = ?");
        $apt_info->bind_param("i", $apartment_id);
        $apt_info->execute();
        $apt = $apt_info->get_result()->fetch_assoc();
        $apt_info->close();
        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><?= htmlspecialchars($apt['building_name'] ?? 'Building') ?> — Apt <?= htmlspecialchars($apt['apt_number'] ?? '') ?> 
                <small class="text-muted">(Materials Used & Still Needed)</small>
            </h4>
            <div>
                <a href="materials-inventory.php?building_id=<?= $apt['building_id'] ?? 0 ?>" class="btn btn-secondary">← Back to Apartments</a>
                <a href="materials-inventory.php" class="btn btn-primary ms-2">← Main Dashboard</a>
            </div>
        </div>

        <!-- Materials Used + Still Needed -->
        <div class="card p-4 mb-4">
            <h5>Materials Used & Still Needed</h5>
            <table class="table table-dark table-striped">
                <thead>
                    <tr><th>Material</th><th>Total Used</th><th>Still Needed</th></tr>
                </thead>
                <tbody>
                <?php
                $summary = $conn->prepare("SELECT m.material_name, m.required_stock,
                                           COALESCE(SUM(i.quantity), 0) as total_used 
                                           FROM materials m 
                                           LEFT JOIN material_issues i ON i.material_id = m.id AND i.apartment_id = ? 
                                           GROUP BY m.id, m.material_name, m.required_stock 
                                           ORDER BY total_used DESC");
                $summary->bind_param("i", $apartment_id);
                $summary->execute();
                $sum_res = $summary->get_result();
                while ($s = $sum_res->fetch_assoc()):
                    $needed = max(0, $s['required_stock'] - $s['total_used']);
                ?>
                    <tr>
                        <td><?= htmlspecialchars($s['material_name']) ?></td>
                        <td><strong><?= $s['total_used'] ?></strong></td>
                        <td><span class="badge bg-info"><?= $needed ?></span></td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($sum_res->num_rows == 0): ?>
                    <tr><td colspan="3">No materials configured yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Detailed Issue History -->
        <div class="card p-4">
            <h5>Detailed Issue History</h5>
            <table class="table table-dark table-striped">
                <thead><tr><th>Date</th><th>Material</th><th>Qty Issued</th><th>Notes</th></tr></thead>
                <tbody>
                <?php
                $history = $conn->prepare("SELECT i.created_at, m.material_name, i.quantity, i.notes 
                                           FROM material_issues i 
                                           JOIN materials m ON i.material_id = m.id 
                                           WHERE i.apartment_id = ? 
                                           ORDER BY i.created_at DESC");
                $history->bind_param("i", $apartment_id);
                $history->execute();
                $hist_res = $history->get_result();
                while ($h = $hist_res->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($h['created_at']) ?></td>
                        <td><?= htmlspecialchars($h['material_name']) ?></td>
                        <td><?= $h['quantity'] ?></td>
                        <td><?= htmlspecialchars($h['notes'] ?? '') ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($hist_res->num_rows == 0): ?>
                    <tr><td colspan="4">No materials issued to this apartment yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($view === 'building_apartments'): ?>
        <!-- ====================== BUILDING APARTMENTS LIST ====================== -->
        <?php
        $bname = $conn->query("SELECT building_name FROM buildings WHERE id = $building_id LIMIT 1")->fetch_assoc()['building_name'] ?? 'Building';
        ?>
        <h4><?= htmlspecialchars($bname) ?> - Apartments</h4>
        <a href="materials-inventory.php" class="btn btn-secondary mb-3">← Back to All Buildings</a>

        <div class="card p-4">
            <table class="table table-dark table-striped">
                <thead><tr><th>Apartment Number</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $apts = $conn->prepare("SELECT id, apt_number FROM apartments WHERE building_id = ? ORDER BY apt_number");
                $apts->bind_param("i", $building_id);
                $apts->execute();
                $result = $apts->get_result();
                while ($a = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td>
                            <a href="?apartment_id=<?= $a['id'] ?>&building_id=<?= $building_id ?>" class="text-white clickable">
                                <?= htmlspecialchars($a['apt_number']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="?apartment_id=<?= $a['id'] ?>&building_id=<?= $building_id ?>" class="btn btn-sm btn-info">View Materials Used & Needed</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                    <tr><td colspan="2">No apartments added to this building yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- ====================== MAIN DASHBOARD ====================== -->

        <!-- Buildings -->
        <div class="card p-4 mb-4">
            <h4>Buildings</h4>
            <form method="POST" class="row g-3 mb-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="add_building" value="1">
                <div class="col-md-8"><input type="text" name="building_name" class="form-control" placeholder="Building Name" required></div>
                <div class="col-md-4"><button type="submit" class="btn btn-primary w-100">Add Building</button></div>
            </form>

            <table class="table table-dark table-striped">
                <thead><tr><th>Building Name</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $blds = $conn->query("SELECT * FROM buildings ORDER BY building_name");
                while ($b = $blds->fetch_assoc()):
                ?>
                    <tr>
                        <td>
                            <a href="?building_id=<?= $b['id'] ?>" class="text-white clickable"><?= htmlspecialchars($b['building_name']) ?></a>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="delete_building" value="1">
                                <input type="hidden" name="building_id" value="<?= $b['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this building and all its apartments?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Apartment -->
        <div class="card p-4 mb-4">
            <h4>Add Apartment</h4>
            <form method="POST" class="row g-3 mb-4">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="add_apartment" value="1">
                <div class="col-md-5">
                    <select name="building_id" class="form-select" required>
                        <option value="">Select Building</option>
                        <?php
                        $buildings = $conn->query("SELECT * FROM buildings ORDER BY building_name");
                        while ($b = $buildings->fetch_assoc()):
                        ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['building_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4"><input type="text" name="apt_number" class="form-control" placeholder="Apt Number (e.g. 101)" required></div>
                <div class="col-md-3"><button type="submit" class="btn btn-success w-100">Add Apartment</button></div>
            </form>
        </div>

        <!-- Add New Material -->
        <div class="card p-4 mb-4">
            <h4>Add New Material</h4>
            <form method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="add_material" value="1">
                <div class="col-md-4"><input type="text" name="material_name" class="form-control" placeholder="Material Name" required></div>
                <div class="col-md-3"><input type="text" name="material_code" class="form-control" placeholder="Code" required></div>
                <div class="col-md-2"><input type="number" name="current_stock" class="form-control" placeholder="Current Stock" min="0" value="0" required></div>
                <div class="col-md-2"><input type="number" name="required_stock" class="form-control" placeholder="Required Stock" min="0" value="10" required></div>
                <div class="col-md-1"><button type="submit" class="btn btn-success w-100">Add</button></div>
            </form>
        </div>

        <!-- Current Materials Stock -->
        <div class="card p-4 mb-4">
            <h4>Current Materials Stock</h4>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Material</th><th>Code</th><th>Current</th><th>Required</th><th>Total Used</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $mats = $conn->query("SELECT * FROM materials ORDER BY material_name");
                while ($m = $mats->fetch_assoc()):
                    $low = $m['current_stock'] < $m['required_stock'];
                ?>
                    <tr class="<?= $low ? 'low-stock' : '' ?>">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="update_stock" value="1">
                            <input type="hidden" name="material_id" value="<?= $m['id'] ?>">
                            <td><?= htmlspecialchars($m['material_name']) ?></td>
                            <td><?= htmlspecialchars($m['material_code']) ?></td>
                            <td><input type="number" name="current_stock" value="<?= $m['current_stock'] ?>" class="form-control form-control-sm" min="0"></td>
                            <td><input type="number" name="required_stock" value="<?= $m['required_stock'] ?>" class="form-control form-control-sm" min="0"></td>
                            <td><?= $m['total_used'] ?></td>
                            <td><span class="badge <?= $low ? 'bg-danger' : 'bg-success' ?>"><?= $low ? 'LOW' : 'OK' ?></span></td>
                            <td>
                                <button type="submit" class="btn btn-sm btn-warning">Update</button>
                                <button type="button" class="btn btn-sm btn-danger ms-2" 
                                        onclick="if(confirm('Remove this material permanently from the list?')) document.getElementById('del<?= $m['id'] ?>').submit();">Remove</button>
                            </td>
                        </form>
                        <form id="del<?= $m['id'] ?>" method="POST" style="display:none;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="delete_material" value="1">
                            <input type="hidden" name="material_id" value="<?= $m['id'] ?>">
                        </form>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Issue Materials -->
        <div class="card p-4 mb-4">
            <h4>Issue Materials to Apartment</h4>
            <form method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="issue_material" value="1">
                <div class="col-md-4">
                    <select name="apartment_id" class="form-select" required>
                        <option value="">Select Apartment</option>
                        <?php
                        $apts = $conn->query("SELECT a.id, b.building_name, a.apt_number 
                                              FROM apartments a JOIN buildings b ON a.building_id = b.id 
                                              ORDER BY b.building_name, a.apt_number");
                        while ($a = $apts->fetch_assoc()):
                        ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['building_name']) ?> - Apt <?= htmlspecialchars($a['apt_number']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="material_id" class="form-select" required>
                        <option value="">Select Material</option>
                        <?php
                        $mats = $conn->query("SELECT id, material_name, current_stock FROM materials ORDER BY material_name");
                        while ($m = $mats->fetch_assoc()):
                        ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['material_name']) ?> (<?= $m['current_stock'] ?> left)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2"><input type="number" name="quantity" class="form-control" placeholder="Qty" min="1" required></div>
                <div class="col-md-2"><button type="submit" class="btn btn-danger w-100">Issue</button></div>
                <div class="col-12 mt-3"><input type="text" name="notes" class="form-control" placeholder="Notes (optional)"></div>
            </form>
        </div>

        <!-- Request More Materials -->
        <div class="card p-4 mb-4 border-warning">
            <h4 class="text-warning">🚨 Request More Materials</h4>
            <form method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="request_more" value="1">
                <div class="col-md-4">
                    <select name="material_id" class="form-select" required>
                        <option value="">Select Material</option>
                        <?php
                        $mats = $conn->query("SELECT id, material_name FROM materials ORDER BY material_name");
                        while ($m = $mats->fetch_assoc()):
                        ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['material_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2"><input type="number" name="request_qty" class="form-control" placeholder="Quantity Needed" min="1" required></div>
                <div class="col-md-4"><input type="text" name="reason" class="form-control" placeholder="Reason" required></div>
                <div class="col-md-2"><button type="submit" class="btn btn-warning w-100">Submit Request</button></div>
            </form>
        </div>

        <!-- Recent History -->
        <div class="card p-4">
            <h4>Recent History (Last 30 records)</h4>
            <table class="table table-dark table-striped">
                <thead>
                    <tr><th>Date</th><th>Material</th><th>Qty</th><th>Type</th><th>Notes</th></tr>
                </thead>
                <tbody>
                <?php
                $hist = $conn->query("SELECT i.*, m.material_name 
                                      FROM material_issues i 
                                      JOIN materials m ON i.material_id = m.id 
                                      ORDER BY i.created_at DESC LIMIT 30");
                while ($h = $hist->fetch_assoc()):
                    $type = is_null($h['apartment_id']) ? 'REQUEST' : 'ISSUED';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($h['created_at']) ?></td>
                        <td><?= htmlspecialchars($h['material_name']) ?></td>
                        <td><?= $h['quantity'] ?></td>
                        <td><span class="badge <?= $type === 'REQUEST' ? 'bg-warning' : 'bg-danger' ?>"><?= $type ?></span></td>
                        <td><?= htmlspecialchars($h['notes'] ?? '') ?></td>
                    </tr>
                <?php endwhile; ?>
                
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>