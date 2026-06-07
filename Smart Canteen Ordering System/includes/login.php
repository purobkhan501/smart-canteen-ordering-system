<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $intended_role = isset($_POST['role']) ? $_POST['role'] : '';

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND is_deleted = 0";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        $role = isset($user['role']) ? $user['role'] : $intended_role;
        
        if ($role !== $intended_role) {
            echo "<script>alert('Access Denied: You are trying to login from the wrong portal.'); window.history.back();</script>";
            exit();
        }

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $role;

        if ($role == 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($role == 'staff') {
            header("Location: ../staff/dashboard.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid Username or Password'); window.history.back();</script>";
        exit();
    }
}
?>