<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Pobierz usługi
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY name");
$services = $stmt->fetchAll();

// Pobierz produkty
$stmt = $pdo->query("SELECT * FROM products ORDER BY name");
$products = $stmt->fetchAll();

// Pobierz klientów
$stmt = $pdo->query("SELECT * FROM clients ORDER BY name");
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Wycen - Panel Administracyjny</title>
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
                <a href="calculator.php" class="nav-link active">
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
                <h1><i class="fas fa-calculator"></i> Kalkulator Wycen</h1>
                <div class="header-actions">
                    <a href="invoices.php" class="btn btn-secondary">
                        <i class="fas fa-file-invoice"></i>
                        Lista Faktur
                    </a>
                </div>
            </header>

            <div class="calculator-container">
                <!-- Sekcja Klienta -->
                <div class="calc-section">
                    <h2><i class="fas fa-user"></i> Dane Klienta</h2>
                    <div class="client-selector">
                        <button class="btn-toggle active" data-type="new">
                            <i class="fas fa-user-plus"></i> Nowy klient
                        </button>
                        <button class="btn-toggle" data-type="existing">
                            <i class="fas fa-users"></i> Istniejący klient
                        </button>
                    </div>

                    <div id="newClientForm" class="client-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="clientName">Imię i nazwisko *</label>
                                <input type="text" id="clientName" placeholder="Jan Kowalski">
                            </div>
                            <div class="form-group">
                                <label for="clientPhone">Telefon</label>
                                <input type="tel" id="clientPhone" placeholder="+48 123 456 789">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="clientEmail">Email</label>
                                <input type="email" id="clientEmail" placeholder="jan@email.pl">
                            </div>
                            <div class="form-group">
                                <label for="clientAddress">Adres</label>
                                <input type="text" id="clientAddress" placeholder="ul. Testowa 1, Warszawa">
                            </div>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="isCompany">
                                <span>Firma (faktura VAT)</span>
                            </label>
                        </div>

                        <div id="companyFields" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="companyName">Nazwa firmy</label>
                                    <input type="text" id="companyName" placeholder="ABC Sp. z o.o.">
                                </div>
                                <div class="form-group">
                                    <label for="companyNip">NIP</label>
                                    <input type="text" id="companyNip" placeholder="1234567890">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="existingClientForm" class="client-form" style="display: none;">
                        <div class="form-group">
                            <label for="selectClient">Wybierz klienta</label>
                            <select id="selectClient">
                                <option value="">-- Wybierz klienta --</option>
                                <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($client['name']); ?>"
                                        data-email="<?php echo htmlspecialchars($client['email']); ?>"
                                        data-phone="<?php echo htmlspecialchars($client['phone']); ?>"
                                        data-address="<?php echo htmlspecialchars($client['address']); ?>"
                                        data-nip="<?php echo htmlspecialchars($client['nip']); ?>"
                                        data-company="<?php echo htmlspecialchars($client['company_name']); ?>">
                                    <?php echo htmlspecialchars($client['name']); ?>
                                    <?php if ($client['company_name']): ?>
                                        (<?php echo htmlspecialchars($client['company_name']); ?>)
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Kalkulator pozycji -->
                <div class="calc-section">
                    <h2><i class="fas fa-list"></i> Pozycje na dokumencie</h2>
                    
                    <div class="add-item-section">
                        <div class="tabs">
                            <button class="tab active" data-tab="service">
                                <i class="fas fa-tools"></i> Usługa
                            </button>
                            <button class="tab" data-tab="product">
                                <i class="fas fa-box"></i> Produkt
                            </button>
                            <button class="tab" data-tab="custom">
                                <i class="fas fa-plus"></i> Własna pozycja
                            </button>
                        </div>

                        <!-- Tab: Usługa -->
                        <div id="serviceTab" class="tab-content active">
                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <label>Wybierz usługę</label>
                                    <select id="selectService">
                                        <option value="">-- Wybierz usługę --</option>
                                        <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($service['name']); ?>"
                                                data-price="<?php echo $service['discount_price'] ?: $service['price']; ?>">
                                            <?php echo htmlspecialchars($service['name']); ?> - 
                                            <?php echo number_format($service['discount_price'] ?: $service['price'], 2); ?> zł
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ilość</label>
                                    <input type="number" id="serviceQty" value="1" min="1" step="1">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary" onclick="addServiceItem()">
                                        <i class="fas fa-plus"></i> Dodaj
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Produkt -->
                        <div id="productTab" class="tab-content">
                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <label>Wybierz produkt</label>
                                    <select id="selectProduct">
                                        <option value="">-- Wybierz produkt --</option>
                                        <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                data-price="<?php echo $product['price']; ?>"
                                                data-stock="<?php echo $product['stock']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?> - 
                                            <?php echo number_format($product['price'], 2); ?> zł
                                            (<?php echo $product['stock']; ?> szt.)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ilość</label>
                                    <input type="number" id="productQty" value="1" min="1" step="1">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary" onclick="addProductItem()">
                                        <i class="fas fa-plus"></i> Dodaj
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Własna pozycja -->
                        <div id="customTab" class="tab-content">
                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <label>Nazwa</label>
                                    <input type="text" id="customName" placeholder="Np. Części zamienne">
                                </div>
                                <div class="form-group">
                                    <label>Cena jedn.</label>
                                    <input type="number" id="customPrice" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="form-group">
                                    <label>Ilość</label>
                                    <input type="number" id="customQty" value="1" min="1" step="1">
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary" onclick="addCustomItem()">
                                        <i class="fas fa-plus"></i> Dodaj
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista pozycji -->
                    <div class="items-list" id="itemsList">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Brak pozycji. Dodaj usługę, produkt lub własną pozycję.</p>
                        </div>
                    </div>
                </div>

                <!-- Podsumowanie -->
                <div class="calc-section summary-section">
                    <h2><i class="fas fa-receipt"></i> Podsumowanie</h2>
                    
                    <div class="summary-grid">
                        <div class="summary-row">
                            <span>Netto:</span>
                            <strong id="subtotalAmount">0.00 zł</strong>
                        </div>
                        <div class="summary-row">
                            <span>VAT (23%):</span>
                            <strong id="taxAmount">0.00 zł</strong>
                        </div>
                        <div class="summary-row total">
                            <span>RAZEM:</span>
                            <strong id="totalAmount">0.00 zł</strong>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Typ dokumentu</label>
                            <select id="invoiceType">
                                <option value="paragon">Paragon</option>
                                <option value="faktura">Faktura VAT</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Metoda płatności</label>
                            <select id="paymentMethod">
                                <option value="gotówka">Gotówka</option>
                                <option value="karta">Karta</option>
                                <option value="przelew">Przelew</option>
                                <option value="blik">BLIK</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Uwagi</label>
                        <textarea id="notes" rows="3" placeholder="Dodatkowe informacje..."></textarea>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-success" onclick="saveInvoice()">
                            <i class="fas fa-save"></i> Zapisz i generuj dokument
                        </button>
                        <button class="btn btn-secondary" onclick="clearCalculator()">
                            <i class="fas fa-times"></i> Wyczyść
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="calculator.js"></script>
</body>
</html>

