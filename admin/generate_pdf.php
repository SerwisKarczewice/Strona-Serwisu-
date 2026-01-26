<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die('Brak ID');
}

$type = $_GET['type'] ?? 'invoice';
$id = intval($_GET['id']);

// Dane firmy (wspólne)
$companyData = [
    'name' => 'Serwis komputerowy Karczewice',
    'address' => ' ul. Nadrzeczna 3b',
    'city' => '42-270 Karczewice',
    'nip' => ' ',
    'phone' => '+48 662 993 490 / 536 200 332',
    'email' => 'SerwisBiuroKarczewice@gmail.com'
];

if ($type === 'solution') {
    // -- LOGIKA DLA ROZWIĄZAŃ (OFERT) --
    $stmt = $pdo->prepare("SELECT s.*, c.name as client_name, c.company_name as client_company, c.address as client_address, c.email as client_email, c.phone as client_phone 
                           FROM client_solutions s 
                           JOIN clients c ON s.client_id = c.id 
                           WHERE s.id = :id");
    $stmt->execute([':id' => $id]);
    $solution = $stmt->fetch();

    if (!$solution)
        die('Rozwiązanie nie istnieje');
    $items = json_decode($solution['items_json'], true);

    // Helper function to force wrap text for PDF
    function format_pdf_text($text, $width = 60)
    {
        // Use wordwrap with cut=true to force break long words
        return nl2br(htmlspecialchars(wordwrap($text, $width, "\n", true)));
    }
    ?>
    <!DOCTYPE html>
    <html lang="pl">

    <head>
        <meta charset="UTF-8">
        <title>Proponowane Rozwiązanie: <?php echo htmlspecialchars($solution['title']); ?></title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                padding: 20px;
                background: #fff;
                color: #333;
                font-size: 14px;
            }

            /* CSS Fallbacks */
            * {
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
                border: 1px solid #eee;
                padding: 40px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            }

            /* ... existing styles ... */

            /* Description */
            .description {
                margin-bottom: 30px;
                line-height: 1.6;
                color: #444;
                width: 100%;
            }

            /* ... */


            /* Header */
            .header {
                display: flex;
                justify-content: space-between;
                border-bottom: 2px solid #ff6b35;
                /* Admin accent color */
                padding-bottom: 20px;
                margin-bottom: 30px;
            }

            .company-info h1 {
                margin: 0;
                color: #2c3e50;
                font-size: 22px;
            }

            .company-info p {
                margin: 5px 0 0;
                color: #7f8c8d;
                font-size: 13px;
                line-height: 1.4;
            }

            .doc-info {
                text-align: right;
            }

            .doc-title {
                font-size: 24px;
                font-weight: bold;
                color: #3498db;
                text-transform: uppercase;
                margin: 0;
            }

            .doc-meta {
                color: #95a5a6;
                font-size: 13px;
                margin-top: 5px;
            }

            /* Client Section */
            .client-section {
                margin-bottom: 40px;
                padding-bottom: 20px;
                border-bottom: 1px solid #eee;
            }

            .section-label {
                font-size: 11px;
                text-transform: uppercase;
                color: #999;
                font-weight: 700;
                letter-spacing: 1px;
                margin-bottom: 10px;
            }



            .client-details {
                font-size: 15px;
                color: #2c3e50;
                line-height: 1.5;
            }

            /* Description */
            .description {
                margin-bottom: 30px;
                line-height: 1.6;
                color: #444;
            }

            .description strong {
                display: block;
                margin-bottom: 10px;
                color: #2c3e50;
                font-size: 16px;
            }

            /* Table */
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
            }

            th {
                background: #2c3e50;
                color: white;
                text-align: left;
                padding: 12px;
                font-size: 13px;
                text-transform: uppercase;
            }

            td {
                padding: 12px;
                border-bottom: 1px solid #eee;
            }

            tr:last-child td {
                border-bottom: 2px solid #2c3e50;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            /* Totals */
            .total-section {
                text-align: right;
                margin-top: 20px;
            }

            .total-label {
                font-size: 14px;
                color: #7f8c8d;
            }

            .total-amount {
                font-size: 28px;
                font-weight: bold;
                color: #27ae60;
                margin-top: 5px;
            }

            /* Disclaimer Footer */
            .footer-disclaimer {
                margin-top: 50px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 11px;
                color: #95a5a6;
                text-align: center;
                line-height: 1.5;
            }

            @media print {
                body {
                    padding: 0;
                    background: white;
                }

                .container {
                    border: none;
                    box-shadow: none;
                    padding: 0;
                    max-width: 100%;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.querySelector('.container');
            const opt = {
                margin: 10,
                filename: 'Propozycja_<?php echo $solution['id']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, logging: false, useCORS: true, allowTaint: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Hide action bar before PDF
            const actions = document.querySelector('.actions-bar');
            if (actions) actions.style.display = 'none';

            html2pdf().set(opt).from(element).save().then(() => {
                if (actions) actions.style.display = 'flex';
            }).catch(err => {
                if (actions) actions.style.display = 'flex';
                alert('Błąd przy pobieraniu PDF. Spróbuj: Ctrl+P → Zapisz jako PDF');
                console.error('PDF Error:', err);
            });
        }

        // Alternative: Print to PDF using browser's native print function
        function printToPDF() {
            window.print();
        }
    </script>

    <style>
        .actions-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .action-btn {
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            font-family: inherit;
            font-size: 14px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-print {
            background: #2c3e50;
        }

        .btn-download {
            background: #3498db;
        }

        @media print {

            .no-print,
            .actions-bar {
                display: none !important;
            }
        }
    </style>

    <body>
        <div class="actions-bar no-print">
            <button class="action-btn btn-print" onclick="window.print()">
                <span style="font-size: 1.2em;">🖨️</span> Drukuj
            </button>
            <button class="action-btn btn-download" onclick="downloadPDF()">
                <span style="font-size: 1.2em;">⬇️</span> Pobierz PDF
            </button>
        </div>

        <div class="container" id="pdf-content">
            <div class="header">
                <div class="company-info">
                    <h1><?php echo $companyData['name']; ?></h1>
                    <p>
                        <?php echo $companyData['address']; ?>, <?php echo $companyData['city']; ?><br>
                        Tel: <?php echo $companyData['phone']; ?> | Email: <?php echo $companyData['email']; ?>
                    </p>
                </div>
                <div class="doc-info">
                    <div class="doc-title">Proponowane<br>Rozwiązanie</div>
                    <div class="doc-meta">
                        Dokument nr: #PROP/<?php echo $solution['id']; ?>/<?php echo date('Y'); ?><br>
                        Data: <?php echo date('d.m.Y', strtotime($solution['created_at'])); ?>
                    </div>
                </div>
            </div>

            <!-- Client & Context -->
            <div class="client-section">
                <div class="section-label">Dla Klienta</div>
                <div class="client-details">
                    <strong
                        style="font-size: 1.2em; color: #2c3e50;"><?php echo htmlspecialchars($solution['client_name']); ?></strong>
                    <div style="margin-top: 5px; color: #555;">
                        <?php if ($solution['client_company'])
                            echo htmlspecialchars($solution['client_company']) . "<br>"; ?>
                        <?php if ($solution['client_address'])
                            echo htmlspecialchars($solution['client_address']) . "<br>"; ?>
                        <?php if ($solution['client_phone'])
                            echo "Tel: " . htmlspecialchars($solution['client_phone']); ?>
                        <?php if ($solution['client_email'])
                            echo " &bull; " . htmlspecialchars($solution['client_email']); ?>
                    </div>
                </div>
            </div>

            <div class="description">
                <strong><?php echo htmlspecialchars($solution['title']); ?></strong>
                <?php echo format_pdf_text($solution['description'], 90); ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">Lp.</th>
                        <th style="width: 60%;">Nazwa Pozycji / Usługi</th>
                        <th style="width: 15%;">Typ</th>
                        <th class="text-right" style="width: 20%;">Wartość</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    foreach ($items as $item): ?>
                        <tr>
                            <td style="color: #999;"><?php echo $i++; ?></td>
                            <td>
                                <strong><?php echo format_pdf_text($item['name'], 50); ?></strong>
                            </td>
                            <td style="font-size: 12px; color: #7f8c8d;">
                                <?php
                                $map = ['product' => 'Produkt', 'service' => 'Usługa', 'custom' => 'Inne'];
                                echo $map[$item['type']] ?? '-';
                                ?>
                            </td>
                            <td class="text-right"><?php echo number_format($item['price'], 2); ?> zł</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-section">
                <div class="total-label">Szacunkowy koszt całkowity:</div>
                <div class="total-amount"><?php echo number_format($solution['total_price'], 2); ?> PLN</div>
            </div>


        </div>
    </body>

    </html>
    <?php
    exit; // Koniec dla Oferty
}

// -- LOGIKA DLA FAKTUR (stara) --
$invoiceId = $id;

// Pobierz fakturę
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = :id");
$stmt->execute([':id' => $invoiceId]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die('Faktura nie istnieje');
}

// Pobierz pozycje
$stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id");
$stmt->execute([':id' => $invoiceId]);
$items = $stmt->fetchAll();

// Dane firmy (już zdefiniowane wyżej)
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $invoice['invoice_type'] === 'faktura' ? 'Faktura VAT' : 'Paragon'; ?>
        <?php echo $invoice['invoice_number']; ?>
    </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20mm;
            background: #f5f5f5;
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 3px solid #ff6b35;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .company-details {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-title {
            text-align: right;
            flex: 1;
        }

        .invoice-type {
            font-size: 28px;
            font-weight: bold;
            color: #ff6b35;
            margin-bottom: 10px;
        }

        .invoice-number {
            font-size: 14px;
            color: #666;
        }

        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .party {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .party+.party {
            margin-left: 20px;
        }

        .party-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 14px;
        }

        .party-info {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-details {
            margin-bottom: 30px;
            font-size: 12px;
        }

        .invoice-details table {
            width: 100%;
        }

        .invoice-details td {
            padding: 5px 0;
        }

        .invoice-details td:first-child {
            font-weight: bold;
            width: 150px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background: #2c3e50;
            color: white;
        }

        .items-table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
        }

        .items-table th.right {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .items-table tbody tr:hover {
            background: #f8f9fa;
        }

        .items-table td {
            padding: 10px;
            font-size: 12px;
        }

        .items-table td.right {
            text-align: right;
        }

        .summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .summary-table {
            width: 300px;
        }

        .summary-table tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .summary-table td {
            padding: 10px;
            font-size: 14px;
        }

        .summary-table td:first-child {
            text-align: left;
            font-weight: bold;
        }

        .summary-table td:last-child {
            text-align: right;
        }

        .summary-table .total {
            background: #ff6b35;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 11px;
            color: #666;
        }

        .payment-info {
            margin-bottom: 20px;
        }

        .payment-info strong {
            color: #2c3e50;
        }

        .notes {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .signature {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid #000;
            width: 200px;
            font-size: 11px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
            font-size: 14px;
        }

        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
        }
    </style>
</head>

<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Drukuj / Zapisz PDF
    </button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name"><?php echo $companyData['name']; ?></div>
                <div class="company-details">
                    <?php echo $companyData['address']; ?><br>
                    <?php echo $companyData['city']; ?><br>
                    NIP: <?php echo $companyData['nip']; ?><br>
                    Tel: <?php echo $companyData['phone']; ?><br>
                    Email: <?php echo $companyData['email']; ?>
                </div>
            </div>
            <div class="invoice-title">
                <div class="invoice-type">
                    <?php echo $invoice['invoice_type'] === 'faktura' ? 'FAKTURA VAT' : 'PARAGON'; ?>
                </div>
                <div class="invoice-number">
                    Nr: <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party">
                <div class="party-title">SPRZEDAWCA:</div>
                <div class="party-info">
                    <strong><?php echo $companyData['name']; ?></strong><br>
                    <?php echo $companyData['address']; ?><br>
                    <?php echo $companyData['city']; ?><br>
                    NIP: <?php echo $companyData['nip']; ?>
                </div>
            </div>
            <div class="party">
                <div class="party-title">NABYWCA:</div>
                <div class="party-info">
                    <?php if ($invoice['client_company']): ?>
                        <strong><?php echo htmlspecialchars($invoice['client_company']); ?></strong><br>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($invoice['client_name']); ?><br>
                    <?php if ($invoice['client_address']): ?>
                        <?php echo htmlspecialchars($invoice['client_address']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['client_nip']): ?>
                        NIP: <?php echo htmlspecialchars($invoice['client_nip']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['client_phone']): ?>
                        Tel: <?php echo htmlspecialchars($invoice['client_phone']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['client_email']): ?>
                        Email: <?php echo htmlspecialchars($invoice['client_email']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <table>
                <tr>
                    <td>Data wystawienia:</td>
                    <td><?php echo date('d.m.Y', strtotime($invoice['created_at'])); ?></td>
                </tr>
                <tr>
                    <td>Metoda płatności:</td>
                    <td><?php echo ucfirst($invoice['payment_method']); ?></td>
                </tr>
                <tr>
                    <td>Status płatności:</td>
                    <td><strong><?php echo ucfirst($invoice['payment_status']); ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">Lp.</th>
                    <th>Nazwa towaru/usługi</th>
                    <th style="width: 80px;" class="right">Ilość</th>
                    <th style="width: 100px;" class="right">Cena jedn.</th>
                    <th style="width: 80px;" class="right">VAT</th>
                    <th style="width: 120px;" class="right">Wartość</th>
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
                            <?php if ($item['description']): ?>
                                <br><small style="color: #666;"><?php echo htmlspecialchars($item['description']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="right"><?php echo number_format($item['quantity'], 2); ?></td>
                        <td class="right"><?php echo number_format($item['unit_price'], 2); ?> zł</td>
                        <td class="right"><?php echo number_format($item['tax_rate'], 0); ?>%</td>
                        <td class="right"><strong><?php echo number_format($item['total'], 2); ?> zł</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td>Netto:</td>
                    <td><?php echo number_format($invoice['subtotal'], 2); ?> zł</td>
                </tr>
                <tr>
                    <td>VAT (23%):</td>
                    <td><?php echo number_format($invoice['tax'], 2); ?> zł</td>
                </tr>
                <tr class="total">
                    <td>RAZEM:</td>
                    <td><?php echo number_format($invoice['total'], 2); ?> zł</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        <?php if ($invoice['notes']): ?>
            <div class="notes">
                <strong>Uwagi:</strong><br>
                <?php echo nl2br(htmlspecialchars($invoice['notes'])); ?>
            </div>
        <?php endif; ?>

        <!-- Payment Info -->
        <div class="payment-info">
            <strong>Sposób płatności:</strong> <?php echo ucfirst($invoice['payment_method']); ?><br>
            <strong>Status płatności:</strong> <?php echo ucfirst($invoice['payment_status']); ?>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature">
                Podpis wystawiającego
            </div>
            <div class="signature">
                Podpis odbiorcy
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>*Dokument wygenerowany automatycznie przez system serwisu</p>
            <p>Data wydruku: <?php echo date('d.m.Y H:i:s'); ?></p>
        </div>
    </div>

    <script>
        // Automatyczne drukowanie po załadowaniu (opcjonalne)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>