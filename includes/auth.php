<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_admin_login() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }
}

function require_client_login() {
    if (!isset($_SESSION['client_id'])) {
        header("Location: ../client/login.php");
        exit();
    }
}
?>
