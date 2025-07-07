<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
} else {
    header("Location: pages/login.php");
}
exit();
