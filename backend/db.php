<?php
$host = 'db';
$user = 'root';
$pass = 'rootpass';
$db   = 'beauty_ai_app';

$attempts = 5;
$delay = 2;

for ($i = 0; $i < $attempts; $i++) {
    try {
        $conn = new mysqli($host, $user, $pass, $db);
        if ($conn->connect_error) {
            throw new Exception("Connect failed: " . $conn->connect_error);
        }
        break; // Success!
    } catch (Exception $e) {
        if ($i === $attempts - 1) {
            die("DB Error: " . $e->getMessage());
        }
        sleep($delay);
    }
}
?>