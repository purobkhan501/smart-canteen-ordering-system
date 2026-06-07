<?php
require_once '../includes/auth.php';
require_role('admin');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = 'staff';
    
    // Check if username or email already exists
    $check_sql = "SELECT user_id FROM users WHERE username = '$username' OR email = '$email'";
    $check_res = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_res) > 0) {
        // Duplicate
        header("Location: dashboard.php?error=duplicate#users");
        exit();
    }
    
    // Insert new staff (plain password for consistency – in production use password_hash)
    $sql = "INSERT INTO users (username, password, role, name, email, is_deleted, created_at) 
            VALUES ('$username', '$password', '$role', '$name', '$email', 0, NOW())";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php?success=staff_added#users");
    } else {
        header("Location: dashboard.php?error=add_failed#users");
    }
} else {
    header("Location: dashboard.php#users");
}
exit();
?>