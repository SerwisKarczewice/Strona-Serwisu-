<?php
require_once 'config.php';

if (!isset($_GET['slug'])) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE slug = :slug AND published = 1");
$stmt->execute([':slug' => $_GET['slug']]);
$news = $stmt->fetch();

if (!$news) {
    header('Location: index.php');
    exit;
}

// Zwiększ licznik wyświetleń
$stmt = $pdo->prepare("UPDATE news SET views = views + 1 WHERE id = :id");
$stmt->execute([':id' => $news['id']]);

// Pobierz inne aktualności
$stmt = $pdo->prepare("SELECT * FROM news WHERE published = 1 AND id != :id ORDER BY created_at DESC LIMIT 3");
$stmt->execute([':id' => $news['id']]);
$other_news = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="<?php echo htmlspecialchars($news['excerpt'] ?: substr(strip_tags($news['content']), 0, 160)); ?>">
    <meta name="keywords" content="aktualności, news, serwis komputerowy, artykuł">
    <title><?php echo htmlspecialchars($news['title']); ?> - TechService</title>
    <link rel="icon" type="image/svg+xml" href="uploads/icons/favicon.svg">
    <link rel="canonical"
        href="https://twojadomena.pl/news-detail.php?slug=<?php echo htmlspecialchars($news['slug']); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?php echo htmlspecialchars($news['title']); ?>">
    <meta property="og:description"
        content="<?php echo htmlspecialchars($news['excerpt'] ?: substr(strip_tags($news['content']), 0, 160)); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url"
        content="https://twojadomena.pl/news-detail.php?slug=<?php echo htmlspecialchars($news['slug']); ?>">
    <meta property="og:image" content="https://twojadomena.pl/images/news-og.jpg">
    <meta property="og:locale" content="pl_PL">
    <meta property="article:published_time" content="<?php echo date('c', strtotime($news['created_at'])); ?>">
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1><?php echo htmlspecialchars($news['title']); ?></h1>
            <p>
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('d.m.Y', strtotime($news['created_at'])); ?>
                &nbsp;&nbsp;
                <i class="fas fa-eye"></i>
                <?php echo $news['views']; ?> wyświetleń
            </p>
        </div>
    </section>

    <section class="news-detail">
        <div class="container">
            <div class="news-content">
                <article>
                    <?php echo nl2br(htmlspecialchars($news['content'])); ?>
                </article>

                <div class="news-footer">
                    <a href="index.php#news" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Powrót do strony głównej
                    </a>
                </div>
            </div>

            <?php if (!empty($other_news)): ?>
                <aside class="related-news">
                    <h3>Inne Aktualności</h3>
                    <div class="related-grid">
                        <?php foreach ($other_news as $item): ?>
                            <a href="news-detail.php?slug=<?php echo urlencode($item['slug']); ?>" class="related-card">
                                <div class="related-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('d.m.Y', strtotime($item['created_at'])); ?>
                                </div>
                                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p><?php echo htmlspecialchars($item['excerpt'] ?: substr($item['content'], 0, 100) . '...'); ?>
                                </p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </aside>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>

</html>

<style>
    .news-detail {
        padding: 80px 0;
        background: #fff;
    }

    .news-detail .container {
        max-width: 1000px;
    }

    .news-content {
        background: #fff;
        margin-bottom: 50px;
    }

    .news-content article {
        line-height: 1.8;
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 40px;
    }

    .news-content article p {
        margin-bottom: 20px;
    }

    .news-footer {
        padding-top: 30px;
        border-top: 2px solid #ecf0f1;
    }

    .related-news {
        background: var(--light-color);
        padding: 40px;
        border-radius: 15px;
    }

    .related-news h3 {
        font-size: 1.8rem;
        color: var(--dark-color);
        margin-bottom: 25px;
    }

    .related-grid {
        display: grid;
        gap: 20px;
    }

    .related-card {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .related-card:hover {
        transform: translateX(10px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .related-date {
        color: var(--primary-color);
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .related-card h4 {
        color: var(--dark-color);
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .related-card p {
        color: var(--text-light);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .news-content article {
            font-size: 1rem;
        }
    }
</style>