<style>
.calculator-container {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.calc-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.calc-section h2 {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.client-selector {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.btn-toggle {
    flex: 1;
    padding: 15px;
    border: 2px solid #ddd;
    background: white;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.btn-toggle:hover {
    border-color: #ff6b35;
}

.btn-toggle.active {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    color: white;
    border-color: #ff6b35;
}

.client-form {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #ff6b35;
}

.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #ecf0f1;
}

.tab {
    padding: 12px 20px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 600;
    color: #666;
    transition: all 0.3s ease;
}

.tab:hover {
    color: #ff6b35;
}

.tab.active {
    color: #ff6b35;
    border-bottom-color: #ff6b35;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

.items-list {
    min-height: 200px;
    margin-top: 25px;
}

.item-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.item-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.item-info {
    flex: 1;
}

.item-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.item-details {
    font-size: 0.9rem;
    color: #666;
}

.item-price {
    font-size: 1.3rem;
    font-weight: 700;
    color: #ff6b35;
    margin-right: 15px;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    background: #c82333;
    transform: scale(1.1);
}

.summary-grid {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #dee2e6;
}

.summary-row.total {
    border-bottom: none;
    font-size: 1.3rem;
    padding-top: 15px;
    border-top: 2px solid #ff6b35;
    margin-top: 10px;
}

.summary-row.total strong {
    color: #ff6b35;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>