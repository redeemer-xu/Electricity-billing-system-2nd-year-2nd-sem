<?php
session_start();

// Remove all session data
session_unset();
session_destroy();

// Send user back to login page
header("Location: index.php");
exit();
?>