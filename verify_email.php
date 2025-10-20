<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db_config.php'; // include DB connection

if (isset($_GET['token'])) {
    // Sanitize the token from the URL
    $token = htmlspecialchars(trim($_GET['token']));

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT sid FROM students WHERE token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    // If token exists
    if ($result->num_rows > 0) {
        // Update user as verified
        $update = $conn->prepare("UPDATE students SET email_verified = 1, token = NULL WHERE token = ?");
        $update->bind_param("s", $token);
        $update->execute();

        echo "<script>
                alert('Email verified successfully! You can now log in.');
                window.location='login.html';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('❌ Invalid or expired verification link.');
                window.location='register.php';
              </script>";
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // If no token in URL, show resend form
    echo "
    <h3>Please check your email for the verification link.</h3>
    <form action='resend_verification.php' method='POST'>
        <input type='email' name='email' placeholder='Enter your email to resend link' required>
        <button type='submit'>Resend Verification Email</button>
    </form>";
}
?>