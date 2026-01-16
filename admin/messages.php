<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiadomości - Panel Administracyjny</title>
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
                <h1>Wiadomości od Klientów</h1>
            </header>

            <div class="content-section full-width">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imię i Nazwisko</th>
                                <th>Adres</th>
                                <th>Telefon</th>
                                <th>Temat</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo $msg['id']; ?></td>
                                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                    <td><?php echo $msg['address'] ? htmlspecialchars($msg['address']) : '<em>Nie podano</em>'; ?>
                                    </td>
                                    <td><a
                                            href="tel:<?php echo htmlspecialchars($msg['phone']); ?>"><?php echo htmlspecialchars($msg['phone']); ?></a>
                                    </td>
                                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $msg['status']; ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_message.php?id=<?php echo $msg['id']; ?>" class="btn-icon"
                                                title="Zobacz">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="delete_message.php?id=<?php echo $msg['id']; ?>"
                                                class="btn-icon delete" title="Usuń"
                                                onclick="return confirm('Czy na pewno chcesz usunąć tę wiadomość?')">
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