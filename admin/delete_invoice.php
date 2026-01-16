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

try {
    $pdo->beginTransaction();
    
    // Pobierz pozycje faktury aby przywrócić stany magazynowe
    $stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = :id AND product_id IS NOT NULL");
    $stmt->execute([':id' => $invoiceId]);
    $items = $stmt->fetchAll();
    
    // Przywróć stany magazynowe
    foreach ($items as $item) {
        if ($item['product_id']) {
            $updateStmt = $pdo->prepare("UPDATE products SET stock = stock + :quantity WHERE id = :id");
            $updateStmt->execute([
                ':quantity' => $item['quantity'],
                ':id' => $item['product_id']
            ]);
        }
    }
    
    // Usuń pozycje faktury
    $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = :id");
    $stmt->execute([':id' => $invoiceId]);
    
    // Usuń fakturę
    $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = :id");
    $stmt->execute([':id' => $invoiceId]);
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
}

header('Location: invoices.php');
exit;