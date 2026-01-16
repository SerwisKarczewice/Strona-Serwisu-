<?php
// ============== VIEW_MESSAGE.PHP ==============
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: messages.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
$message = $stmt->fetch();

if (!$message) {
    header('Location: messages.php');
    exit;
}

if ($message['status'] === 'nowa') {
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'przeczytana' WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiadomość - Panel Administracyjny</title>
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
                <a href="messages.php" class="nav-link active">
                    <i class="fas fa-envelope"></i>
                    <span>Wiadomości</span>
                </a>
                <a href="news.php" class="nav-link">
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
                <a href="calculator.php" class="nav-link">
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
                <h1>Szczegóły Wiadomości</h1>
                <div class="header-actions">
                    <a href="messages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Powrót
                    </a>
                </div>
            </header>

            <div class="content-section full-width">
                <div class="message-details">
                    <div class="message-header">
                        <h2><?php echo htmlspecialchars($message['subject']); ?></h2>
                        <span class="status-badge <?php echo $message['status']; ?>">
                            <?php echo ucfirst($message['status']); ?>
                        </span>
                    </div>

                    <div class="message-meta">
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <strong>Od:</strong> <?php echo htmlspecialchars($message['name']); ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <strong>Adres:</strong>
                            <?php echo $message['address'] ? htmlspecialchars($message['address']) : '<em>Nie podano</em>'; ?>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-phone"></i>
                            <strong>Telefon:</strong> <a
                                href="tel:<?php echo htmlspecialchars($message['phone']); ?>"><?php echo htmlspecialchars($message['phone']); ?></a>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <strong>Data:</strong> <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                        </div>
                    </div>

                    <div class="message-content">
                        <h3>Treść wiadomości:</h3>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                    </div>

                    <div class="message-actions">
                        <a href="mark_answered.php?id=<?php echo $message['id']; ?>" class="btn btn-success">
                            <i class="fas fa-check"></i>
                            Oznacz jako odpowiedziana
                        </a>
                        <a href="delete_message.php?id=<?php echo $message['id']; ?>" class="btn btn-danger"
                            onclick="return confirm('Czy na pewno chcesz usunąć tę wiadomość?')">
                            <i class="fas fa-trash"></i>
                            Usuń wiadomość
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>

<style>
    .header-actions {
        display: flex;
        gap: 10px;
    }

    .message-details {
        background: white;
        padding: 30px;
        border-radius: 15px;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 2px solid #ecf0f1;
        margin-bottom: 25px;
    }

    .message-header h2 {
        color: #2c3e50;
        font-size: 1.8rem;
    }

    .message-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #2c3e50;
    }

    .meta-item i {
        color: #ff6b35;
        font-size: 1.2rem;
    }

    .meta-item a {
        color: #ff6b35;
        text-decoration: none;
    }

    .meta-item a:hover {
        text-decoration: underline;
    }

    .message-content {
        margin-bottom: 30px;
    }

    .message-content h3 {
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .message-content p {
        line-height: 1.8;
        color: #666;
        font-size: 1.05rem;
    }

    .message-actions {
        display: flex;
        gap: 15px;
        padding-top: 20px;
        border-top: 2px solid #ecf0f1;
    }

    .btn-success {
        background: #28a745;
        color: white;
        padding: 12px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .btn-danger {
        background: #dc3545;
        color: white;
        padding: 12px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
    }
</style>