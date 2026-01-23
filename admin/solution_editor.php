<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$client_id = $_GET['client_id'] ?? 0;
// If creating new solution for client
if ($client_id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
} else {
    // If editing existing solution
    $solution_id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM client_solutions WHERE id = ?");
    $stmt->execute([$solution_id]);
    $solution = $stmt->fetch();

    if ($solution) {
        $client_id = $solution['client_id'];
        $stmt_client = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt_client->execute([$client_id]);
        $client = $stmt_client->fetch();
        $stored_items = json_decode($solution['items_json'], true);
    }
}

$message_id = $_GET['message_id'] ?? 0;
if ($message_id && !$solution_id) {
    // Creating new solution from message
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch();

    if ($message) {
        $client_id = $message['client_id']; // Ensure we stay with the same client

        // Refetch client to be sure
        $stmt_client = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt_client->execute([$client_id]);
        $client = $stmt_client->fetch();

        // Pre-fill data
        $prefill_title = "Rozwiązanie do zgłoszenia: " . $message['subject'];
        $prefill_desc = "Dotyczy zgłoszenia z dnia " . $message['created_at'] . ":\n" . $message['message'];
    }
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
            font-size: 1.5rem;
            font-weight: bold;
            border: none;
            border-bottom: 2px solid #eee;
            padding: 10px 0;
            margin-bottom: 20px;
            outline: none;
            color: #2c3e50;
            transition: border-color 0.3s;
        }

        .editor-header-input:focus {
            border-color: #3498db;
        }

        .editor-desc {
            width: 100%;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.3s;
        }

        .editor-desc:focus {
            border-color: #3498db;
            outline: none;
        }

        .items-container {
            flex: 1;
            margin-bottom: 30px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #e0e0e0;
        }

        .item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .item-row:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-color: #3498db;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.05rem;
        }

        .item-type {
            font-size: 0.8rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .item-price {
            font-weight: bold;
            font-size: 1.2rem;
            color: #27ae60;
            margin: 0 20px;
        }

        .remove-btn {
            color: #e74c3c;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .remove-btn:hover {
            background: #fcebeb;
        }

        .editor-footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-label {
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .total-val {
            font-size: 2rem;
            font-weight: 800;
            color: #2c3e50;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
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
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            color: #7f8c8d;
            border-bottom: 2px solid transparent;
        }

        .tab-btn.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }

        .picker-item {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .picker-item:hover {
            background: #f0f8ff;
        }

        .add-btn-main {
            background: #3498db;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transition: transform 0.2s;
        }

        .add-btn-main:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <main class="main-content" style="margin-left: 0; width: 100%; background: #f4f6f9; min-height: 100vh;">
            <div
                style="max-width: 1000px; margin: 0 auto 20px auto; padding: 20px 0 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0; font-size: 1.5rem; color: #2c3e50;">
                    <?php echo isset($solution) ? 'Edycja Propozycji' : 'Nowa Propozycja'; ?>
                    <span style="font-weight: 400; color: #7f8c8d; font-size: 1rem;">dla
                        <?php echo htmlspecialchars($client['name']); ?></span>
                </h1>
                <a href="client_view.php?id=<?php echo $client_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Zamknij
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div
                    style="max-width: 1000px; margin: 0 auto 20px auto; background: #fee; color: #c0392b; padding: 15px; border-radius: 8px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="solutionForm" class="editor-layout">
                <input type="hidden" name="items_json" id="itemsJsonInput">
                <input type="hidden" name="total_price" id="totalPriceInput">
                <?php if (isset($message_id) && $message_id): ?>
                    <input type="hidden" name="message_id" value="<?php echo $message_id; ?>">
                <?php endif; ?>

                <input type="text" name="title" class="editor-header-input"
                    placeholder="Tytuł propozycji (np. Naprawa laptopa Dell)" required
                    value="<?php echo isset($solution) ? htmlspecialchars($solution['title']) : (isset($prefill_title) ? htmlspecialchars($prefill_title) : ''); ?>">

                <textarea name="description" class="editor-desc" rows="3"
                    placeholder="Opis, zakres prac, uwagi dla klienta..."><?php echo isset($solution) ? htmlspecialchars($solution['description']) : (isset($prefill_desc) ? htmlspecialchars($prefill_desc) : ''); ?></textarea>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0; color: #2c3e50;">Pozycje i Kosztorys</h3>
                    <button type="button" class="add-btn-main" onclick="openPicker()">
                        <i class="fas fa-plus"></i> Dodaj pozycję
                    </button>
                </div>

                <div id="itemsContainer" class="items-container">
                    <!-- Items rendered via JS -->
                </div>

                <div class="editor-footer">
                    <div>
                        <select name="status" class="form-control"
                            style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            <option value="sent" <?php echo (isset($solution) && $solution['status'] == 'sent') ? 'selected' : ''; ?>>Status: Propozycja (Wysłana)</option>
                            <option value="accepted" <?php echo (isset($solution) && $solution['status'] == 'accepted') ? 'selected' : ''; ?>>Status: Zaakceptowana</option>
                        </select>
                    </div>
                    <div style="text-align: right;">
                        <div class="total-label">Suma całkowita</div>
                        <div class="total-val"><span id="displayTotal">0.00</span> zł</div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px; width: 100%;">
                            <i class="fas fa-save"></i> Zapisz Zmiany
                        </button>
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
                    <button type="button" class="tab-btn active" onclick="switchTab('products')">Produkty</button>
                    <button type="button" class="tab-btn" onclick="switchTab('services')">Usługi</button>
                    <button type="button" class="tab-btn" onclick="switchTab('custom')">Inne</button>
                </div>
                <span class="close" onclick="closePicker()" style="cursor: pointer; font-size: 1.5rem;">&times;</span>
            </div>

            <div style="padding: 0 20px;">
                <input type="text" id="pickerSearch" placeholder="Szukaj..."
                    style="width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #ddd; border-radius: 5px;">
            </div>

            <div class="modal-body" style="background: #fafafa; min-height: 300px;">
                <!-- Product Tab -->
                <div id="tab-products">
                    <?php foreach ($products as $p): ?>
                        <div class="picker-item" data-name="<?php echo strtolower($p['name']); ?>"
                            onclick="addItem('product', '<?php echo $p['id']; ?>', '<?php echo addslashes($p['name']); ?>', <?php echo $p['price']; ?>)">
                            <div>
                                <strong
                                    style="display: block; color: #2c3e50;"><?php echo htmlspecialchars($p['name']); ?></strong>
                                <small style="color: #7f8c8d;"><?php echo htmlspecialchars($p['category']); ?> | Stan:
                                    <?php echo $p['stock']; ?></small>
                            </div>
                            <strong style="color: #27ae60;"><?php echo number_format($p['price'], 2); ?> zł</strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Services Tab -->
                <div id="tab-services" style="display: none;">
                    <?php foreach ($services as $s): ?>
                        <div class="picker-item" data-name="<?php echo strtolower($s['name']); ?>"
                            onclick="addItem('service', '<?php echo $s['id']; ?>', '<?php echo addslashes($s['name']); ?>', <?php echo $s['price']; ?>)">
                            <div>
                                <strong
                                    style="display: block; color: #2c3e50;"><?php echo htmlspecialchars($s['name']); ?></strong>
                            </div>
                            <strong style="color: #27ae60;"><?php echo number_format($s['price'], 2); ?> zł</strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Custom Tab -->
                <div id="tab-custom" style="display: none; padding: 20px;">
                    <div class="form-group">
                        <label>Nazwa pozycji</label>
                        <input type="text" id="customName" class="form-control"
                            style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <div class="form-group">
                        <label>Cena (zł)</label>
                        <input type="number" id="customPrice" class="form-control"
                            style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <button type="button" class="btn btn-primary" style="width: 100%; padding: 12px;"
                        onclick="addCustomItem()">Dodaj pozycję</button>
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
            document.getElementById('tab-products').style.display = 'none';
            document.getElementById('tab-services').style.display = 'none';
            document.getElementById('tab-custom').style.display = 'none';

            document.getElementById('tab-' + tabName).style.display = 'block';

            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        function filterPicker() {
            const val = document.getElementById('pickerSearch').value.toLowerCase();
            document.querySelectorAll('.picker-item').forEach(el => {
                const name = el.getAttribute('data-name');
                if (name) {
                    el.style.display = name.includes(val) ? 'flex' : 'none';
                }
            });
        }

        function addItem(type, id, name, price) {
            items.push({ type, id, name, price: parseFloat(price) });
            renderItems();
            closePicker();
            // Optional: Show toast
        }

        function addCustomItem() {
            const name = document.getElementById('customName').value;
            const price = parseFloat(document.getElementById('customPrice').value);

            if (name && !isNaN(price)) {
                addItem('custom', 0, name, price);
                document.getElementById('customName').value = '';
                document.getElementById('customPrice').value = '';
            } else {
                alert('Proszę podać poprawną nazwę i cenę.');
            }
        }

        function removeItem(index) {
            items.splice(index, 1);
            renderItems();
        }

        function renderItems() {
            const container = document.getElementById('itemsContainer');
            container.innerHTML = '';

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p>Brak pozycji. Kliknij "Dodaj pozycję" aby rozpocząć.</p>
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
                div.innerHTML = `
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-type">${item.type === 'product' ? 'Produkt' : (item.type === 'service' ? 'Usługa' : 'Własna')}</div>
                    </div>
                    <div class="item-price">${item.price.toFixed(2)} zł</div>
                    <div class="remove-btn" onclick="removeItem(${index})">
                        <i class="fas fa-trash"></i>
                    </div>
                `;
                container.appendChild(div);
            });

            document.getElementById('displayTotal').textContent = total.toFixed(2);
            document.getElementById('totalPriceInput').value = total.toFixed(2);
            document.getElementById('itemsJsonInput').value = JSON.stringify(items);
        }

        renderItems();
    </script>
</body>

</html>