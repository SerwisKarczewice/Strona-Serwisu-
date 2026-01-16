<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: invoices.php');
    exit;
}

$invoiceId = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = :id");
$stmt->execute([':id' => $invoiceId]);
$invoice = $stmt->fetch();

if (!$invoice) {
    header('Location: invoices.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
$stmt->execute([':id' => $invoiceId]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podgląd - <?php echo $invoice['invoice_number']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
      <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-file-invoice"></i> <?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
                <div class="header-actions">
                    <a href="generate_pdf.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        Generuj PDF
                    </a>
                    <a href="invoices.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Powrót
                    </a>
                </div>
            </header>

            <div class="invoice-view">
                <div class="invoice-header-card">
                    <div class="invoice-type-badge <?php echo $invoice['invoice_type']; ?>">
                        <?php echo $invoice['invoice_type'] === 'faktura' ? 'Faktura VAT' : 'Paragon'; ?>
                    </div>
                    <h2><?php echo htmlspecialchars($invoice['invoice_number']); ?></h2>
                    <p class="invoice-date">
                        <i class="fas fa-calendar"></i>
                        <?php echo date('d.m.Y H:i', strtotime($invoice['created_at'])); ?>
                    </p>
                </div>

                <div class="invoice-parties">
                    <div class="party-card">
                        <h3><i class="fas fa-user"></i> Klient</h3>
                        <div class="party-details">
                            <?php if ($invoice['client_company']): ?>
                                <p><strong><?php echo htmlspecialchars($invoice['client_company']); ?></strong></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($invoice['client_name']); ?></p>
                            <?php if ($invoice['client_address']): ?>
                                <p><?php echo htmlspecialchars($invoice['client_address']); ?></p>
                            <?php endif; ?>
                            <?php if ($invoice['client_nip']): ?>
                                <p>NIP: <?php echo htmlspecialchars($invoice['client_nip']); ?></p>
                            <?php endif; ?>
                            <?php if ($invoice['client_phone']): ?>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($invoice['client_phone']); ?></p>
                            <?php endif; ?>
                            <?php if ($invoice['client_email']): ?>
                                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($invoice['client_email']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="party-card">
                        <h3><i class="fas fa-info-circle"></i> Płatność</h3>
                        <div class="party-details">
                            <p>
                                <strong>Metoda:</strong><br>
                                <span class="payment-badge"><?php echo ucfirst($invoice['payment_method']); ?></span>
                            </p>
                            <p>
                                <strong>Status:</strong><br>
                                <span class="status-badge <?php echo $invoice['payment_status']; ?>">
                                    <?php echo ucfirst($invoice['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="invoice-items-card">
                    <h3><i class="fas fa-list"></i> Pozycje</h3>
                    <div class="table-responsive">
                        <table class="invoice-items-table">
                            <thead>
                                <tr>
                                    <th>Lp.</th>
                                    <th>Nazwa</th>
                                    <th>Ilość</th>
                                    <th>Cena jedn.</th>
                                    <th>Wartość</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $counter = 1;
                                foreach ($items as $item): 
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <br><span class="item-type"><?php echo ucfirst($item['item_type']); ?></span>
                                    </td>
                                    <td><?php echo number_format($item['quantity'], 2); ?></td>
                                    <td><?php echo number_format($item['unit_price'], 2); ?> zł</td>
                                    <td><strong><?php echo number_format($item['total'], 2); ?> zł</strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="invoice-summary-card">
                    <h3><i class="fas fa-calculator"></i> Podsumowanie</h3>
                    <div class="summary-rows">
                        <div class="summary-row">
                            <span>Netto:</span>
                            <strong><?php echo number_format($invoice['subtotal'], 2); ?> zł</strong>
                        </div>
                        <div class="summary-row">
                            <span>VAT (23%):</span>
                            <strong><?php echo number_format($invoice['tax'], 2); ?> zł</strong>
                        </div>
                        <div class="summary-row total">
                            <span>RAZEM:</span>
                            <strong><?php echo number_format($invoice['total'], 2); ?> zł</strong>
                        </div>
                    </div>
                </div>

                <?php if ($invoice['notes']): ?>
                <div class="invoice-notes-card">
                    <h3><i class="fas fa-sticky-note"></i> Uwagi</h3>
                    <p><?php echo nl2br(htmlspecialchars($invoice['notes'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

<style>
.invoice-view {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.invoice-header-card {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
}

.invoice-type-badge {
    display: inline-block;
    padding: 8px 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.invoice-header-card h2 {
    font-size: 2rem;
    margin-bottom: 10px;
}

.invoice-date {
    opacity: 0.9;
    font-size: 1.1rem;
}

.invoice-parties {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.party-card,
.invoice-items-card,
.invoice-summary-card,
.invoice-notes-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.party-card h3,
.invoice-items-card h3,
.invoice-summary-card h3,
.invoice-notes-card h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.party-details p {
    margin-bottom: 8px;
    color: #666;
    line-height: 1.6;
}

.payment-badge {
    display: inline-block;
    padding: 6px 15px;
    background: #e7f3ff;
    color: #004085;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: 600;
}

.invoice-items-table {
    width: 100%;
    border-collapse: collapse;
}

.invoice-items-table thead {
    background: #f8f9fa;
}

.invoice-items-table th {
    padding: 12px;
    text-align: left;
    font-size: 0.9rem;
    color: #2c3e50;
    border-bottom: 2px solid #e0e0e0;
}

.invoice-items-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.item-type {
    font-size: 0.85rem;
    color: #666;
    background: #f8f9fa;
    padding: 3px 8px;
    border-radius: 8px;
}

.summary-rows {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 1.1rem;
}

.summary-row.total {
    border-bottom: none;
    border-top: 2px solid #ff6b35;
    padding-top: 15px;
    margin-top: 10px;
    font-size: 1.4rem;
}

.summary-row.total strong {
    color: #ff6b35;
}

.invoice-notes-card p {
    color: #666;
    line-height: 1.8;
}

@media (max-width: 768px) {
    .invoice-parties {
        grid-template-columns: 1fr;
    }
}
</style>