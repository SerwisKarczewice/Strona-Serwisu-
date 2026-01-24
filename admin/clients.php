<?php
require_once '../config.php';
define('SILENT_MIGRATION', true);
require_once 'migration_clients.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Logic to add a new client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_client') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $company = trim($_POST['company_name']);
    $nip = trim($_POST['nip']);
    $notes = trim($_POST['notes']);

    if ($name) {
        // Dedup Check
        $phone_norm = preg_replace('/[\s\-\(\)]+/', '', $phone);

        // Complex WHERE clause for cleaned phone matching
        $sql = "SELECT id FROM clients 
                WHERE (REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', '') = ? AND phone != '') 
                OR (email = ? AND email != '')";

        $check = $pdo->prepare($sql);
        $check->execute([$phone_norm, $email]);
        if ($check->fetchColumn()) {
            header('Location: clients.php?error=duplicate');
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO clients (name, email, phone, address, company_name, nip, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $phone, $address, $company, $nip, $notes]);
        header('Location: clients.php?success=added');
        exit;
    }
}

// Search & Pagination
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR company_name LIKE ?)";
    $params = array_fill(0, 4, "%$search%");
}

$stmt = $pdo->prepare("SELECT * FROM clients $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$clients = $stmt->fetchAll();

$total = $pdo->prepare("SELECT COUNT(*) FROM clients $where");
$total->execute($params);
$total_clients = $total->fetchColumn();
$total_pages = ceil($total_clients / $limit);

?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klienci - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .client-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }

        .client-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-color: #3498db;
        }

        .client-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .client-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .client-actions {
            display: flex;
            gap: 10px;
        }

        .badge-company {
            background: #e8f6ff;
            color: #3498db;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .client-meetings {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
            font-size: 0.85rem;
        }

        .meeting-badge {
            background: #f0e6ff;
            color: #8e44ad;
            padding: 4px 10px;
            border-radius: 12px;
            display: inline-block;
            margin: 2px 5px 2px 0;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Baza Klientów</h1>
                <button class="btn btn-primary" onclick="openModal()">
                    <i class="fas fa-user-plus"></i> Dodaj Klienta
                </button>
            </header>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'duplicate'): ?>
                <div class="alert alert-danger"
                    style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <strong>Błąd!</strong> Klient o takim numerze telefonu lub adresie email już istnieje.
                </div>
            <?php endif; ?>

            <div class="content-section full-width">
                <!-- Search -->
                <form class="search-bar" style="max-width: 100%; margin-bottom: 20px; display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Szukaj klienta (imię, email, telefon)..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Szukaj</button>
                </form>

                <div class="clients-list">
                    <?php if (empty($clients)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users-slash" style="font-size: 3rem; color: #ddd; margin-bottom: 10px;"></i>
                            <p>Brak klientów w bazie.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($clients as $client): ?>
                            <?php
                            // Fetch upcoming meetings for this client
                            $stmt_meetings = null;
                            $upcoming_meetings = [];
                            try {
                                $stmt_meetings = $pdo->prepare("SELECT * FROM client_meetings WHERE client_id = ? AND meeting_date >= CURDATE() ORDER BY meeting_date ASC LIMIT 2");
                                $stmt_meetings->execute([$client['id']]);
                                $upcoming_meetings = $stmt_meetings->fetchAll();
                            } catch (Exception $e) {
                                // Table might not exist yet
                            }
                            ?>
                            <div class="client-card" onclick="location.href='client_view.php?id=<?php echo $client['id']; ?>'"
                                style="cursor: pointer;">
                                <div class="client-info" style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <h4>
                                            <?php echo htmlspecialchars($client['name']); ?>
                                        </h4>
                                        <?php if ($client['company_name']): ?>
                                            <span class="badge-company"><i class="fas fa-building"></i>
                                                <?php echo htmlspecialchars($client['company_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p>
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($client['email'] ?: 'Brak email'); ?> |
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($client['phone'] ?: 'Brak telefonu'); ?>
                                    </p>
                                    <?php if (!empty($upcoming_meetings)): ?>
                                        <div class="client-meetings">
                                            <i class="fas fa-calendar-check" style="color: #8e44ad; margin-right: 5px;"></i>
                                            <strong>Nadchodzące spotkania:</strong>
                                            <?php foreach ($upcoming_meetings as $meeting): ?>
                                                <span class="meeting-badge">
                                                    <i class="far fa-calendar"></i>
                                                    <?php echo date('d.m.Y', strtotime($meeting['meeting_date'])); ?>
                                                    <i class="far fa-clock" style="margin-left: 5px;"></i>
                                                    <?php echo date('H:i', strtotime($meeting['meeting_time'])); ?>
                                                    - <?php echo htmlspecialchars($meeting['title']); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="client-actions">
                                    <a href="client_view.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-secondary"
                                        title="Szczegóły">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                                class="<?php echo $page == $i ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Client Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="margin-bottom: 20px;">Dodaj Nowego Klienta</h2>
            <form method="POST" class="admin-form">
                <input type="hidden" name="action" value="add_client">

                <div class="form-group">
                    <label>Imię i Nazwisko *</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="text" name="phone">
                    </div>
                </div>

                <div class="form-group">
                    <label>Adres</label>
                    <input type="text" name="address">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nazwa Firmy</label>
                        <input type="text" name="company_name">
                    </div>
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" name="nip">
                    </div>
                </div>

                <div class="form-group">
                    <label>Notatki</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Zapisz Klienta</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("addModal");
        function openModal() { modal.style.display = "block"; }
        function closeModal() { modal.style.display = "none"; }
        window.onclick = function (event) { if (event.target == modal) closeModal(); }
    </script>
</body>

</html>