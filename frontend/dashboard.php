<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User'; 

include '../backend/db.php';

$sql = "SELECT * FROM skin_analysis WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$analyses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My Dashboard | AI Beauty Salon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom.css"> 
    <style>
        .analysis-card { transition: transform 0.3s; border: 1px solid #eee; }
        .analysis-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .img-wrapper { height: 180px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center; }
        .skin-badge { font-size: 0.8rem; padding: 0.5em 0.8em; border-radius: 50px; }
        .badge-oily { background-color: #ffe5e5; color: #d63031; }
        .badge-dry { background-color: #fff4e5; color: #e17055; }
        .badge-normal { background-color: #e5f9e5; color: #00b894; }
        .badge-primary { background-color: var(--primary-color, #557c55); color: white; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3 mb-4">
        <div class="container">
            <a class="navbar-brand text-success fw-bold" href="index.html">AI Beauty Salon</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted d-none d-md-block">Hi, <strong><?= htmlspecialchars($user_name) ?></strong></span>
                <a href="upload.html" class="btn btn-primary btn-sm rounded-pill px-3">New Scan</a>
                <a href="../backend/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="fw-bold mb-4">My Skin History</h2>

        <?php if (empty($analyses)): ?>
            <div class="text-center py-5">
                <h3 class="fw-bold text-muted mt-3">No Scans Yet</h3>
                <a href="upload.html" class="btn btn-primary btn-lg rounded-pill shadow-sm">Analyze My Skin</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($analyses as $analysis): 
                    $type = strtolower($analysis['skin_type']);
                    $badgeClass = 'badge-primary';
                    if (strpos($type, 'oily') !== false) $badgeClass = 'badge-oily';
                    elseif (strpos($type, 'dry') !== false) $badgeClass = 'badge-dry';
                    elseif (strpos($type, 'normal') !== false) $badgeClass = 'badge-normal';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card analysis-card h-100 rounded-4 border-0 shadow-sm">
                        <div class="img-wrapper position-relative">
                            <?php if (!empty($analysis['image_path'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($analysis['image_path']) ?>" class="w-100 h-100 object-fit-cover">
                            <?php else: ?>
                                <span class="text-muted">No Image</span>
                            <?php endif; ?>
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-white text-dark shadow-sm rounded-pill"><?= date('M j', strtotime($analysis['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge <?= $badgeClass ?> skin-badge"><?= ucfirst(htmlspecialchars($analysis['skin_type'])) ?></span>
                                <small class="text-muted"><?= round($analysis['confidence'], 0) ?>% Match</small>
                            </div>
                            
                            <div class="mt-auto d-flex gap-2">
                                <a href="result.php?skin_type=<?= urlencode($analysis['skin_type']) ?>&image_file=<?= urlencode($analysis['image_path']) ?>&confidence=<?= $analysis['confidence'] ?>&oily_level=<?= $analysis['oily_level'] ?>&dry_level=<?= $analysis['dry_level'] ?>&normal_level=<?= $analysis['normal_level'] ?>" 
                                   class="btn btn-outline-success btn-sm flex-fill rounded-pill">View</a>
                                
                                <button class="btn btn-danger btn-sm rounded-pill px-3 delete-btn" 
                                        data-id="<?= $analysis['id'] ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Delete Logic
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this?')) {
                    fetch('../backend/delete_analysis.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + encodeURIComponent(id)
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === 'success') {
                            location.reload(); // Refresh page to remove item
                        } else {
                            alert('Delete failed.');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>