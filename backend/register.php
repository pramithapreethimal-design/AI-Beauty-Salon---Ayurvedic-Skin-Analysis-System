<?php
session_start();
include 'db.php';

if (isset($_POST['register'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $checkSql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // ✅ FIXED PATH
        echo "<script>alert('Email already registered!'); window.location.href='../frontend/login.html';</script>";
        $stmt->close();
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashed_pass);

        if ($stmt->execute()) {
            // ✅ FIXED PATH
            echo "<script>alert('Registration Successful!'); window.location.href='../frontend/login.html';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>