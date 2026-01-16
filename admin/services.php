<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT * FROM services";
if ($filter === 'single') {
    $sql .= " WHERE category = 'single'";
} elseif ($filter === 'package') {
    $sql .= " WHERE category = 'package'";
}
$sql .= " ORDER BY category, display_order ASC";

$stmt = $pdo->query($sql);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Ofertą - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Zarządzanie Ofertą</h1>
                <a href="add_service.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Dodaj Usługę
                </a>
            </header>

            <div class="content-section full-width">
                <div class="filters">
                    <a href="services.php?filter=all"
                        class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        Wszystkie
                    </a>
                    <a href="services.php?filter=single"
                        class="filter-btn <?php echo $filter === 'single' ? 'active' : ''; ?>">
                        Usługi Pojedyncze
                    </a>
                    <a href="services.php?filter=package"
                        class="filter-btn <?php echo $filter === 'package' ? 'active' : ''; ?>">
                        Pakiety
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nazwa</th>
                                <th>Typ</th>
                                <th>Cena</th>
                                <th>Cena po rabacie</th>
                                <th>Status</th>
                                <th>Kolejność</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo $service['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                        <?php if ($service['description']): ?>
                                            <br><small
                                                style="color: #666;"><?php echo htmlspecialchars(substr($service['description'], 0, 60)) . '...'; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="type-badge <?php echo $service['category']; ?>">
                                            <?php echo $service['category'] === 'single' ? 'Pojedyncza' : 'Pakiet'; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo number_format($service['price'], 2); ?> zł</strong></td>
                                    <td>
                                        <?php if ($service['discount_price']): ?>
                                            <span style="color: #28a745; font-weight: 600;">
                                                <?php echo number_format($service['discount_price'], 2); ?> zł
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span
                                            class="status-badge <?php echo $service['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $service['is_active'] ? 'Aktywna' : 'Nieaktywna'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $service['display_order']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_service.php?id=<?php echo $service['id']; ?>" class="btn-icon"
                                                title="Edytuj">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="toggle_service.php?id=<?php echo $service['id']; ?>" class="btn-icon"
                                                title="<?php echo $service['is_active'] ? 'Dezaktywuj' : 'Aktywuj'; ?>">
                                                <i
                                                    class="fas fa-<?php echo $service['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </a>
                                            <a href="delete_service.php?id=<?php echo $service['id']; ?>"
                                                class="btn-icon delete" title="Usuń"
                                                onclick="return confirm('Czy na pewno chcesz usunąć tę usługę?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($services)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tools"></i>
                        <h3>Brak usług</h3>
                        <p>Dodaj pierwszą usługę do swojej oferty</p>
                        <a href="add_service.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Dodaj Usługę
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>

<style>
    .filters {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        flex-wrap: wrap;
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

    .type-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .type-badge.single {
        background: #e7f3ff;
        color: #004085;
    }

    .type-badge.package {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }
</style>