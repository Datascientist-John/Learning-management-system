<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

    // Check for valid email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT sid FROM students WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();


    if ($check->num_rows > 0) {
        die("Email already registered. You can <a href='login.php'>login here</a>.</p>");
    }
    $check->close();

    // Insert user data
    $stmt = $conn->prepare("INSERT INTO students (firstname, lastname, email, password, gender, dob) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstname, $lastname, $email, $password, $gender, $dob);

    if ($stmt->execute()) {
        echo "<h3>Registration successful!</h3>";
        echo "<p><a href='login.php'>Click here to login</a></p>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>