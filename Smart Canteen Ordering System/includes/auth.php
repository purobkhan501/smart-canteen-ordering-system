<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        echo "<script>alert('Unauthorized access'); window.history.back();</script>";
        exit();
    }
}
?>
