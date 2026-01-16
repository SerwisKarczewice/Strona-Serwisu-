<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
$news_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Aktualnościami - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-laptop-code"></i>
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="messages.php" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    <span>Wiadomości</span>
                </a>
                <a href="news.php" class="nav-link active">
                    <i class="fas fa-newspaper"></i>
                    <span>Aktualności</span>
                </a>
                <a href="gallery.php" class="nav-link">
                    <i class="fas fa-images"></i>
                    <span>Galeria</span>
                </a>
                <a href="products.php" class="nav-link">
                    <i class="fas fa-box"></i>
                    <span>Produkty</span>
                </a>
                <a href="services.php" class="nav-link">
                    <i class="fas fa-tools"></i>
                    <span>Usługi</span>
                </a>
                <a href="calculator.php" class="nav-link ">
                    <i class="fas fa-calculator"></i>
                    <span>Kalkulator</span>
                </a>
                <a href="invoices.php" class="nav-link">
                    <i class="fas fa-file-invoice"></i>
                    <span>Faktury</span>
                </a>
                <a href="../index.php" class="nav-link" target="_blank">
                    <i class="fas fa-eye"></i>
                    <span>Zobacz stronę</span>
                </a>
                <a href="logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Wyloguj</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="content-header">
                <h1>Aktualności</h1>
                <a href="add_news.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Dodaj Aktualność
                </a>
            </header>

            <div class="content-section full-width">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tytuł</th>
                                <th>Data utworzenia</th>
                                <th>Status</th>
                                <th>Wyświetlenia</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news_items as $news): ?>
                                <tr>
                                    <td><?php echo $news['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($news['title']); ?></strong>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($news['created_at'])); ?></td>
                                    <td>
                                        <span
                                            class="status-badge <?php echo $news['published'] ? 'published' : 'draft'; ?>">
                                            <?php echo $news['published'] ? 'Opublikowana' : 'Szkic'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $news['views']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_news.php?id=<?php echo $news['id']; ?>" class="btn-icon"
                                                title="Edytuj">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="toggle_news.php?id=<?php echo $news['id']; ?>" class="btn-icon"
                                                title="<?php echo $news['published'] ? 'Ukryj' : 'Opublikuj'; ?>">
                                                <i
                                                    class="fas fa-<?php echo $news['published'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </a>
                                            <a href="delete_news.php?id=<?php echo $news['id']; ?>" class="btn-icon delete"
                                                title="Usuń"
                                                onclick="return confirm('Czy na pewno chcesz usunąć tę aktualność?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>

<style>
    .full-width {
        grid-column: 1 / -1;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 25px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .status-badge.published {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.draft {
        background: #f8d7da;
        color: #721c24;
    }
</style>