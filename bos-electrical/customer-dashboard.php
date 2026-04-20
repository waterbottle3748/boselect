<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: customer-portal.php");
    exit;
}
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container"><a class="navbar-brand" href="index.php">Bo's Electrical</a> <a href="logout.php" class="btn btn-outline-light">Logout</a></div>
</nav>

<div class="container py-5">
    <h2>Welcome, <?php echo $_SESSION['full_name']; ?></h2>
    <p>Your recent service requests will appear here.</p>
    <!-- You can query and display user's jobs here in a table -->
    <a href="customer-portal.php" class="btn btn-primary">Make New Request</a>
</div>
</body>
</html>