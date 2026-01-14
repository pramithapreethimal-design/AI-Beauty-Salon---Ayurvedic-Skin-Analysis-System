<?php
// PHP Logic remains the same
$skin = $_GET['skin_type'] ?? 'Unknown';
$image_file = $_GET['image_file'] ?? null;
$confidence = floatval($_GET['confidence'] ?? 0);
$oily_level = floatval($_GET['oily_level'] ?? 0);
$dry_level = floatval($_GET['dry_level'] ?? 0);
$normal_level = floatval($_GET['normal_level'] ?? 0);

$recommended = [];
if (!empty($_GET['products'])) {
    $decoded = base64_decode(urldecode($_GET['products']));
    $recommended = json_decode($decoded, true) ?: [];
}

// Fallback logic
if (empty($recommended)) {
    $fallback = [
        "oily" => [
            ["name"=>"Siddhalepa Neem Face Wash", "description"=>"Purifies oily skin", "price"=>"LKR 850", "link"=>"#"],
            ["name"=>"Wickramasiri Sandalwood Soap", "description"=>"Reduces oil & acne", "price"=>"LKR 450", "link"=>"#"],
            ["name"=>"Herbal Concepts Turmeric Wash", "description"=>"Antibacterial herbal", "price"=>"LKR 750", "link"=>"#"]
        ],
        "dry" => [
            ["name"=>"Siddhalepa Aloe Vera Cream", "description"=>"Deep hydration", "price"=>"LKR 950", "link"=>"#"],
            ["name"=>"Herbal Concepts Coconut Oil", "description"=>"Nourishing blend", "price"=>"LKR 1,200", "link"=>"#"],
            ["name"=>"Wickramasiri Rose Moisturizer", "description"=>"Gentle daily care", "price"=>"LKR 800", "link"=>"#"]
        ],
        "normal" => [
            ["name"=>"Siddhalepa Sandalwood Pack", "description"=>"Balances & brightens", "price"=>"LKR 750", "link"=>"#"],
            ["name"=>"Herbal Concepts Cinnamon Toner", "description"=>"Refreshing daily", "price"=>"LKR 650", "link"=>"#"],
            ["name"=>"Wickramasiri Aloe Vera Gel", "description"=>"Lightweight care", "price"=>"LKR 550", "link"=>"#"]
        ]
    ];
    // Simple logic: if skin contains 'oily', show oily products, etc.
    $key = 'normal';
    $skin_lower = strtolower($skin);
    if (strpos($skin_lower, 'oily') !== false) $key = 'oily';
    if (strpos($skin_lower, 'dry') !== false) $key = 'dry';
    
    $recommended = $fallback[$key];
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Analysis Result | AI Beauty Salon</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/custom.css">

    <style>
        .analysis-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }
        
        .result-header {
            background: linear-gradient(135deg, rgba(85, 124, 85, 0.1) 0%, rgba(212, 163, 115, 0.1) 100%);
            padding: 2rem;
            text-align: center;
        }

        .level-bar-container {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }

        .level-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }

        /* Custom Progress Colors */
        .fill-oily { background-color: #e74c3c; } /* Red/Pink for Oil */
        .fill-dry { background-color: #f39c12; }  /* Orange for Dry */
        .fill-normal { background-color: #27ae60; } /* Green for Normal */

        .product-card {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 15px;
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3 mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-leaf me-2"></i>AI Beauty Salon
            </a>
            <div class="d-flex gap-2">
                <a href="upload.html" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="fas fa-camera me-2"></i>New Scan
                </a>
                <a href="../backend/logout.php" class="btn btn-danger btn-sm rounded-pill">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card analysis-card shadow-lg mb-5">
                    
                    <div class="result-header">
                        <h4 class="text-muted text-uppercase small ls-2">Analysis Complete</h4>
                        <h1 class="display-5 fw-bold text-success mb-3" style="font-family: 'Playfair Display', serif;">
                            <?= ucfirst(htmlspecialchars($skin)) ?> Skin
                        </h1>
                        <span class="badge bg-white text-success border border-success rounded-pill px-3 py-2 shadow-sm">
                            <i class="fas fa-check-circle me-2"></i>Confidence: <?= round($confidence, 1) ?>%
                        </span>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        
                        <div class="row align-items-center mb-5">
                            <?php if ($image_file): ?>
                            <div class="col-md-5 text-center mb-4 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <img src="../uploads/<?= htmlspecialchars($image_file) ?>" 
                                         alt="Skin Analysis" 
                                         class="img-fluid rounded-4 shadow"
                                         style="max-height: 250px; object-fit: cover; border: 4px solid white;">
                                    <div class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle p-2 shadow">
                                        <i class="fas fa-magic"></i>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="col-md-<?php echo $image_file ? '7' : '12'; ?>">
                                <h5 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif;">Detailed Breakdown</h5>
                                
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted"><i class="fas fa-tint me-2 text-danger"></i>Oily Level</span>
                                        <span class="fw-bold"><?= round($oily_level, 1) ?>%</span>
                                    </div>
                                    <div class="level-bar-container">
                                        <div class="level-fill fill-oily" style="width: <?= min(100, $oily_level) ?>%;"></div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted"><i class="fas fa-sun me-2 text-warning"></i>Dryness Level</span>
                                        <span class="fw-bold"><?= round($dry_level, 1) ?>%</span>
                                    </div>
                                    <div class="level-bar-container">
                                        <div class="level-fill fill-dry" style="width: <?= min(100, $dry_level) ?>%;"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted"><i class="fas fa-smile me-2 text-success"></i>Healthy/Normal</span>
                                        <span class="fw-bold"><?= round($normal_level, 1) ?>%</span>
                                    </div>
                                    <div class="level-bar-container">
                                        <div class="level-fill fill-normal" style="width: <?= min(100, $normal_level) ?>%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5 opacity-10">

                        <div class="text-center mb-4">
                            <span class="badge bg-success bg-opacity-10 text-success mb-2">Ayurvedic Remedies</span>
                            <h3 class="fw-bold" style="font-family: 'Playfair Display', serif;">Recommended For You</h3>
                            <p class="text-muted">Curated products based on your <?= htmlspecialchars($skin) ?> skin type.</p>
                        </div>

                        <div class="row g-4">
                            <?php foreach ($recommended as $prod): ?>
                                <div class="col-md-4">
                                    <div class="card product-card h-100 p-3">
                                        <div class="text-center mb-3">
                                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fas fa-pump-soap text-success fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="card-body text-center p-0">
                                            <h6 class="fw-bold mb-2"><?= htmlspecialchars($prod['name']) ?></h6>
                                            <p class="small text-muted mb-3"><?= htmlspecialchars($prod['description']) ?></p>
                                            
                                            <?php if(isset($prod['price'])): ?>
                                                <p class="fw-bold text-success mb-2"><?= htmlspecialchars($prod['price']) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($prod['link']) && $prod['link'] !== '#'): ?>
                                                <a href="<?= htmlspecialchars($prod['link']) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill w-100">
                                                    View Details
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light rounded-pill w-100" disabled>Out of Stock</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        const setTheme = (theme) => {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            if (theme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        };

        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        setTheme(savedTheme);

        themeToggle.addEventListener('click', () => {
            const newTheme = htmlElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });
    </script>
</body>
</html>