<?php

require_once 'config/db_config.php';


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize inputs
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];

    /*===$sql= "INSERT INTO students (firstname, lastname,email, password,gender, dob)
           VALUES('$firstname', '$lastname','$email', '$password','$gender', '$dob')";==*/

    // Validate fields
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($gender) || empty($dob)) {
        die("All fields are required.");
    }

    // Check for valid email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Email already registered. Please use a different email.");
    }
    $check->close();

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data
    $stmt = $conn->prepare("INSERT INTO students (firstname, lastname, email, password, gender, dob) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstname, $lastname, $email, $hashed_password, $gender, $dob);

    if ($stmt->execute()) {
        echo "<h3>Registration successful! 🎉</h3>";
        echo "<p><a href='login.php'>Click here to login</a></p>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>