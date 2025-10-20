<?php
session_start();
require_once 'config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo "<script>alert('Please enter your email.'); window.location='verify_email.php';</script>";
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $newToken = random_int(100000, 999999); // 6-digit code

        // Update token in database
        $update = $conn->prepare("UPDATE students SET token = ? WHERE email = ?");
        $update->bind_param("is", $newToken, $email);

        if ($update->execute()) {
            // Prepare verification link and email content
            $link = "https://johnnjoroge.eagletechafrica.com/verify_email.php?token=$newToken";
            $subject = "Resend Verification - QHSE LMS";
            $message = "Hi, here is your new verification code: $newToken\n\nClick below to verify:\n$link";

            // Send email
            if (mail($email, $subject, $message)) {
                echo "<script>
                        alert('Verification link resent successfully. Check your inbox.');
                        window.location='verify_email.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Error sending email. Please try again later.');
                        window.location='verify_email.php';
                      </script>";
            }
        } else {
            echo "<script>alert('Could not update token. Try again later.'); window.location='verify_email.php';</script>";
        }

        $update->close();
    } else {
        echo "<script>alert('Email not found. Please register first.'); window.location='register.html';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request.'); window.location='verify_email.php';</script>";
}
?>