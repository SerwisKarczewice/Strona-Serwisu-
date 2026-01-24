<?php
require_once '../config.php';
define('SILENT_MIGRATION', true);
require_once 'migration_clients.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$client_id = $_GET['client_id'] ?? 0;
$solution_id = $_GET['id'] ?? 0;
$message_id = $_GET['message_id'] ?? 0;
$client = null;
$solution = null;
$message = null;

// If editing existing solution
if ($solution_id) {
    $stmt = $pdo->prepare("SELECT * FROM client_solutions WHERE id = ?");
    $stmt->execute([$solution_id]);
    $solution = $stmt->fetch();

    if ($solution) {
        $client_id = $solution['client_id'];
        $stored_items = json_decode($solution['items_json'], true);
    }
}

// If creating from message
if ($message_id && !$solution_id) {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch();

    if ($message) {
        $client_id = $message['client_id']; // Use message's client_id
        // Pre-fill data
        $prefill_title = "Rozwiązanie do zgłoszenia: " . $message['subject'];
        $prefill_desc = "Dotyczy zgłoszenia z dnia " . $message['created_at'] . ":\n" . $message['message'];
    }
}

// Always fetch the client
if ($client_id) {
    $stmt_client = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt_client->execute([$client_id]);
    $client = $stmt_client->fetch();
}

if (!$client) {
    die("Nie znaleziono klienta.");
}

