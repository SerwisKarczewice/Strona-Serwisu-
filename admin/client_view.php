<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$client_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    header('Location: clients.php');
    exit;
}

// Stats
$stmt_msgs = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE client_id = ?");
$stmt_msgs->execute([$client_id]);
$msg_count = $stmt_msgs->fetchColumn();

$stmt_solutions = $pdo->prepare("SELECT COUNT(*) FROM client_solutions WHERE client_id = ?");
$stmt_solutions->execute([$client_id]);
$solution_count = $stmt_solutions->fetchColumn();

// Fetch Messages
$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE client_id = ? ORDER BY created_at DESC");
$stmt->execute([$client_id]);
$messages = $stmt->fetchAll();

// Fetch Solutions
$stmt = $pdo->prepare("SELECT * FROM client_solutions WHERE client_id = ? ORDER BY created_at DESC");
$stmt->execute([$client_id]);
$solutions = $stmt->fetchAll();

// Logic for updating client notes/data could go here...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_notes') {
    $notes = $_POST['notes'];
    $stmt = $pdo->prepare("UPDATE clients SET notes = ? WHERE id = ?");
    $stmt->execute([$notes, $client_id]);
    header("Location: client_view.php?id=$client_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Klienta - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile-info h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }

        .profile-stats {
            display: flex;
            gap: 20px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message-item {
            border-left: 3px solid #3498db;
            padding: 10px 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }

        .message-meta {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }

        .solution-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: transform 0.2s;
        }

        .solution-item:hover {
            border-color: #3498db;
            transform: translateY(-2px);
        }

        .solution-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .solution-price {
            font-weight: bold;
            color: #27ae60;
            font-size: 1.1rem;
        }

        .solution-status.new {
            color: #f39c12;
        }

        .solution-status.sent {
            color: #3498db;
        }

        .solution-status.accepted {
            color: #27ae60;
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px dashed #eee;
            padding-bottom: 5px;
        }

        .info-label {
            width: 100px;
            font-weight: bold;
            color: #7f8c8d;
        }

        .info-value {
            color: #2c3e50;
            flex: 1;
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Profil Klienta</h1>
                <a href="clients.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Powrót</a>
            </header>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <h2><i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($client['name']); ?>
                    </h2>
                    <p><i class="fas fa-building"></i>
                        <?php echo htmlspecialchars($client['company_name'] ?: 'Klient indywidualny'); ?>
                    </p>
                </div>
                <div class="profile-stats">
                    <div class="stat-box">
                        <div class="stat-value">
                            <?php echo $msg_count; ?>
                        </div>
                        <div class="stat-label">Zgłoszenia</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">
                            <?php echo $solution_count; ?>
                        </div>
                        <div class="stat-label">Oferty</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Left Column -->
                <div class="main-column">
                    <!-- Actions -->
                    <div style="margin-bottom: 20px; display: flex; gap: 10px;">
                        <a href="solution_editor.php?client_id=<?php echo $client['id']; ?>" class="btn btn-primary"
                            style="flex: 1; text-align: center;">
                            <i class="fas fa-file-invoice-dollar"></i> Stwórz Proponowane Rozwiązanie
                        </a>
                        <a href="#" class="btn btn-secondary" style="flex: 1; text-align: center;">
                            <i class="fas fa-file-pdf"></i> Raport PDF
                        </a>
                    </div>

                    <!-- Client Solutions -->
                    <div class="card">
                        <h3><i class="fas fa-lightbulb"></i> Proponowane Rozwiązania</h3>
                        <?php if (empty($solutions)): ?>
                            <p style="color: #999; text-align: center;">Brak przygotowanych rozwiązań.</p>
                        <?php else: ?>
                            <?php foreach ($solutions as $sol): ?>
                                <div class="solution-item">
                                    <div class="solution-header">
                                        <strong>
                                            <?php echo htmlspecialchars($sol['title']); ?>
                                        </strong>
                                        <span class="solution-status <?php echo $sol['status']; ?>">
                                            <?php echo strtoupper($sol['status'] == 'sent' ? 'Wysłana' : ($sol['status'] == 'accepted' ? 'Zaakceptowana' : $sol['status'])); ?>
                                        </span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div class="solution-price">
                                            <?php echo number_format($sol['total_price'], 2); ?> zł
                                        </div>
                                        <div class="solution-actions">
                                            <a href="generate_pdf.php?type=solution&id=<?php echo $sol['id']; ?>"
                                                target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i>
                                                Drukuj</a>
                                            <a href="solution_editor.php?id=<?php echo $sol['id']; ?>"
                                                class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edytuj</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Client Problems / Messages -->
                    <div class="card">
                        <h3><i class="fas fa-comments"></i> Zgłoszone Problemy (Wiadomości)</h3>
                        <?php if (empty($messages)): ?>
                            <p style="color: #999; text-align: center;">Brak historii wiadomości.</p>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <div class="message-item">
                                    <div class="message-meta">
                                        <span><i class="far fa-clock"></i>
                                            <?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?>
                                        </span>
                                        <span <span
                                            class="badge status-<?php echo $msg['status'] == 'nowa' ? 'pending' : 'success'; ?>">
                                            <?php echo $msg['status']; ?>
                                        </span>
                                        <?php
                                        // Check if this message has a solution
                                        $linked_solution = null;
                                        foreach ($solutions as $s) {
                                            if ($s['message_id'] == $msg['id']) {
                                                $linked_solution = $s;
                                                break;
                                            }
                                        }
                                        ?>
                                        <?php if ($linked_solution): ?>
                                            <a href="solution_editor.php?id=<?php echo $linked_solution['id']; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                style="margin-left: 10px; font-size: 0.7rem;">
                                                <i class="fas fa-link"></i> Oferta:
                                                <?php echo htmlspecialchars($linked_solution['title']); ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="solution_editor.php?message_id=<?php echo $msg['id']; ?>"
                                                class="btn btn-sm btn-outline-success"
                                                style="margin-left: 10px; font-size: 0.7rem;">
                                                <i class="fas fa-plus"></i> Stwórz Ofertę
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <strong style="display: block; margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($msg['subject']); ?>
                                    </strong>
                                    <p style="margin: 0; white-space: pre-wrap; color: #555;">
                                        <?php echo htmlspecialchars($msg['message']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column (Details) -->
                <div class="sidebar-column">
                    <div class="card">
                        <h3>Dane Kontaktowe</h3>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><a href="mailto:<?php echo $client['email']; ?>">
                                    <?php echo htmlspecialchars($client['email']); ?>
                                </a></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Telefon:</span>
                            <span class="info-value"><a href="tel:<?php echo $client['phone']; ?>">
                                    <?php echo htmlspecialchars($client['phone']); ?>
                                </a></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Adres:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($client['address']); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">NIP:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($client['nip']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="card">
                        <h3>Notatki</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_notes">
                            <textarea name="notes" rows="6"
                                style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; resize: vertical;"
                                placeholder="Wewnętrzne notatki o kliencie..."><?php echo htmlspecialchars($client['notes']); ?></textarea>
                            <button type="submit" class="btn btn-sm btn-primary"
                                style="margin-top: 10px; width: 100%;">Zapisz notatkę</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>