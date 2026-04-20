<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - Bo's Electrical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'nav.php'; ?>

<div class="container py-5">
    <h2 class="text-center">Get In Touch</h2>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form>
                <div class="mb-3"><input type="text" class="form-control" placeholder="Name"></div>
                <div class="mb-3"><input type="email" class="form-control" placeholder="Email"></div>
                <div class="mb-3"><textarea class="form-control" rows="5" placeholder="Message"></textarea></div>
                <button type="submit" class="btn btn-primary w-100">Send Message</button>
            </form>
            <p class="mt-4 text-center">Phone: 868-555-1234 | Email: info@bos-electrical.com</p>
        </div>
    </div>
</div>
</body>
</html>