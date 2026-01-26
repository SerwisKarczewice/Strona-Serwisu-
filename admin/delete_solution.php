<?php
// ============== DELETE_SOLUTION.PHP ==============
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // We need the client_id to redirect back to the client profile
    $stmt = $pdo->prepare("SELECT client_id FROM client_solutions WHERE id = ?");
    $stmt->execute([$id]);
    $client_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM client_solutions WHERE id = ?");
    $stmt->execute([$id]);

    if ($client_id) {
        header("Location: client_view.php?id=$client_id&success=solution_deleted");
    } else {
        header('Location: clients.php');
    }
} else {
    header('Location: clients.php');
}
exit;
?>