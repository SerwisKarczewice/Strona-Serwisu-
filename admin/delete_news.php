<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
}

header('Location: news.php');
exit;
?>