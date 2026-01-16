<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Filtrowanie
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM invoices WHERE 1=1";

if ($filter === 'faktura') {
    $sql .= " AND invoice_type = 'faktura'";
} elseif ($filter === 'paragon') {
    $sql .= " AND invoice_type = 'paragon'";
}

if ($search) {
    $sql .= " AND (invoice_number LIKE :search OR client_name LIKE :search)";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->execute([':search' => '%' . $search . '%']);
} else {
    $stmt->execute();
}
$invoices = $stmt->fetchAll();

// Statystyki
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN invoice_type = 'faktura' THEN 1 ELSE 0 END) as faktury,
    SUM(CASE WHEN invoice_type = 'paragon' THEN 1 ELSE 0 END) as paragony,
    SUM(total) as suma_total
FROM invoices");
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktury i Paragony - Panel Administracyjny</title>
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
                <a href="calculator.php" class="nav-link">
                    <i class="fas fa-calculator"></i>
                    <span>Kalkulator</span>
                </a>
                <a href="invoices.php" class="nav-link active">
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
                <h1><i class="fas fa-file-invoice"></i> Faktury i Paragony</h1>
                <a href="calculator.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Nowa Faktura/Paragon
                </a>
            </header>

            <!-- Statystyki -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Wszystkie dokumenty</h3>
                        <p class="stat-number"><?php echo $stats['total']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Faktury VAT</h3>
                        <p class="stat-number"><?php echo $stats['faktury']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Paragony</h3>
                        <p class="stat-number"><?php echo $stats['paragony']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Łączna wartość</h3>
                        <p class="stat-number"><?php echo number_format($stats['suma_total'], 2); ?> zł</p>
                    </div>
                </div>
            </div>

            <!-- Filtry i wyszukiwanie -->
            <div class="content-section">
                <div class="filters-row">
                    <div class="filters-left">
                        <a href="invoices.php?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                            Wszystkie
                        </a>
                        <a href="invoices.php?filter=faktura" class="filter-btn <?php echo $filter === 'faktura' ? 'active' : ''; ?>">
                            Faktury VAT
                        </a>
                        <a href="invoices.php?filter=paragon" class="filter-btn <?php echo $filter === 'paragon' ? 'active' : ''; ?>">
                            Paragony
                        </a>
                    </div>
                    
                    <form method="GET" class="search-form">
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <input type="text" name="search" placeholder="Szukaj po numerze lub kliencie..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista faktur -->
            <div class="content-section full-width">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Numer</th>
                                <th>Typ</th>
                                <th>Klient</th>
                                <th>Data</th>
                                <th>Kwota</th>
                                <th>Płatność</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                </td>
                                <td>
                                    <?php if ($invoice['invoice_type'] === 'faktura'): ?>
                                        <span class="type-badge faktura">Faktura VAT</span>
                                    <?php else: ?>
                                        <span class="type-badge paragon">Paragon</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($invoice['client_name']); ?>
                                    <?php if ($invoice['client_company']): ?>
                                        <br><small style="color: #666;"><?php echo htmlspecialchars($invoice['client_company']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($invoice['created_at'])); ?></td>
                                <td><strong><?php echo number_format($invoice['total'], 2); ?> zł</strong></td>
                                <td>
                                    <span class="payment-badge <?php echo $invoice['payment_method']; ?>">
                                        <?php echo ucfirst($invoice['payment_method']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $invoice['payment_status']; ?>">
                                        <?php echo ucfirst($invoice['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn-icon" title="Zobacz">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="generate_pdf.php?id=<?php echo $invoice['id']; ?>" class="btn-icon" title="PDF" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="delete_invoice.php?id=<?php echo $invoice['id']; ?>" class="btn-icon delete" title="Usuń" onclick="return confirm('Czy na pewno chcesz usunąć ten dokument?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($invoices)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-invoice"></i>
                    <h3>Brak dokumentów</h3>
                    <p>Nie znaleziono żadnych faktur ani paragonów</p>
                    <a href="calculator.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Utwórz pierwszy dokument
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.filters-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.filters-left {
    display: flex;
    gap: 10px;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #ddd;
    border-radius: 25px;
    text-decoration: none;
    color: #666;
    font-weight: 600;
    transition: all 0.3s ease;
}

.filter-btn:hover,
.filter-btn.active {
    border-color: #ff6b35;
    color: #ff6b35;
    background: rgba(255, 107, 53, 0.1);
}

.search-form {
    display: flex;
    gap: 10px;
}

.search-form input {
    padding: 10px 15px;
    border: 2px solid #ddd;
    border-radius: 25px;
    width: 300px;
}

.btn-search {
    padding: 10px 20px;
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
}

.type-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.type-badge.faktura {
    background: #e7f3ff;
    color: #004085;
}

.type-badge.paragon {
    background: #fff3cd;
    color: #856404;
}

.payment-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #f8f9fa;
    color: #666;
}

.status-badge.opłacona {
    background: #d4edda;
    color: #155724;
}

.status-badge.nieopłacona {
    background: #f8d7da;
    color: #721c24;
}
</style>