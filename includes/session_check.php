<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Works from any folder depth
    $root = str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 2);
    header("Location: " . $root . "index.php");
    exit();
}
?>