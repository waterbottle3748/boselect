<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Services - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'nav.php'; ?>

<div class="container py-5">
    <h2 class="text-center mb-5">Our Electrical Services</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>Residential & Commercial Installation</h5>
                    <p>Complete wiring and installation services for homes and businesses.</p>
                    <a href="customer-portal.php" class="btn btn-primary">Request Quote</a>
                </div>
            </div>
        </div>
        <!-- Repeat similar cards for: Maintenance & Emergency Repair, Solar Solutions, Smart Lighting, Generator Installation, Industrial Electrical -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>Maintenance & Emergency Repair</h5>
                    <p>24/7 response for electrical issues.</p>
                    <a href="customer-portal.php" class="btn btn-primary">Request Quote</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>Renewable Energy & Solar</h5>
                    <p>Eco-friendly solar panel integration.</p>
                    <a href="customer-portal.php" class="btn btn-primary">Request Quote</a>
                </div>
            </div>
        </div>
        <!-- Add the other 3 cards similarly -->
    </div>
</div>
</body>
</html>