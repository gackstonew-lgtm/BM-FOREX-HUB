<?php
require_once __DIR__ . '/engine_config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header("Location: overview.php");
    exit;
}

$sqliteFile = __DIR__ . '/storage/database.sqlite';
$db = new PDO('sqlite:' . $sqliteFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->prepare("
    SELECT p.*, c.name as category_name 
    FROM market_posts p 
    LEFT JOIN market_categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.status = 'published'
");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: overview.php");
    exit;
}

define('PAGE_TITLE', htmlspecialchars($post['title']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>try{if(localStorage.getItem('bmTheme')==='light'){document.documentElement.setAttribute('data-theme','light')}}catch(e){}</script>
<link rel="stylesheet" href="css/theme-light.css?v=1">
<script src="js/theme.js" defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= PAGE_TITLE ?> | BM Forex Hub</title>
    <?php if(!empty($post['seo_description'])): ?>
    <meta name="description" content="<?= htmlspecialchars($post['seo_description']) ?>">
    <?php elseif(!empty($post['summary'])): ?>
    <meta name="description" content="<?= htmlspecialchars($post['summary']) ?>">
    <?php endif; ?>
    <link rel="icon" type="image/png" href="BM-ForexHub-Logo-Circle.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="basics.css?v=<?= filemtime('basics.css') ?>">
    <link rel="stylesheet" href="css/motion.css?v=<?= filemtime('css/motion.css') ?>">
    <link rel="stylesheet" href="css/overview.css?v=<?= filemtime('css/overview.css') ?>">
    <style>
        .post-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .post-header {
            margin-bottom: 30px;
        }
        .post-header h1 {
            font-size: 36px;
            color: #fff;
            margin-bottom: 15px;
        }
        .post-meta {
            color: #a0aec0;
            font-size: 14px;
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .post-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .post-hero-image {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .post-content {
            color: #f0f2f5;
            font-size: 16px;
            line-height: 1.8;
        }
        .post-content h2, .post-content h3 {
            color: #00d4aa;
            margin-top: 30px;
        }
        .post-content img {
            max-width: 100%;
            border-radius: 8px;
            height: auto;
        }
        .post-content a {
            color: #667eea;
            text-decoration: none;
        }
        .post-content a:hover {
            color: #00d4aa;
            text-decoration: underline;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #00d4aa;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>
    <main class="post-container">
        <a href="overview.php" class="back-link">← Back to Market Overview</a>
        <article class="post-detail">
            <header class="post-header">
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    <?php if(!empty($post['category_name'])): ?>
                    <span><i class="fas fa-folder"></i> <?= htmlspecialchars($post['category_name']) ?></span>
                    <?php endif; ?>
                    <?php if(!empty($post['author'])): ?>
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($post['author']) ?></span>
                    <?php endif; ?>
                    <span><i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($post['publish_date'] ?? $post['created_at'])) ?></span>
                </div>
                <?php if (!empty($post['featured_image'])): ?>
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-hero-image">
                <?php elseif (!empty($post['thumbnail_path'])): // fallback to old schema field ?>
                    <img src="<?= htmlspecialchars($post['thumbnail_path']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-hero-image">
                <?php endif; ?>
            </header>
            <div class="post-content">
                <?php 
                // Decode HTML entities if they were encoded in DB. TinyMCE generates HTML.
                // Assuming content is sanitized during output or input.
                // Using html_entity_decode to render raw HTML correctly if it was encoded.
                // If it's already raw HTML in DB, just echo.
                // Depending on the API implementation, we might just echo it.
                // We'll echo it directly, assuming the rich text is clean HTML.
                echo !empty($post['content']) ? $post['content'] : nl2br(htmlspecialchars($post['summary'])); 
                ?>
            </div>
        </article>
    </main>
    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
