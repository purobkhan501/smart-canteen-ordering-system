<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $intended_role = isset($_POST['role']) ? $_POST['role'] : '';

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        // Use DB role if exists, otherwise fallback to intended role
        $role = isset($user['role']) ? $user['role'] : $intended_role;
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