<?php
session_start();
include 'db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    // FIXED: Selecting 'full_name' based on your database structure
    $sql = "SELECT id, full_name, password FROM users WHERE email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['full_name']; 

            // ✅ FIXED PATH: Redirect to 'frontend' folder
            header("Location: ../frontend/dashboard.php");
            exit();
        } else {
            // ✅ FIXED PATH
            echo "<script>alert('Wrong password!'); window.location.href='../frontend/login.html';</script>";
        }
    } else {
        // ✅ FIXED PATH
        echo "<script>alert('User not found. Please register!'); window.location.href='../frontend/register.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>