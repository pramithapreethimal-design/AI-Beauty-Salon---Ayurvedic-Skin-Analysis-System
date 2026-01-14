<?php
session_start();

// 1. Clear all session variables
session_unset();

// 2. Destroy the session
session_destroy();

// 3. ✅ FIXED PATH: Redirect to 'frontend' folder instead of 'ui'
header("Location: ../frontend/login.html");
exit();
?>