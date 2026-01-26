<?php
// ============== DELETE_CLIENT.PHP ==============
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Deletion will cascade to:
    // - client_solutions (ON DELETE CASCADE)
    // - client_meetings (ON DELETE CASCADE)
    // And will set NULL in:
    // - contact_messages (ON DELETE SET NULL)

    $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: clients.php?success=deleted');
exit;
?>