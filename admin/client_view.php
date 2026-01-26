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

// AUTO-FIX: Ensure DB schema is correct
define('SILENT_MIGRATION', true);
require_once 'migration_clients.php';

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

// Logic for updating client notes/data and managing meetings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_notes') {
        $notes = $_POST['notes'];
        $stmt = $pdo->prepare("UPDATE clients SET notes = ? WHERE id = ?");
        $stmt->execute([$notes, $client_id]);
        header("Location: client_view.php?id=$client_id");
        exit;
    } elseif ($_POST['action'] === 'add_meeting') {
        // Ensure table exists
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS client_meetings (
                id INT(11) NOT NULL AUTO_INCREMENT,
                client_id INT(11) NOT NULL,
                title VARCHAR(255) NOT NULL,
                meeting_date DATE NOT NULL,
                meeting_time TIME NOT NULL,
                location VARCHAR(255),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_client (client_id),
                CONSTRAINT fk_meeting_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Exception $e) {
        }

        $title = trim($_POST['title']);
        $meeting_date = $_POST['meeting_date'];
        $meeting_time = $_POST['meeting_time'];
        $location = trim($_POST['location'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($title && $meeting_date && $meeting_time) {
            $stmt = $pdo->prepare("INSERT INTO client_meetings (client_id, title, meeting_date, meeting_time, location, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$client_id, $title, $meeting_date, $meeting_time, $location, $notes]);
        }
        header("Location: client_view.php?id=$client_id");
        exit;
    } elseif ($_POST['action'] === 'delete_meeting') {
        $meeting_id = $_POST['meeting_id'];
        $stmt = $pdo->prepare("DELETE FROM client_meetings WHERE id = ? AND client_id = ?");
        $stmt->execute([$meeting_id, $client_id]);
        header("Location: client_view.php?id=$client_id");
        exit;
    } elseif ($_POST['action'] === 'update_client') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $company = trim($_POST['company_name']);
        $nip = trim($_POST['nip']);

        if ($name) {
            $stmt = $pdo->prepare("UPDATE clients SET name = ?, email = ?, phone = ?, address = ?, company_name = ?, nip = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $company, $nip, $client_id]);
            header("Location: client_view.php?id=$client_id&success=updated");
            exit;
        }
    }
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
            margin-bottom: 12px;
            padding-bottom: 2px;
        }

        .info-label {
            width: 80px;
            font-weight: bold;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .info-value a {
            text-decoration: none;
            color: #ff6b35;
            transition: color 0.2s;
        }

        .info-value a:hover {
            color: #f7931e;
            text-decoration: underline;
        }

        .info-value {
            color: #2c3e50;
            flex: 1;
            white-space: pre-wrap;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        /* Fix for message content wrapping */
        .message-item p {
            white-space: pre-wrap;
            overflow-wrap: break-word;
            word-wrap: break-word;
            max-width: 100%;
        }

        /* Fix for solution items wrapping */
        .solution-item {
            white-space: normal;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Profil Klienta: <?php echo htmlspecialchars($client['name']); ?></h1>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="clients.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Powrót do listy
                    </a>
                    <a href="delete_client.php?id=<?php echo $client_id; ?>" class="btn btn-danger"
                        onclick="return confirm('Czy na pewno chcesz usunąć tego klienta oraz wszystkie jego powiązane dane (spotkania, oferty)?')"
                        style="background: #e74c3c; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: 600;">
                        <i class="fas fa-trash"></i> Usuń Klienta
                    </a>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"
                    style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    if ($_GET['success'] === 'solution_deleted')
                        echo "Pomyślnie usunięto ofertę.";
                    elseif ($_GET['success'] === 'updated')
                        echo "Zaktualizowano dane klienta.";
                    else
                        echo "Operacja zakończona sukcesem.";
                    ?>
                </div>
            <?php endif; ?>

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
                        <button onclick="openEditModal()" class="btn btn-secondary"
                            style="flex: 1; text-align: center;">
                            <i class="fas fa-user-edit"></i> Edytuj Dane
                        </button>
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
                                            <a href="delete_solution.php?id=<?php echo $sol['id']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Czy na pewno chcesz usunąć to rozwiązanie?')"
                                                style="background: #e74c3c; color: white;">
                                                <i class="fas fa-trash"></i> Usuń</a>
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
                                        <span
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
                            <span class="info-value">
                                <?php echo htmlspecialchars($client['email']); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Telefon:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($client['phone']); ?>
                            </span>
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

                    <div class="card">
                        <h3><i class="fas fa-calendar-alt"></i> Grafik Spotkań</h3>
                        <div id="schedule-container" style="max-height: 400px; overflow-y: auto;">
                            <?php
                            // Fetch meetings for this client
                            try {
                                $stmt_meetings = $pdo->prepare("SELECT * FROM client_meetings WHERE client_id = ? ORDER BY meeting_date ASC");
                                $stmt_meetings->execute([$client_id]);
                                $meetings = $stmt_meetings->fetchAll();

                                if (!empty($meetings)):
                                    foreach ($meetings as $meeting):
                                        ?>
                                        <div class="meeting-item"
                                            style="border-left: 3px solid #9b59b6; padding: 10px; margin-bottom: 10px; background: #f8f9fa; border-radius: 5px;">
                                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($meeting['title']); ?></strong>
                                                    <div style="font-size: 0.85rem; color: #7f8c8d;">
                                                        <i class="far fa-calendar"></i>
                                                        <?php echo date('d.m.Y', strtotime($meeting['meeting_date'])); ?>
                                                        <br>
                                                        <i class="far fa-clock"></i>
                                                        <?php echo date('H:i', strtotime($meeting['meeting_time'])); ?>
                                                    </div>
                                                    <?php if (!empty($meeting['location'])): ?>
                                                        <div style="font-size: 0.85rem; color: #7f8c8d;">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                            <?php echo htmlspecialchars($meeting['location']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($meeting['notes'])): ?>
                                                        <div
                                                            style="font-size: 0.85rem; color: #555; margin-top: 5px; font-style: italic;">
                                                            <?php echo htmlspecialchars($meeting['notes']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <form method="POST" style="margin: 0;"
                                                    onsubmit="return confirm('Usuń to spotkanie?');">
                                                    <input type="hidden" name="action" value="delete_meeting">
                                                    <input type="hidden" name="meeting_id" value="<?php echo $meeting['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        style="padding: 5px 10px; font-size: 0.75rem;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <?php
                                    endforeach;
                                else:
                                    echo '<p style="color: #999; text-align: center; font-size: 0.9rem;">Brak zaplanowanych spotkań</p>';
                                endif;
                            } catch (PDOException $e) {
                                echo '<p style="color: #999; text-align: center; font-size: 0.9rem;">Brak danych spotkań</p>';
                            }
                            ?>
                        </div>
                        <form method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="add_meeting">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <input type="date" name="meeting_date" required
                                    style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.9rem;">
                                <input type="time" name="meeting_time" value="10:00" required
                                    style="padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.9rem;">
                            </div>
                            <input type="text" name="title" placeholder="Tytuł spotkania" required
                                style="width: 100%; padding: 8px; margin-top: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.9rem;">
                            <input type="text" name="location" placeholder="Lokalizacja (opcjonalnie)"
                                style="width: 100%; padding: 8px; margin-top: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.9rem;">
                            <textarea name="notes" placeholder="Notatki do spotkania" rows="2"
                                style="width: 100%; padding: 8px; margin-top: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 0.9rem; font-family: inherit; resize: vertical;"></textarea>
                            <button type="submit" class="btn btn-sm btn-success" style="width: 100%; margin-top: 10px;">
                                <i class="fas fa-plus"></i> Dodaj Spotkanie
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Edit Client Modal -->
    <div id="editModal" class="modal"
        style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
        <div class="modal-content"
            style="background-color:#fefefe; margin:5% auto; padding:20px; border:1px solid #888; width:80%; max-width:600px; border-radius:10px;">
            <span class="close" onclick="closeEditModal()"
                style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
            <h2 style="margin-bottom: 20px;">Edytuj Dane Klienta</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="update_client">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Imię i Nazwisko *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 5px;">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 5px;">Telefon</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Adres</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($client['address']); ?>"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 5px;">Nazwa Firmy</label>
                        <input type="text" name="company_name"
                            value="<?php echo htmlspecialchars($client['company_name']); ?>"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 5px;">NIP</label>
                        <input type="text" name="nip" value="<?php echo htmlspecialchars($client['nip']); ?>"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Zapisz
                    Zmiany</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal() { document.getElementById('editModal').style.display = 'block'; }
        function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
        window.onclick = function (event) {
            if (event.target == document.getElementById('editModal')) closeEditModal();
        }
    </script>
</body>

</html>