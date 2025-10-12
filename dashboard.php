<?php
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !==true) {
    header("location: login.html");
    exit;
}
?>


<?php
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HSE Student Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="assets/img/hse.png" alt="Logo">
            <h3>HSE Learning portal</h3>
        </div>
        <a href="#"> Dashboard</a>
        <a href="#"> Courses</a>
        <a href="#"> Assignments</a>
        <a href="#"> Results</a>
        <a href="logout.php"> Logout</a>
    </div>

    <!-- Top Navigation Bar -->
    <div class="topbar">
        <h2>Welcome to HSE Student Dashboard</h2>
        <div class="user">
            <?php echo $_SESSION['email']; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Hello, <?php echo $_SESSION['email']; ?> 👋</h1>
        <p>Welcome to your learning dashboard. You can access your courses, assignments, and results here.</p>
    </div>
</body>
</html>