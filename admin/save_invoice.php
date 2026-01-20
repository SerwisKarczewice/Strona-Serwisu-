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
        
        // Automatycznie dodaj do finansów jeśli to usługa
        if (isset($item['serviceId']) && $item['serviceId']) {
            // Pobierz wszystkich członków zespołu
            $teamStmt = $pdo->prepare("SELECT id FROM team_members WHERE is_active = 1");
            $teamStmt->execute();
            $team_members = $teamStmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($team_members)) {
                // Utwórz wpis service_execution
                $serviceExecStmt = $pdo->prepare("
                    INSERT INTO service_executions (service_id, invoice_id, service_price, executed_at, notes) 
                    VALUES (?, ?, ?, NOW(), ?)
                ");
                $serviceExecStmt->execute([
                    $item['serviceId'],
                    $invoiceId,
                    $item['total'], // cena z VAT
                    "Automatycznie dodane z faktury/paragonu"
                ]);
                $execution_id = $pdo->lastInsertId();
                
                // Podziel cenę równo między zespół
                $share_per_member = $item['total'] / count($team_members);
                
                $teamInsertStmt = $pdo->prepare("
                    INSERT INTO service_team (execution_id, team_member_id, payment_share, assigned_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                
                foreach ($team_members as $member_id) {
                    $teamInsertStmt->execute([$execution_id, $member_id, $share_per_member]);
                }
            }
        }
        
        // Automatycznie dodaj do finansów jeśli to produkt z wkładami
        if (isset($item['productId']) && $item['productId']) {
            // Pobierz wkłady dla tego produktu
            $contribStmt = $pdo->prepare("
                SELECT team_member_id, amount FROM financial_contributions 
                WHERE product_id = ?
            ");
            $contribStmt->execute([$item['productId']]);
            $contributions = $contribStmt->fetchAll();
            
            if (!empty($contributions)) {
                $total_contribution = array_sum(array_column($contributions, 'amount'));
                
                // Cena sprzedaży = ilość * cena jednostkowa brutto
                $sale_price = $item['quantity'] * $item['unitPrice'];
                // Koszt = suma wkładów
                $sale_cost = $total_contribution;
                $profit = $sale_price - $sale_cost;
                
                // Utwórz wpis product_sale
                $productSaleStmt = $pdo->prepare("
                    INSERT INTO product_sales (product_id, sale_price, sale_cost, profit, invoice_id, notes, sold_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $productSaleStmt->execute([
                    $item['productId'],
                    $sale_price,
                    $sale_cost,
                    $profit,
                    $invoiceId,
                    "Automatycznie dodane z faktury/paragonu"
                ]);
                $sale_id = $pdo->lastInsertId();
                
                // Podziel zysk proporcjonalnie do wkładów
                $profitDistStmt = $pdo->prepare("
                    INSERT INTO profit_distributions (sale_id, team_member_id, contribution_percentage, profit_share, distributed_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                foreach ($contributions as $contrib) {
                    $percentage = ($contrib['amount'] / $total_contribution) * 100;
                    $profit_share = ($percentage / 100) * $profit;
                    
                    $profitDistStmt->execute([
                        $sale_id,
                        $contrib['team_member_id'],
                        $percentage,
                        $profit_share
                    ]);
                }
            }
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