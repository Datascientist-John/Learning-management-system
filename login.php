<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db_config.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');



// Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ✅ 1. Check if email is verified
        if ($row['email_verified'] == 0) {
            echo "<script>
                    alert('Please verify your email before logging in.');
                    window.location='verify_email.php';
                  </script>";
            exit;
        }

        // ✅ 2. (Optional) Check if account is active
        if (isset($row['active']) && $row['active'] == 0) {
            echo "<script>
                    alert('Account suspended. Contact admin.');
                    window.location='login.php';
                  </script>";
            exit;
        }

        // ✅ 3. Verify password
        if (password_verify($password, $row['password'])) {
            // Login successful
            $_SESSION['sid'] = $row['sid'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['email'] = $row['email'];

            echo "<script>
                    alert('Login successful!');
                    window.location='dashboard.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Invalid password.');
                    window.location='login.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Email not found.');
                window.location='login.php';
              </script>";
    }






    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $sql = "SELECT * FROM students WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = "Server error. Please try again later.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION['email'] = $user['email'];
                    // Redirect to dashboard after successful login
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "Email not found.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

