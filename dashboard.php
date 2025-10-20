<?php
session_start();

// Check if user is logged in by verifying email session variable

if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Redirect to login page (use login.php if that’s your login file)
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to My Learners Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
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
        <h2>Welcome to HSE & Quality Student Dashboard</h2>
        <div class="user">
            <?php echo $_SESSION['email']; ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="blog-section">
    <h2>Latest Articles</h2>
    <article class="blog-post">
        <h3>Fire Risk Reduction Rules, 2007 (Kenya)</h3>
        <p>Fire safety starts with awareness. Discover how proactive planning and training can prevent small sparks from becoming disasters.</p>
        <a href="https://www.google.com/search?q=fire+risk+reduction+rules+2007+kenya+law" target="_blank">Read More Kenya Fire risk reduction rules→</a>
    </article>

    <article class="blog-post">
        <h3>First Aid in the Workplace regulations 2024 (Kenya)</h3>
        <p>Kenya’s <em>Occupational Safety and Health (First Aid in the Workplace) Regulations, 2024</em> 
        (Legal Notice No. 79 of 2024) outline mandatory requirements for all workplaces. </p>
        <a href="https://new.kenyalaw.org/akn/ke/act/ln/2024/79/eng@2024-06-07" target="_blank">Read More Kenya First Aid Rules →</a>
    </article>

    <article class="blog-post">
        <h3>Personal Protective Equipment (PPE)</h3>
        <p>Choosing the right PPE is critical for every industry. Here’s how to select and use protective gear effectively.</p>
        <a href="https://www.google.com/search?q=how+to+select+the+right+PPE+according+to+OSHA+act+2007" target="_blank">Read More PPE Selection→</a>
    </article>
</div>
</body>
</html>