// Fetch available products and services for the picker
$products = $pdo->query("SELECT id, name, price, stock, category FROM products WHERE is_visible = 1 ORDER BY name")->fetchAll();
$services = $pdo->query("SELECT id, name, price FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $json_raw = $_POST['items_json'] ?? '[]';
    if (empty($json_raw))
        $json_raw = '[]';

    $total = floatval($_POST['total_price']);
    $status = $_POST['status'];

    try {
        if (isset($solution)) {
            // Update
            $stmt = $pdo->prepare("UPDATE client_solutions SET title=?, description=?, items_json=?, total_price=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$title, $description, $json_raw, $total, $status, $solution['id']]);
        } else {
            // Create
            $msg_id_insert = isset($_POST['message_id']) && !empty($_POST['message_id']) ? $_POST['message_id'] : null;
            $stmt = $pdo->prepare("INSERT INTO client_solutions (client_id, message_id, title, description, items_json, total_price, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$client_id, $msg_id_insert, $title, $description, $json_raw, $total, $status]);
        }
        header("Location: client_view.php?id=$client_id");
        exit;
    } catch (PDOException $e) {
        $error = "Błąd zapisu bazy danych: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytor Propozycji - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .editor-layout {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
            min-height: calc(100vh - 140px);
            display: flex;
            flex-direction: column;
        }

        .editor-header-input {
            width: 100%;
            font-size: 1.3rem;
            font-weight: 600;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 12px 15px;
            outline: none;
            color: #2c3e50;
            transition: all 0.3s;
        }

        .editor-header-input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .editor-desc {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            font-family: inherit;
            resize: vertical;
            transition: all 0.3s;
            color: #2c3e50;
            line-height: 1.5;
        }

        .editor-desc:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        .items-container {
            flex: 1;
            margin-bottom: 30px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            min-height: 200px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: #95a5a6;
            border-radius: 8px;
            border: 2px dashed #e0e0e0;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .item-row:hover {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border-color: #3498db;
            transform: translateY(-1px);
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.05rem;
            margin-bottom: 3px;
        }

        .item-type {
            font-size: 0.75rem;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #ecf0f1;
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            margin-top: 3px;
        }

        .item-price {
            font-weight: 800;
            font-size: 1.2rem;
            color: #27ae60;
            margin: 0 25px;
            white-space: nowrap;
        }

        .remove-btn {
            color: #e74c3c;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.2s;
            background: #fadbd8;
            border: none;
            font-size: 0.9rem;
        }

        .remove-btn:hover {
            background: #f5b7b1;
            transform: scale(1.05);
        }

        .editor-footer {
            border-top: 2px solid #ecf0f1;
            padding-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 30px;
            background: #f8f9fa;
            padding: 25px;
            margin: 0 -30px -30px -30px;
            border-radius: 0 0 10px 10px;
        }

        .total-label {
            font-size: 0.85rem;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
        }

        .total-val {
            font-size: 2.2rem;
            font-weight: 800;
            color: #2c3e50;
            margin-top: 5px;
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
            backdrop-filter: blur(2px);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 600px;
            max-width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 80vh;
        }

        .modal-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 600;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: #3498db;
        }

        .tab-btn.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }

        .picker-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }

        .picker-item:hover {
            background: #f0f8ff;
            padding-left: 20px;
        }

        .add-btn-main {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 11px 25px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.2s;
        }

        .add-btn-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        @media print {
            .editor-footer {
                display: none !important;
            }
            .add-btn-main {
                display: none !important;
            }
            body {
                background: white !important;
            }
            .admin-wrapper {
                margin: 0 !important;
            }
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <main class="main-content" style="margin-left: 0; width: 100%; background: #f4f6f9; min-height: 100vh;">
            <div style="max-width: 1000px; margin: 0 auto 20px auto; padding: 20px 0 0 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h1 style="margin: 0 0 5px 0; font-size: 1.5rem; color: #2c3e50;">
                            <?php echo isset($solution) ? 'Edycja Propozycji' : 'Nowa Propozycja'; ?>
                        </h1>
                        <p style="margin: 0; color: #7f8c8d; font-size: 0.95rem;">
                            <i class="fas fa-user-circle"></i> dla
                            <?php echo htmlspecialchars($client['name']); ?>
                        </p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <?php if (isset($solution)): ?>
                            <a href="generate_pdf.php?type=solution&id=<?php echo $solution['id']; ?>" target="_blank" class="btn btn-sm btn-secondary" title="Pobierz PDF">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="window.print()" title="Drukuj">
                                <i class="fas fa-print"></i> Druk
                            </button>
                        <?php endif; ?>
                        <a href="client_view.php?id=<?php echo $client_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Zamknij
                        </a>
                    </div>
            </div>

            <?php if (isset($error)): ?>
                <div
                    style="max-width: 1000px; margin: 0 auto 20px auto; background: #fee; color: #c0392b; padding: 15px; border-radius: 8px; border-left: 4px solid #c0392b;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="solutionForm" class="editor-layout">
                <input type="hidden" name="items_json" id="itemsJsonInput">
                <input type="hidden" name="total_price" id="totalPriceInput">
                <?php if (isset($message_id) && $message_id): ?>
                    <input type="hidden" name="message_id" value="<?php echo $message_id; ?>">
                <?php endif; ?>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #eee;">
                    <label style="color: #2c3e50; font-weight: 700; display: block; margin-bottom: 8px; font-size: 0.95rem;">📝 Tytuł Propozycji *</label>
                    <input type="text" name="title" class="editor-header-input"
                        placeholder="np. Naprawa laptopa Dell, Przegląd systemu" required
                        value="<?php echo isset($solution) ? htmlspecialchars($solution['title']) : (isset($prefill_title) ? htmlspecialchars($prefill_title) : ''); ?>"
                        style="background: white; border-radius: 5px; padding: 12px; margin-bottom: 15px;">

                    <label style="color: #2c3e50; font-weight: 700; display: block; margin-bottom: 8px; font-size: 0.95rem;">📋 Opis i Szczegóły</label>
                    <textarea name="description" class="editor-desc" rows="4"
                        placeholder="Opis, zakres prac, gwarancje, uwagi dla klienta..." style="background: white;"><?php echo isset($solution) ? htmlspecialchars($solution['description']) : (isset($prefill_desc) ? htmlspecialchars($prefill_desc) : ''); ?></textarea>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="margin: 0 0 5px 0; color: #2c3e50;">
                            <i class="fas fa-list"></i> Pozycje Kosztorysu
                        </h3>
                        <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">Dodaj produkty i usługi</p>
                    </div>
                    <button type="button" class="add-btn-main" onclick="openPicker()">
                        <i class="fas fa-plus"></i> Dodaj pozycję
                    </button>
                </div>

                <div id="itemsContainer" class="items-container">
                    <!-- Items rendered via JS -->
                </div>

                <div class="editor-footer">
                    <div style="flex: 1;">
                        <label style="color: #7f8c8d; font-weight: 600; display: block; margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase;">📊 Status Propozycji</label>
                        <select name="status" class="form-control"
                            style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: white; font-weight: 500;">
                            <option value="new" <?php echo (isset($solution) && $solution['status'] == 'new') ? 'selected' : ''; ?>>🔄 Nowa (Wersja Robocza)</option>
                            <option value="sent" <?php echo (isset($solution) && $solution['status'] == 'sent') ? 'selected' : ''; ?>>📤 Wysłana do Klienta</option>
                            <option value="accepted" <?php echo (isset($solution) && $solution['status'] == 'accepted') ? 'selected' : ''; ?>>✅ Zaakceptowana</option>
                            <option value="rejected" <?php echo (isset($solution) && $solution['status'] == 'rejected') ? 'selected' : ''; ?>>❌ Odrzucona</option>
                        </select>
                    </div>
                    <div style="text-align: right;">
                        <div class="total-label">💰 Suma całkowita</div>
                        <div class="total-val" style="color: #27ae60;"><span id="displayTotal">0.00</span> zł</div>
                        <div style="display: flex; gap: 10px; margin-top: 15px;">
                            <a href="client_view.php?id=<?php echo $client_id; ?>" class="btn btn-sm btn-secondary" style="flex: 0; padding: 10px 15px;">
                                <i class="fas fa-arrow-left"></i> Powrót
                            </a>
                            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 10px 20px;">
                                <i class="fas fa-save"></i> Zapisz Propozycję
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Picker Modal -->
    <div id="pickerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="tabs">
                    <button type="button" class="tab-btn active" onclick="switchTab('products')"><i class="fas fa-box"></i> Produkty</button>
                    <button type="button" class="tab-btn" onclick="switchTab('services')"><i class="fas fa-wrench"></i> Usługi</button>
                    <button type="button" class="tab-btn" onclick="switchTab('custom')"><i class="fas fa-plus-circle"></i> Własne</button>
                </div>
                <span class="close" onclick="closePicker()" style="cursor: pointer; font-size: 1.5rem; color: #7f8c8d;">&times;</span>
            </div>

            <div style="padding: 0 20px;">
                <input type="text" id="pickerSearch" placeholder="🔍 Szukaj produktu lub usługi..." 
                    oninput="filterPicker()"
                    style="width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 0.95rem;">
            </div>

            <div class="modal-body" style="background: #fafafa; min-height: 300px;">
                <!-- Product Tab -->
                <div id="tab-products">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <div class="picker-item" data-name="<?php echo strtolower($p['name']); ?>"
                                onclick="addItem('product', '<?php echo $p['id']; ?>', '<?php echo addslashes($p['name']); ?>', <?php echo $p['price']; ?>)">
                                <div>
                                    <strong style="display: block; color: #2c3e50; margin-bottom: 3px;"><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <small style="color: #95a5a6;"><i class="fas fa-folder"></i> <?php echo htmlspecialchars($p['category']); ?> • Stan: <strong><?php echo $p['stock']; ?></strong></small>
                                </div>
                                <strong style="color: #27ae60; font-size: 1.1rem;"><?php echo number_format($p['price'], 2); ?> zł</strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 20px; color: #95a5a6;">
                            <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                            <p>Brak dostępnych produktów</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Services Tab -->
                <div id="tab-services" style="display: none;">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $s): ?>
                            <div class="picker-item" data-name="<?php echo strtolower($s['name']); ?>"
                                onclick="addItem('service', '<?php echo $s['id']; ?>', '<?php echo addslashes($s['name']); ?>', <?php echo $s['price']; ?>)">
                                <div>
                                    <strong style="display: block; color: #2c3e50; margin-bottom: 3px;"><?php echo htmlspecialchars($s['name']); ?></strong>
                                    <small style="color: #95a5a6;"><i class="fas fa-star"></i> Usługa</small>
                                </div>
                                <strong style="color: #27ae60; font-size: 1.1rem;"><?php echo number_format($s['price'], 2); ?> zł</strong>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 20px; color: #95a5a6;">
                            <i class="fas fa-tools" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                            <p>Brak dostępnych usług</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Custom Tab -->
                <div id="tab-custom" style="display: none; padding: 30px 20px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Nazwa pozycji</label>
                        <input type="text" id="customName" class="form-control"
                            placeholder="np. Konfiguracja sieci, Szkolenie"
                            style="width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #2c3e50;">Cena (zł)</label>
                        <input type="number" id="customPrice" step="0.01" min="0" class="form-control"
                            placeholder="0.00"
                            style="width: 100%; padding: 12px; margin-bottom: 25px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <button type="button" class="add-btn-main" style="width: 100%; justify-content: center;"
                        onclick="addCustomItem()"><i class="fas fa-plus"></i> Dodaj pozycję</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let items = <?php echo isset($stored_items) ? json_encode($stored_items) : '[]'; ?>;
        const modal = document.getElementById('pickerModal');

        function openPicker() {
            modal.style.display = 'block';
            document.getElementById('pickerSearch').focus();
        }

        function closePicker() {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                closePicker();
            }
        }

        function switchTab(tabName) {
            // Hide all tabs
            document.getElementById('tab-products').style.display = 'none';
            document.getElementById('tab-services').style.display = 'none';
            document.getElementById('tab-custom').style.display = 'none';

            // Show selected tab
            document.getElementById('tab-' + tabName).style.display = 'block';

            // Update button states
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        function filterPicker() {
            const val = document.getElementById('pickerSearch').value.toLowerCase();
            let visibleCount = 0;
            
            document.querySelectorAll('.picker-item').forEach(el => {
                const name = el.getAttribute('data-name');
                if (name && name.includes(val)) {
                    el.style.display = 'flex';
                    visibleCount++;
                } else {
                    el.style.display = 'none';
                }
            });
        }

        function addItem(type, id, name, price) {
            items.push({ type, id, name, price: parseFloat(price) });
            renderItems();
            closePicker();
            showNotification(`✓ "${name}" dodane do kosztorysu`);
        }

        function addCustomItem() {
            const name = document.getElementById('customName').value.trim();
            const price = parseFloat(document.getElementById('customPrice').value);

            if (!name) {
                showNotification('Podaj nazwę pozycji', 'error');
                return;
            }

            if (isNaN(price) || price < 0) {
                showNotification('Podaj prawidłową cenę', 'error');
                return;
            }

            addItem('custom', 0, name, price);
            document.getElementById('customName').value = '';
            document.getElementById('customPrice').value = '';
            document.getElementById('customName').focus();
        }

        function removeItem(index) {
            const itemName = items[index].name;
            items.splice(index, 1);
            renderItems();
            showNotification(`✓ "${itemName}" usunięte`);
        }

        function renderItems() {
            const container = document.getElementById('itemsContainer');
            container.innerHTML = '';

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-boxes" style="font-size: 3rem; margin-bottom: 15px;"></i>
                        <p style="font-size: 1.1rem; margin-bottom: 10px;">Brak pozycji w kosztorysie</p>
                        <p style="font-size: 0.9rem; opacity: 0.7;">Kliknij przycisk "Dodaj pozycję" aby rozpocząć tworzenie</p>
                    </div>
                `;
                document.getElementById('displayTotal').textContent = '0.00';
                document.getElementById('totalPriceInput').value = 0;
                document.getElementById('itemsJsonInput').value = '[]';
                return;
            }

            let total = 0;
            items.forEach((item, index) => {
                total += item.price;
                const div = document.createElement('div');
                div.className = 'item-row';
                const typeIcon = item.type === 'product' ? '📦' : (item.type === 'service' ? '🔧' : '✏️');
                const typeLabel = item.type === 'product' ? 'Produkt' : (item.type === 'service' ? 'Usługa' : 'Własna');
                
                div.innerHTML = `
                    <div class="item-info">
                        <div class="item-name">${escapeHtml(item.name)}</div>
                        <div class="item-type">${typeIcon} ${typeLabel}</div>
                    </div>
                    <div class="item-price">${item.price.toFixed(2)} zł</div>
                    <button type="button" class="remove-btn" onclick="removeItem(${index})" title="Usuń pozycję">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                `;
                container.appendChild(div);
            });

            document.getElementById('displayTotal').textContent = total.toFixed(2);
            document.getElementById('totalPriceInput').value = total.toFixed(2);
            document.getElementById('itemsJsonInput').value = JSON.stringify(items);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: ${type === 'error' ? '#e74c3c' : '#27ae60'};
                color: white;
                padding: 12px 20px;
                border-radius: 5px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                animation: slideIn 0.3s ease;
                font-weight: 500;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }

        // Call on page load to render items
        renderItems();

        // Add CSS animation for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>