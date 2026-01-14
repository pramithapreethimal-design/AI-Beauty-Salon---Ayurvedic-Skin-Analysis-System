<?php
session_start();
include 'db.php';

// Check if a file was actually uploaded
if (isset($_POST['upload']) && isset($_FILES['skin_image'])) {
    
    // 1. Determine if user is logged in or a guest
    $is_logged_in = isset($_SESSION['user_id']);
    $user_id = $is_logged_in ? $_SESSION['user_id'] : null;

    $targetDir = "../uploads/";
    
    // Create uploads folder if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $file = $_FILES["skin_image"];

    // Check upload error
    if ($file["error"] !== UPLOAD_ERR_OK) {
        die("Error uploading file. Error Code: " . $file["error"]);
    }

    // Limit file size (5MB)
    if ($file["size"] > 5 * 1024 * 1024) {
        die("File too large. Max size: 5MB.");
    }

    // Sanitize filename
    $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", basename($file["name"]));
    $targetFilePath = $targetDir . $fileName;
    $ext = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Validate extension
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        die("Only JPG, JPEG, PNG allowed.");
    }

    // Save file temporarily
    if (!move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        die("Failed to save image.");
    }

    // 2. Call Flask AI API
    $curl = curl_init();
    $cfile = new CURLFile($targetFilePath, mime_content_type($targetFilePath), $fileName);
    curl_setopt_array($curl, [
        CURLOPT_URL => "http://127.0.0.1:5000/api/predict",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['image' => $cfile],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    // Handle API errors
    if ($httpCode !== 200 || $error) {
        unlink($targetFilePath); // Delete image on error
        die("AI service unavailable. Make sure python app.py is running!");
    }

    $result = json_decode($response, true);
    if (!$result || !isset($result['skin_type'])) {
        unlink($targetFilePath);
        die("Invalid AI response.");
    }

    // Extract data
    $skin_type = $result['skin_type'];
    $confidence = floatval($result['confidence']);
    $oily_level = floatval($result['oily_level']);
    $dry_level = floatval($result['dry_level']);
    $normal_level = floatval($result['normal_level']);
    $products = $result['products'] ?? [];

    // 3. Logic Split: Member vs Guest
    if ($is_logged_in) {
        // --- MEMBER: SAVE TO DB ---
        $stmt = $conn->prepare("INSERT INTO skin_analysis (user_id, skin_type, image_path, confidence, oily_level, dry_level, normal_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isssddd", $user_id, $skin_type, $fileName, $confidence, $oily_level, $dry_level, $normal_level);
            $stmt->execute();
            $stmt->close();
        }
        // We keep the file in ../uploads/ so they can see it in Dashboard
    } else {
        // --- GUEST: PRIVACY FIRST ---
        // We do NOT save to database.
        // We DELETE the image immediately after analysis.
        unlink($targetFilePath);
        $fileName = ""; // Clear filename so result page doesn't try to show it
    }

    // 4. Redirect to Result Page
    $productsJson = json_encode($products);
    $encodedProducts = urlencode(base64_encode($productsJson));

    $params = http_build_query([
        'skin_type' => $skin_type,
        'image_file' => $fileName, // This will be empty for guests
        'confidence' => $confidence,
        'oily_level' => $oily_level,
        'dry_level' => $dry_level,
        'normal_level' => $normal_level,
        'products' => $encodedProducts
    ]);

    header("Location: ../frontend/result.php?$params");
    exit;
} else {
    // If someone tries to access this file directly without uploading
    header("Location: ../frontend/upload.html");
    exit();
}
?>