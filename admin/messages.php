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
        <?php include 'includes/sidebar.php'; ?>

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