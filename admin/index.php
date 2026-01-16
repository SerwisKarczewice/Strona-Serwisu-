<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Statystyki
$stmt = $pdo->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'nowa'");
$new_messages = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM news WHERE published = 1");
$published_news = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$total_products = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery");
$total_gallery = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE is_active = 1");
$active_services = $stmt->fetch()['total'];

// Ostatnie wiadomości
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$recent_messages = $stmt->fetchAll();

// Ostatnie aktualności
$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5");
$recent_news = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administracyjny - Serwis Komputerowy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>Dashboard</h1>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            </div>
        </header>

        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon messages">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <h3>Nowe Wiadomości</h3>
                    <p class="stat-number"><?php echo $new_messages; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon news">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-content">
                    <h3>Aktualności</h3>
                    <p class="stat-number"><?php echo $published_news; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3>Produkty</h3>
                    <p class="stat-number"><?php echo $total_products; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon visits">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-content">
                    <h3>Galeria</h3>
                    <p class="stat-number"><?php echo $total_gallery; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon news">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="stat-content">
                    <h3>Aktywne Usługi</h3>
                    <p class="stat-number"><?php echo $active_services; ?></p>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-envelope"></i> Ostatnie Wiadomości</h2>
                    <a href="messages.php" class="btn-link">Zobacz wszystkie</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Od</th>
                                <th>Temat</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_messages)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #999;">Brak wiadomości</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_messages as $msg): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $msg['status']; ?>">
                                                <?php echo ucfirst($msg['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view_message.php?id=<?php echo $msg['id']; ?>" class="btn-icon"
                                                title="Zobacz">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-newspaper"></i> Ostatnie Aktualności</h2>
                    <a href="news.php" class="btn-link">Zobacz wszystkie</a>
                </div>
                <div class="news-list">
                    <?php if (empty($recent_news)): ?>
                        <p style="text-align: center; color: #999; padding: 20px;">Brak aktualności</p>
                    <?php else: ?>
                        <?php foreach ($recent_news as $news_item): ?>
                            <div class="news-item">
                                <div class="news-item-header">
                                    <h4><?php echo htmlspecialchars($news_item['title']); ?></h4>
                                    <span class="news-status <?php echo $news_item['published'] ? 'published' : 'draft'; ?>">
                                        <?php echo $news_item['published'] ? 'Opublikowana' : 'Szkic'; ?>
                                    </span>
                                </div>
                                <p class="news-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d.m.Y H:i', strtotime($news_item['created_at'])); ?>
                                </p>
                                <div class="news-actions">
                                    <a href="edit_news.php?id=<?php echo $news_item['id']; ?>" class="btn-icon" title="Edytuj">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_news.php?id=<?php echo $news_item['id']; ?>" class="btn-icon delete"
                                        title="Usuń" onclick="return confirm('Czy na pewno chcesz usunąć tę aktualność?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    </div>
</body>

</html>