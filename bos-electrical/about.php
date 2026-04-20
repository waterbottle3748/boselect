<?php include 'db.php'; session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background: #f8f9fa; }</style>
</head>
<body>
<?php include 'nav.php'; ?>  <!-- Create nav.php or copy navbar from index.php -->

<div class="container py-5">
    <h2 class="text-center mb-4">About Bo's Electrical Services</h2>
    <p>Bo's Electrical is a medium to large sized electrical contracting company operating in Trinidad for several years. Started as a small sole-trader providing residential services, we have expanded into commercial jobs and a wide variety of electrical services.</p>
    
    <h4>Our Mission</h4>
    <p>To deliver reliable, safe, and innovative electrical solutions with integrity and excellence.</p>
    
    <h4>Core Values</h4>
    <div class="row text-center">
        <div class="col-md-3"><div class="card p-3">Safety</div></div>
        <div class="col-md-3"><div class="card p-3">Reliability</div></div>
        <div class="col-md-3"><div class="card p-3">Innovation</div></div>
        <div class="col-md-3"><div class="card p-3">Excellence</div></div>
    </div>
</div>
</body>
</html>