<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Brak autoryzacji']);
    exit;
}

// Pobierz dane JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowe dane']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // 1. Sprawdź/dodaj klienta
    $clientId = null;
    $clientData = $data['client'];
    
    if (!empty($clientData['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $clientData['email']]);
        $existingClient = $stmt->fetch();
        
        if ($existingClient) {
            $clientId = $existingClient['id'];
            
            // Zaktualizuj dane klienta
            $stmt = $pdo->prepare("
                UPDATE clients 
                SET name = :name, phone = :phone, address = :address, 
                    nip = :nip, company_name = :company 
                WHERE id = :id
            ");
            $stmt->execute([
                ':name' => $clientData['name'],
                ':phone' => $clientData['phone'],
                ':address' => $clientData['address'],
                ':nip' => $clientData['nip'],
                ':company' => $clientData['company'],
                ':id' => $clientId
            ]);
        }
    }
    
    // Jeśli klient nie istnieje, dodaj nowego
    if (!$clientId && !empty($clientData['name'])) {
        $stmt = $pdo->prepare("
            INSERT INTO clients (name, email, phone, address, nip, company_name, created_at) 
            VALUES (:name, :email, :phone, :address, :nip, :company, NOW())
        ");
        $stmt->execute([
            ':name' => $clientData['name'],
            ':email' => $clientData['email'],
            ':phone' => $clientData['phone'],
            ':address' => $clientData['address'],
            ':nip' => $clientData['nip'],
            ':company' => $clientData['company']
        ]);
        $clientId = $pdo->lastInsertId();
    }
    
    // 2. Generuj numer faktury/paragonu
    $invoiceType = $data['invoiceType'];
    $year = date('Y');
    $month = date('m');
    
    if ($invoiceType === 'faktura') {
        $prefix = "FV/{$year}/{$month}/";
    } else {
        $prefix = "PAR/{$year}/{$month}/";
    }
    
    // Znajdź ostatni numer
    $stmt = $pdo->prepare("
        SELECT invoice_number 
        FROM invoices 
        WHERE invoice_number LIKE :prefix 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([':prefix' => $prefix . '%']);
    $lastInvoice = $stmt->fetch();
    
    $nextNumber = 1;
    if ($lastInvoice) {
        $parts = explode('/', $lastInvoice['invoice_number']);
        $nextNumber = intval(end($parts)) + 1;
    }
    
    $invoiceNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    // 3. Oblicz sumy
    $items = $data['items'];
    $subtotal = 0;
    
    foreach ($items as $item) {
        $subtotal += $item['total'];
    }
    
    $taxRate = 0.23;
    $netto = $subtotal / (1 + $taxRate);
    $tax = $subtotal - $netto;
    
    // 4. Dodaj fakturę
    $stmt = $pdo->prepare("
        INSERT INTO invoices (
            invoice_number, invoice_type, client_id, 
            client_name, client_email, client_phone, client_address, 
            client_nip, client_company,
            subtotal, tax, total,
            payment_method, payment_status,
            notes, created_at, created_by
        ) VALUES (
            :invoice_number, :invoice_type, :client_id,
            :client_name, :client_email, :client_phone, :client_address,
            :client_nip, :client_company,
            :subtotal, :tax, :total,
            :payment_method, 'opłacona',
            :notes, NOW(), :created_by
        )
    ");
    
    $stmt->execute([
        ':invoice_number' => $invoiceNumber,
        ':invoice_type' => $invoiceType,
        ':client_id' => $clientId,
        ':client_name' => $clientData['name'],
        ':client_email' => $clientData['email'],
        ':client_phone' => $clientData['phone'],
        ':client_address' => $clientData['address'],
        ':client_nip' => $clientData['nip'],
        ':client_company' => $clientData['company'],
        ':subtotal' => $netto,
        ':tax' => $tax,
        ':total' => $subtotal,
        ':payment_method' => $data['paymentMethod'],
        ':notes' => $data['notes'],
        ':created_by' => $_SESSION['admin_id']
    ]);
    
    $invoiceId = $pdo->lastInsertId();
    
    // 5. Dodaj pozycje faktury
    $stmt = $pdo->prepare("
        INSERT INTO invoice_items (
            invoice_id, item_type, name, quantity, unit_price, tax_rate, total,
            service_id, product_id
        ) VALUES (
            :invoice_id, :item_type, :name, :quantity, :unit_price, :tax_rate, :total,
            :service_id, :product_id
        )
    ");
    
    foreach ($items as $item) {
        $stmt->execute([
            ':invoice_id' => $invoiceId,
            ':item_type' => $item['type'],
            ':name' => $item['name'],
            ':quantity' => $item['quantity'],
            ':unit_price' => $item['unitPrice'],
            ':tax_rate' => 23.00,
            ':total' => $item['total'],
            ':service_id' => $item['serviceId'] ?? null,
            ':product_id' => $item['productId'] ?? null
        ]);
        
        // Jeśli to produkt, zaktualizuj stan magazynowy
        if (isset($item['productId'])) {
            $updateStmt = $pdo->prepare("
                UPDATE products 
                SET stock = stock - :quantity 
                WHERE id = :id AND stock >= :quantity
            ");
            $updateStmt->execute([
                ':quantity' => $item['quantity'],
                ':id' => $item['productId']
            ]);
        }
    }
    
    $pdo->commit();
    
    // 6. Zwróć sukces
    echo json_encode([
        'success' => true,
        'message' => 'Dokument został zapisany',
        'invoiceId' => $invoiceId,
        'invoiceNumber' => $invoiceNumber,
        'pdfUrl' => 'generate_pdf.php?id=' . $invoiceId
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Błąd: ' . $e->getMessage()
    ]);
}