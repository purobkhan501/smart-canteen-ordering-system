<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = isset($_POST['role']) ? $_POST['role'] : 'student';

    // Check if username or email already exists
    $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Username or Email already exists! Please try a different one.'); window.history.back();</script>";
        exit();
    }

    // Insert user without password hashing, as the login.php currently checks plain text passwords
    $sql = "INSERT INTO users (username, password, role, name, email) VALUES ('$username', '$password', '$role', '$name', '$email')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Registration successful! You can now login.'); window.location.href='../student/login.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error during registration: " . mysqli_error($conn) . "'); window.history.back();</script>";
        exit();
    }
} else {
    // Redirect if accessed directly without POST
    header("Location: ../student/register.php");
    exit();
}
?>
