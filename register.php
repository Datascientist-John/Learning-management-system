<?php

ob_start(); // Start output buffering
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!empty($_POST['sapien-check'])) {
        exit('Spam detected');
    }

    // Sanitize inputs
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname  = htmlspecialchars(trim($_POST['lastname']));
    $email     = htmlspecialchars(trim($_POST['email']));
    $gender    = $_POST['gender'];
    $dob       = $_POST['dob'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate fields
    if (empty($firstname) || empty($lastname) || empty($email) || empty($_POST['password']) || empty($gender) || empty($dob)) {
        die("All fields are required.");
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT sid FROM students WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
    // Close the prepared statement
    $check->close();

    // Redirect to login page with a notice
    header("Location: login.html?already=1");
    exit();
}
    $check->close();

    // Generate verification data
    $token = rand(100000, 999999);
    $email_verified = 0;

    // ✅ Corrected INSERT query (8 columns, 8 placeholders)
    $stmt = $conn->prepare("INSERT INTO students (firstname, lastname, email, password, gender, dob, token, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssii", $firstname, $lastname, $email, $password, $gender, $dob, $token, $email_verified);

    if ($stmt->execute()) {
        // Send verification email
        $verify_link = "https://johnnjoroge.eagletechafrica.com/verify_email.php?token=" . $token;
        $subject = "Verify Your Email - QHSE LMS";
        $message = "Hi $firstname,\n\nPlease verify your account using this code: $token\n\nOr click the link below:\n$verify_link\n\nThank you,\nRITA Africa LMS";

        mail($email, $subject, $message);

        // Redirect to login page after success
        header("Location: login.html?registered=1");
        exit();
        // For already registered email
        header("Location: login.html?already=1");
        exit();

        // For successful email verification (in verify_email.php)
        header("Location: login.html?verified=1");
        exit();

    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
ob_end_flush(); // Send the buffered output
?>