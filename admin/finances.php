<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? 'dashboard';
$error = '';

// ============================================================================
// TEAM MEMBERS MANAGEMENT
// ============================================================================

if ($action === 'add_member' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO team_members (name, email, phone, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$_POST['name'], $_POST['email'] ?? null, $_POST['phone'] ?? null, $_POST['role'] ?? null]);
        header("Location: ?action=team&success=member_added");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($action === 'delete_member' && isset($_GET['id'])) {
    try {
        $pdo->prepare("DELETE FROM team_members WHERE id = ?")->execute([$_GET['id']]);
        header("Location: ?action=team&success=member_deleted");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ============================================================================
// PRODUCT CONTRIBUTIONS
// ============================================================================

if ($action === 'add_contribution' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO financial_contributions (product_id, team_member_id, amount, description, contributed_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$_POST['product_id'], $_POST['team_member_id'], $_POST['amount'], $_POST['description'] ?? null]);
        header("Location: ?action=products&success=contribution_added");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if ($action === 'delete_contribution' && isset($_GET['id'])) {
    try {
        $pdo->prepare("DELETE FROM financial_contributions WHERE id = ?")->execute([$_GET['id']]);
        header("Location: ?action=products&success=contribution_deleted");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ============================================================================
// PRODUCT SALES & PROFIT DISTRIBUTION
// ============================================================================

if ($action === 'add_sale' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $product_id = $_POST['product_id'];
        $sale_price = floatval($_POST['sale_price']);
        $sale_cost = floatval($_POST['sale_cost']);
        $profit = $sale_price - $sale_cost;

        $contribs = $pdo->prepare("SELECT team_member_id, amount FROM financial_contributions WHERE product_id = ?");
        $contribs->execute([$product_id]);
        $contributions = $contribs->fetchAll();

        $total_contribution = array_sum(array_column($contributions, 'amount'));

        if ($total_contribution <= 0) {
            throw new Exception("Brak wkładów dla tego produktu!");
        }

        $pdo->beginTransaction();

        // Link do faktury jeśli została przesłana
        $invoice_id = $_POST['invoice_id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO product_sales (product_id, sale_price, sale_cost, profit, invoice_id, notes, sold_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$product_id, $sale_price, $sale_cost, $profit, $invoice_id, $_POST['notes'] ?? null]);
        $sale_id = $pdo->lastInsertId();

        foreach ($contributions as $contrib) {
            $percentage = ($contrib['amount'] / $total_contribution) * 100;
            $profit_share = ($percentage / 100) * $profit;

            $stmt = $pdo->prepare("INSERT INTO profit_distributions (sale_id, team_member_id, contribution_percentage, profit_share, distributed_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$sale_id, $contrib['team_member_id'], $percentage, $profit_share]);
        }

        // Jeśli jest faktura, aktualizuj invoice_items aby miał product_id
        if ($invoice_id) {
            $pdo->prepare("UPDATE invoice_items SET product_id = ? WHERE invoice_id = ? AND product_id IS NULL LIMIT 1")->execute([$product_id, $invoice_id]);
        }

        $pdo->commit();
        header("Location: ?action=products&success=sale_added");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

if ($action === 'delete_sale' && isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM profit_distributions WHERE sale_id = ?")->execute([$_GET['id']]);
        $pdo->prepare("DELETE FROM product_sales WHERE id = ?")->execute([$_GET['id']]);
        $pdo->commit();
        header("Location: ?action=products&success=sale_deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// ============================================================================
// SERVICE EXECUTIONS
// ============================================================================

if ($action === 'add_service_exec' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $service_id = $_POST['service_id'];
        $service_price = floatval($_POST['service_price']);
        $team_ids = $_POST['team_members'] ?? [];

        if (count($team_ids) <= 0) {
            throw new Exception("Musisz wybrać co najmniej jednego członka zespołu!");
        }

        $share_per_member = $service_price / count($team_ids);

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO service_executions (service_id, invoice_id, service_price, executed_at, notes) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$service_id, $_POST['invoice_id'] ?? null, $service_price, $_POST['notes'] ?? null]);
        $exec_id = $pdo->lastInsertId();

        foreach ($team_ids as $member_id) {
            $stmt = $pdo->prepare("INSERT INTO service_team (execution_id, team_member_id, payment_share, assigned_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$exec_id, $member_id, $share_per_member]);
        }

        $pdo->commit();
        header("Location: ?action=services&success=service_added");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

if ($action === 'delete_service_exec' && isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM service_team WHERE execution_id = ?")->execute([$_GET['id']]);
        $pdo->prepare("DELETE FROM service_executions WHERE id = ?")->execute([$_GET['id']]);
        $pdo->commit();
        header("Location: ?action=services&success=service_deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// ============================================================================
// GET DATA FOR DISPLAY
// ============================================================================

$team_members = $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY name")->fetchAll();
$products = $pdo->query("SELECT id, name, price, stock, category, image_path FROM products ORDER BY name")->fetchAll();
$services = $pdo->query("SELECT id, name, price FROM services ORDER BY name")->fetchAll();
$invoices = $pdo->query("SELECT id, invoice_number, invoice_type, client_name, total, created_at FROM invoices ORDER BY created_at DESC LIMIT 100")->fetchAll();

$product_contributions = [];
foreach ($products as $prod) {
    $contribs = $pdo->prepare("SELECT tm.id, tm.name, fc.id as contrib_id, fc.amount, fc.contributed_at FROM financial_contributions fc JOIN team_members tm ON fc.team_member_id = tm.id WHERE fc.product_id = ? ORDER BY tm.name");
    $contribs->execute([$prod['id']]);
    $product_contributions[$prod['id']] = $contribs->fetchAll();
}

$sales = $pdo->query("SELECT ps.*, p.name as product_name, p.stock, inv.invoice_number, inv.client_name FROM product_sales ps JOIN products p ON ps.product_id = p.id LEFT JOIN invoices inv ON ps.invoice_id = inv.id ORDER BY ps.sold_at DESC LIMIT 20")->fetchAll();

$service_execs = $pdo->query("SELECT se.*, s.name as service_name FROM service_executions se JOIN services s ON se.service_id = s.id ORDER BY se.executed_at DESC LIMIT 20")->fetchAll();

$distributions = $pdo->query("SELECT pd.*, tm.name as member_name, ps.product_id, p.name as product_name FROM profit_distributions pd JOIN team_members tm ON pd.team_member_id = tm.id JOIN product_sales ps ON pd.sale_id = ps.id JOIN products p ON ps.product_id = p.id ORDER BY pd.distributed_at DESC LIMIT 30")->fetchAll();

$service_teams = $pdo->query("SELECT st.*, tm.name as member_name, se.service_id, s.name as service_name FROM service_team st JOIN team_members tm ON st.team_member_id = tm.id JOIN service_executions se ON st.execution_id = se.id JOIN services s ON se.service_id = s.id ORDER BY st.assigned_at DESC LIMIT 30")->fetchAll();

$team_summary = $pdo->query("
    SELECT 
        tm.id, 
        tm.name,
        (SELECT COALESCE(SUM(pd.profit_share), 0) 
         FROM profit_distributions pd 
         WHERE pd.team_member_id = tm.id) as profit_share,
        (SELECT COALESCE(SUM(st.payment_share), 0) 
         FROM service_team st 
         WHERE st.team_member_id = tm.id) as service_payment
    FROM team_members tm
    WHERE tm.is_active = 1
    ORDER BY ((SELECT COALESCE(SUM(pd.profit_share), 0) FROM profit_distributions pd WHERE pd.team_member_id = tm.id) + 
              (SELECT COALESCE(SUM(st.payment_share), 0) FROM service_team st WHERE st.team_member_id = tm.id)) DESC
")->fetchAll();

// Financial Summary: Investments + Earnings
$financial_summary = $pdo->query("
    SELECT 
        tm.id,
        tm.name,
        (SELECT COALESCE(SUM(fc.amount), 0) 
         FROM financial_contributions fc 
         WHERE fc.team_member_id = tm.id AND fc.is_transferred = 0) as total_invested,
        (SELECT COALESCE(SUM(pd.profit_share), 0) 
         FROM profit_distributions pd 
         WHERE pd.team_member_id = tm.id) as total_earned_products,
        (SELECT COALESCE(SUM(st.payment_share), 0) 
         FROM service_team st 
         WHERE st.team_member_id = tm.id) as total_earned_services,
        ((SELECT COALESCE(SUM(pd.profit_share), 0) FROM profit_distributions pd WHERE pd.team_member_id = tm.id) + 
         (SELECT COALESCE(SUM(st.payment_share), 0) FROM service_team st WHERE st.team_member_id = tm.id)) as total_earned,
        ((SELECT COALESCE(SUM(fc.amount), 0) FROM financial_contributions fc WHERE fc.team_member_id = tm.id AND fc.is_transferred = 0) + 
         (SELECT COALESCE(SUM(pd.profit_share), 0) FROM profit_distributions pd WHERE pd.team_member_id = tm.id) + 
         (SELECT COALESCE(SUM(st.payment_share), 0) FROM service_team st WHERE st.team_member_id = tm.id)) as total_sum
    FROM team_members tm
    WHERE tm.is_active = 1
    ORDER BY tm.name ASC
")->fetchAll();

// Get total counts for stats
$team_count = count($team_members);
$sales_count = count($sales);
$services_count = count($service_execs);
$total_profit = array_sum(array_column($distributions, 'profit_share'));
$total_service_payment = array_sum(array_column($service_teams, 'payment_share'));
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Finansami - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Finanse</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    $success_map = [
                        'member_added' => 'Członek zespołu został dodany!',
                        'member_deleted' => 'Członek zespołu został usunięty!',
                        'contribution_added' => 'Wkład finansowy został dodany!',
                        'contribution_deleted' => 'Wkład finansowy został usunięty!',
                        'sale_added' => 'Sprzedaż została zarejestrowana!',
                        'sale_deleted' => 'Sprzedaż została usunięta!',
                        'service_added' => 'Usługa została zarejestrowana!',
                        'service_deleted' => 'Usługa została usunięta!'
                    ];
                    echo $success_map[$_GET['success']] ?? 'Operacja wykonana!';
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs -->
            <div class="tabs-container">
                <div class="tabs-nav">
                    <a href="?action=dashboard" class="tab-link <?php echo $action === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie"></i> Dashboard
                    </a>
                    <a href="?action=team" class="tab-link <?php echo $action === 'team' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Zespół
                    </a>
                    <a href="?action=products" class="tab-link <?php echo $action === 'products' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> Produkty
                    </a>
                    <a href="?action=services" class="tab-link <?php echo $action === 'services' ? 'active' : ''; ?>">
                        <i class="fas fa-tools"></i> Usługi
                    </a>
                    <a href="?action=reports" class="tab-link <?php echo $action === 'reports' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i> Raporty
                    </a>
                </div>
            </div>

            <!-- Dashboard -->
            <?php if ($action === 'dashboard'): ?>
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <div class="stat-icon messages">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Członków zespołu</h3>
                            <p class="stat-number"><?php echo $team_count; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Sprzedaży zarejestrowanych</h3>
                            <p class="stat-number"><?php echo $sales_count; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon visits">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Usług wykonanych</h3>
                            <p class="stat-number"><?php echo $services_count; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon news">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Łączny zysk z produktów</h3>
                            <p class="stat-number"><?php echo number_format($total_profit, 2); ?> zł</p>
                        </div>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-shopping-cart"></i> Ostatnie sprzedaże</h2>
                        </div>
                        <?php if (empty($sales)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Brak zarejestrowanych sprzedaży</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Produkt</th>
                                            <th>Cena</th>
                                            <th>Koszt</th>
                                            <th>Zysk</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($sales, 0, 5) as $sale): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                                <td><?php echo number_format($sale['sale_price'], 2); ?> zł</td>
                                                <td><?php echo number_format($sale['sale_cost'], 2); ?> zł</td>
                                                <td><span
                                                        style="color: #28a745; font-weight: bold;">+<?php echo number_format($sale['profit'], 2); ?>
                                                        zł</span></td>
                                                <td><?php echo date('d.m.Y', strtotime($sale['sold_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-tools"></i> Ostatnie usługi</h2>
                        </div>
                        <?php if (empty($service_execs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Brak wykonanych usług</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Usługa</th>
                                            <th>Cena</th>
                                            <th>Na osobę</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($service_execs, 0, 5) as $exec):
                                            $team_count_exec = $pdo->prepare("SELECT COUNT(*) as cnt FROM service_team WHERE execution_id = ?");
                                            $team_count_exec->execute([$exec['id']]);
                                            $cnt = $team_count_exec->fetch()['cnt'];
                                            $per_person = $cnt > 0 ? $exec['service_price'] / $cnt : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($exec['service_name']); ?></td>
                                                <td><?php echo number_format($exec['service_price'], 2); ?> zł</td>
                                                <td><?php echo number_format($per_person, 2); ?> zł</td>
                                                <td><?php echo date('d.m.Y', strtotime($exec['executed_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Team Management -->
            <?php elseif ($action === 'team'): ?>
                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user-plus"></i> Dodaj członka zespołu</h2>
                    </div>
                    <form method="POST" action="?action=add_member" class="form-grid">
                        <div class="form-group">
                            <label for="name">Imię i nazwisko *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="phone">Telefon</label>
                            <input type="text" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="role">Stanowisko</label>
                            <input type="text" id="role" name="role">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Dodaj członka
                            </button>
                        </div>
                    </form>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-list"></i> Lista członków zespołu</h2>
                    </div>
                    <?php if (empty($team_members)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>Brak członków zespołu</h3>
                            <p>Dodaj pierwszego członka zespołu</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Imię</th>
                                        <th>Email</th>
                                        <th>Telefon</th>
                                        <th>Stanowisko</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team_members as $member): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($member['phone'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($member['role'] ?? '-'); ?></td>
                                            <td>
                                                <a href="?action=delete_member&id=<?php echo $member['id']; ?>"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć tego członka?')"
                                                    class="btn-icon delete" title="Usuń">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Products & Contributions -->
            <?php elseif ($action === 'products'): ?>
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Produkty w katalogu</h3>
                            <p class="stat-number"><?php echo count($products); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon messages">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Sprzedane produkty</h3>
                            <p class="stat-number"><?php echo count($sales); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon visits">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Całkowity zysk</h3>
                            <p class="stat-number">
                                <?php echo number_format(array_sum(array_column($sales, 'profit')), 2); ?> zł</p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-plus"></i> Dodaj wkład finansowy do produktu</h2>
                    </div>
                    <form method="POST" action="?action=add_contribution" class="form-grid">
                        <div class="form-group">
                            <label for="product_id">Produkt *</label>
                            <select id="product_id" name="product_id" required onchange="updateProductInfo()">
                                <option value="">-- Wybierz produkt --</option>
                                <?php foreach ($products as $prod): ?>
                                    <option value="<?php echo $prod['id']; ?>" data-price="<?php echo $prod['price']; ?>"
                                        data-stock="<?php echo $prod['stock']; ?>"
                                        data-category="<?php echo htmlspecialchars($prod['category'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($prod['name']); ?>
                                        (<?php echo number_format($prod['price'], 2); ?> zł - Stan:
                                        <?php echo $prod['stock']; ?> szt.)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small id="product-info" style="color: #999; margin-top: 5px;"></small>
                        </div>
                        <div class="form-group">
                            <label for="team_member_id">Członek zespołu *</label>
                            <select id="team_member_id" name="team_member_id" required>
                                <option value="">-- Wybierz osobę --</option>
                                <?php foreach ($team_members as $member): ?>
                                    <option value="<?php echo $member['id']; ?>">
                                        <?php echo htmlspecialchars($member['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Kwota wkładu *</label>
                            <input type="number" id="amount" name="amount" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label for="description">Opis (component, części itd.)</label>
                            <input type="text" id="description" name="description"
                                placeholder="np. Komponenty CPU, RAM, SSD">
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Dodaj wkład
                            </button>
                        </div>
                    </form>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-check"></i> Zarejestruj sprzedaż produktu</h2>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Ważne:</strong> Aby zarejestrować sprzedaż produktu, musisz najpierw dodać wkłady finansowe
                        dla tego produktu. Zysk będzie automatycznie podzielony proporcjonalnie do wkładów.
                    </div>
                    <form method="POST" action="?action=add_sale" class="form-grid">
                        <div class="form-group">
                            <label for="sale_product_id">Produkt *</label>
                            <select id="sale_product_id" name="product_id" required onchange="updateSaleProductInfo()">
                                <option value="">-- Wybierz produkt --</option>
                                <?php foreach ($products as $prod):
                                    $contribs_sum = array_sum(array_column($product_contributions[$prod['id']] ?? [], 'amount'));
                                    if ($contribs_sum > 0):
                                        ?>
                                        <option value="<?php echo $prod['id']; ?>"
                                            data-contribs="<?php echo number_format($contribs_sum, 2); ?>">
                                            <?php echo htmlspecialchars($prod['name']); ?> - Wkłady:
                                            <?php echo number_format($contribs_sum, 2); ?> zł
                                        </option>
                                    <?php endif; endforeach; ?>
                            </select>
                            <small id="sale-product-info" style="color: #999; margin-top: 5px;"></small>
                        </div>
                        <div class="form-group">
                            <label for="sale_cost">Koszt (suma wkładów) *</label>
                            <input type="number" id="sale_cost" name="sale_cost" step="0.01" min="0" required
                                placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label for="sale_price">Cena sprzedaży *</label>
                            <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" required
                                placeholder="0.00" onchange="updateProfit()">
                        </div>
                        <div class="form-group">
                            <label for="profit_display">Zysk netto</label>
                            <input type="text" id="profit_display" disabled placeholder="0.00 zł"
                                style="background: #f8f9fa;">
                        </div>
                        <div class="form-group">
                            <label for="sale_invoice_id">Faktura (z listy)</label>
                            <select id="sale_invoice_id" name="invoice_id">
                                <option value="">-- Bez faktury --</option>
                                <?php foreach ($invoices as $inv): ?>
                                    <option value="<?php echo $inv['id']; ?>">
                                        <?php echo htmlspecialchars($inv['invoice_number']); ?> -
                                        <?php echo htmlspecialchars($inv['client_name']); ?>
                                        (<?php echo number_format($inv['total'], 2); ?> zł)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label for="sale_notes">Notatka</label>
                            <textarea id="sale_notes" name="notes" rows="3"
                                placeholder="Uwagi dotyczące sprzedaży (gdzie sprzedano, do kogo, etc.)"></textarea>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Zarejestruj sprzedaż
                            </button>
                        </div>
                    </form>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-box"></i> Produkty i ich wkłady finansowe</h2>
                    </div>
                    <?php if (empty($products)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box"></i>
                            <h3>Brak produktów</h3>
                            <p>Dodaj produkty <a href="products.php" style="color: #ff6b35; font-weight: 600;">w sekcji
                                    Produkty</a></p>
                        </div>
                    <?php else: ?>
                        <div class="products-contributions-grid">
                            <?php foreach ($products as $product):
                                $contribs = $product_contributions[$product['id']] ?? [];
                                $total_contribs = array_sum(array_column($contribs, 'amount'));
                                ?>
                                <div class="product-contribution-card">
                                    <div class="product-header">
                                        <div class="product-meta">
                                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <?php if ($product['category']): ?>
                                                <span
                                                    class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-price">
                                            <span class="price"><?php echo number_format($product['price'], 2); ?> zł</span>
                                            <span class="stock-info">Stan: <?php echo $product['stock']; ?> szt.</span>
                                        </div>
                                    </div>

                                    <?php if (!empty($contribs)): ?>
                                        <div class="contributions-list">
                                            <div class="contributions-header">
                                                <strong>Wkłady (<?php echo count($contribs); ?>)</strong>
                                                <span class="total-contribs"><?php echo number_format($total_contribs, 2); ?> zł</span>
                                            </div>
                                            <?php foreach ($contribs as $contrib):
                                                $percent = ($contrib['amount'] / $total_contribs) * 100;
                                                ?>
                                                <div class="contribution-item">
                                                    <div class="contrib-info">
                                                        <span class="member-name"><?php echo htmlspecialchars($contrib['name']); ?></span>
                                                        <span class="contrib-amount"><?php echo number_format($contrib['amount'], 2); ?> zł
                                                            (<?php echo number_format($percent, 1); ?>%)</span>
                                                    </div>
                                                    <div class="progress-bar"
                                                        style="width: <?php echo $percent; ?>%; background: linear-gradient(90deg, #ff6b35 0%, #f7931e 100%);">
                                                    </div>
                                                    <a href="?action=delete_contribution&id=<?php echo $contrib['contrib_id']; ?>"
                                                        onclick="return confirm('Usuń?')" class="contrib-delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-contributions">
                                            <p>Brak wkładów dla tego produktu</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-history"></i> Historia sprzedaży produktów</h2>
                    </div>
                    <?php if (empty($sales)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Brak historii sprzedaży</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Produkt</th>
                                        <th>Koszt</th>
                                        <th>Cena sprzedaży</th>
                                        <th>Zysk</th>
                                        <th>Faktura</th>
                                        <th>Klient</th>
                                        <th>Data</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sales as $sale): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($sale['product_name']); ?></strong></td>
                                            <td><?php echo number_format($sale['sale_cost'], 2); ?> zł</td>
                                            <td><?php echo number_format($sale['sale_price'], 2); ?> zł</td>
                                            <td style="color: #28a745; font-weight: bold;">
                                                +<?php echo number_format($sale['profit'], 2); ?> zł</td>
                                            <td>
                                                <?php if ($sale['invoice_id']): ?>
                                                    <a href="view_invoice.php?id=<?php echo $sale['invoice_id']; ?>"
                                                        style="color: #ff6b35; text-decoration: none;">
                                                        <i class="fas fa-file-invoice"></i>
                                                        <?php echo htmlspecialchars($sale['invoice_number'] ?? ''); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color: #999;">Brak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($sale['client_name'] ?? '-'); ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($sale['sold_at'])); ?></td>
                                            <td>
                                                <a href="?action=delete_sale&id=<?php echo $sale['id']; ?>"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć tę sprzedaż?')"
                                                    class="btn-icon delete" title="Usuń">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Services & Executions -->
            <?php elseif ($action === 'services'): ?>
                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-plus"></i> Zarejestruj wykonanie usługi</h2>
                    </div>
                    <p style="color: #666; margin-bottom: 15px;">
                        <i class="fas fa-info-circle"></i> Cena usługi (z VAT) będzie podzielona równo między wybranych
                        członków zespołu. Faktura jest opcjonalna.
                    </p>
                    <form method="POST" action="?action=add_service_exec" class="form-grid">
                        <div class="form-group">
                            <label for="service_id">Usługa *</label>
                            <select id="service_id" name="service_id" required onchange="updateServicePrice()">
                                <option value="">-- Wybierz usługę --</option>
                                <?php foreach ($services as $serv): ?>
                                    <option value="<?php echo $serv['id']; ?>" data-price="<?php echo $serv['price']; ?>">
                                        <?php echo htmlspecialchars($serv['name']); ?>
                                        (<?php echo number_format($serv['price'], 2); ?> zł)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="service_price">Cena usługi (z VAT) *</label>
                            <input type="number" id="service_price" name="service_price" step="0.01" min="0" required
                                placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label for="service_invoice_id">Faktura (opcjonalnie)</label>
                            <select id="service_invoice_id" name="invoice_id">
                                <option value="">-- Bez faktury --</option>
                                <?php if (!empty($invoices)):
                                    foreach ($invoices as $inv): ?>
                                        <option value="<?php echo $inv['id']; ?>">
                                            <?php echo htmlspecialchars($inv['invoice_number']); ?> -
                                            <?php echo htmlspecialchars($inv['client_name']); ?>
                                            (<?php echo number_format($inv['total'], 2); ?> zł)</option>
                                    <?php endforeach; else: ?>
                                    <option disabled>Brak dostępnych faktur</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label>Członkowie zespołu biorący udział *</label>
                            <div class="checkbox-grid">
                                <?php foreach ($team_members as $member): ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="member_<?php echo $member['id']; ?>" name="team_members[]"
                                            value="<?php echo $member['id']; ?>" onchange="updateServiceShare()">
                                        <label
                                            for="member_<?php echo $member['id']; ?>"><?php echo htmlspecialchars($member['name']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="grid-column: 1 / -1;" id="service-share-display" style="display: none;"></div>
                        <div style="grid-column: 1 / -1;">
                            <label for="service_notes">Notatka</label>
                            <textarea id="service_notes" name="notes" rows="3"
                                placeholder="Uwagi dotyczące usługi"></textarea>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Zarejestruj usługę
                            </button>
                        </div>
                    </form>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-history"></i> Historia usług</h2>
                    </div>
                    <?php if (empty($service_execs)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Brak zarejestrowanych usług</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Usługa</th>
                                        <th>Cena</th>
                                        <th>Zespół</th>
                                        <th>Na osobę</th>
                                        <th>Data</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($service_execs as $exec):
                                        $team_q = $pdo->prepare("SELECT tm.name FROM service_team st JOIN team_members tm ON st.team_member_id = tm.id WHERE st.execution_id = ?");
                                        $team_q->execute([$exec['id']]);
                                        $team_names = array_column($team_q->fetchAll(), 'name');
                                        $team_count_service = count($team_names);
                                        $per_person = $team_count_service > 0 ? $exec['service_price'] / $team_count_service : 0;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($exec['service_name']); ?></strong></td>
                                            <td><?php echo number_format($exec['service_price'], 2); ?> zł</td>
                                            <td><?php echo htmlspecialchars(implode(', ', $team_names)); ?></td>
                                            <td><?php echo number_format($per_person, 2); ?> zł</td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($exec['executed_at'])); ?></td>
                                            <td>
                                                <a href="?action=delete_service_exec&id=<?php echo $exec['id']; ?>"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć tę usługę?')"
                                                    class="btn-icon delete" title="Usuń">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Reports -->
            <?php elseif ($action === 'reports'): ?>
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <div class="stat-icon news">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Łączny zysk z produktów</h3>
                            <p class="stat-number"><?php echo number_format($total_profit, 2); ?> zł</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Łączna wartość usług</h3>
                            <p class="stat-number"><?php echo number_format($total_service_payment, 2); ?> zł</p>
                        </div>
                    </div>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-balance-scale"></i> Podsumowanie finansowe (Inwestycje + Zarobki)</h2>
                    </div>
                    <?php if (empty($financial_summary)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Brak danych</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nazwa</th>
                                        <th>Inwestycje</th>
                                        <th>Ze sprzedaży</th>
                                        <th>Z usług</th>
                                        <th>Razem zarobki</th>
                                        <th>Całkowita suma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_investments = 0;
                                    $total_earned = 0;
                                    $grand_total_sum = 0;
                                    foreach ($financial_summary as $member):
                                        $total_investments += $member['total_invested'];
                                        $total_earned += $member['total_earned'];
                                        $grand_total_sum += $member['total_sum'];
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                                            <td style="color: #ff6b35; font-weight: bold;">
                                                -<?php echo number_format($member['total_invested'], 2); ?> zł</td>
                                            <td style="color: #28a745;">
                                                <?php echo number_format($member['total_earned_products'], 2); ?> zł</td>
                                            <td style="color: #667eea;">
                                                <?php echo number_format($member['total_earned_services'], 2); ?> zł</td>
                                            <td style="font-weight: bold; color: #2196F3;">
                                                +<?php echo number_format($member['total_earned'], 2); ?> zł</td>
                                            <td style="font-weight: bold; font-size: 1.1em; color: #28a745;">
                                                <?php echo number_format($member['total_sum'], 2); ?> zł</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="background: #f8f9fa; font-weight: 600; font-size: 1.1em;">
                                        <td>RAZEM:</td>
                                        <td style="color: #ff6b35;">-<?php echo number_format($total_investments, 2); ?> zł</td>
                                        <td style="color: #28a745;"><?php echo number_format($total_profit, 2); ?> zł</td>
                                        <td style="color: #667eea;"><?php echo number_format($total_service_payment, 2); ?> zł
                                        </td>
                                        <td style="color: #2196F3;">+<?php echo number_format($total_earned, 2); ?> zł</td>
                                        <td><?php echo number_format($grand_total_sum, 2); ?> zł</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="content-section">
                    <div class="section-header">
                        <h2><i class="fas fa-users"></i> Przychody członków zespołu</h2>
                    </div>
                    <?php if (empty($team_summary)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Brak danych</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nazwa</th>
                                        <th>Ze sprzedaży</th>
                                        <th>Z usług</th>
                                        <th>Razem do wypłaty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $grand_total = 0;
                                    foreach ($team_summary as $member):
                                        $total_member = $member['profit_share'] + $member['service_payment'];
                                        $grand_total += $total_member;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                                            <td style="color: #28a745;"><?php echo number_format($member['profit_share'], 2); ?> zł
                                            </td>
                                            <td style="color: #667eea;"><?php echo number_format($member['service_payment'], 2); ?>
                                                zł</td>
                                            <td style="font-weight: bold; font-size: 1.1em;">
                                                <?php echo number_format($total_member, 2); ?> zł</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr style="background: #f8f9fa; font-weight: 600; font-size: 1.1em;">
                                        <td>RAZEM:</td>
                                        <td style="color: #28a745;"><?php echo number_format($total_profit, 2); ?> zł</td>
                                        <td style="color: #667eea;"><?php echo number_format($total_service_payment, 2); ?> zł
                                        </td>
                                        <td><?php echo number_format($grand_total, 2); ?> zł</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="content-grid">
                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-chart-pie"></i> Podziały zysku ze sprzedaży</h2>
                        </div>
                        <?php if (empty($distributions)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Brak podziałów</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Produkt</th>
                                            <th>Osoba</th>
                                            <th>% wkładu</th>
                                            <th>Zysk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($distributions as $dist): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($dist['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($dist['member_name']); ?></td>
                                                <td><?php echo number_format($dist['contribution_percentage'], 2); ?>%</td>
                                                <td style="color: #28a745; font-weight: bold;">
                                                    +<?php echo number_format($dist['profit_share'], 2); ?> zł</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-tasks"></i> Podziały wynagrodzeń z usług</h2>
                        </div>
                        <?php if (empty($service_teams)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Brak podziałów</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Usługa</th>
                                            <th>Osoba</th>
                                            <th>Wynagrodzenie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($service_teams as $team_assign): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($team_assign['service_name']); ?></td>
                                                <td><?php echo htmlspecialchars($team_assign['member_name']); ?></td>
                                                <td style="color: #667eea; font-weight: bold;">
                                                    +<?php echo number_format($team_assign['payment_share'], 2); ?> zł</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>

<style>
    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .tabs-container {
        margin-bottom: 30px;
    }

    .tabs-nav {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .tab-link {
        padding: 10px 20px;
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        color: #666;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .tab-link:hover {
        border-color: #ff6b35;
        color: #ff6b35;
    }

    .tab-link.active {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        border-color: #ff6b35;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 12px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #ff6b35;
    }

    .checkbox-item label {
        margin: 0;
        cursor: pointer;
        color: #333;
        font-weight: 400;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .btn-icon {
        padding: 8px;
        background: #f8f9fa;
        color: #666;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .btn-icon:hover {
        color: #ff6b35;
        border-color: #ff6b35;
        background: #fff5f0;
    }

    .btn-icon.delete:hover {
        color: #dc3545;
        border-color: #dc3545;
        background: #fff5f5;
    }

    .content-subsection {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .content-subsection h3 {
        margin-bottom: 15px;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .empty-state h3 {
        margin-top: 15px;
        color: #666;
    }

    .data-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .data-table tbody tr:hover {
        background: #f8f9fa;
    }

    .products-contributions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .product-contribution-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .product-contribution-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: #ff6b35;
    }

    .product-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .product-meta h3 {
        margin: 0 0 8px 0;
        color: #2c3e50;
        font-size: 1.1rem;
        word-break: break-word;
    }

    .product-category {
        display: inline-block;
        background: #f0f0f0;
        color: #666;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
        margin-bottom: 10px;
    }

    .product-price {
        margin-top: 10px;
    }

    .price {
        display: block;
        font-size: 1.3rem;
        font-weight: bold;
        color: #ff6b35;
        margin-bottom: 5px;
    }

    .stock-info {
        display: block;
        font-size: 0.9rem;
        color: #999;
    }

    .contributions-list {
        margin-top: 15px;
    }

    .contributions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f0f0f0;
    }

    .total-contribs {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .contribution-item {
        margin-bottom: 10px;
        padding: 8px 0;
        position: relative;
    }

    .contrib-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }

    .member-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .contrib-amount {
        font-size: 0.9rem;
        color: #666;
    }

    .progress-bar {
        height: 6px;
        border-radius: 3px;
        margin-bottom: 4px;
        opacity: 0.7;
    }

    .contrib-delete {
        position: absolute;
        right: 0;
        top: 8px;
        opacity: 0;
        transition: opacity 0.3s;
        color: #dc3545;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .contribution-item:hover .contrib-delete {
        opacity: 1;
    }

    .no-contributions {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        text-align: center;
        color: #999;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .content-grid {
            grid-template-columns: 1fr;
        }

        .products-contributions-grid {
            grid-template-columns: 1fr;
        }

        .tabs-nav {
            overflow-x: auto;
        }
    }
</style>

</body>

</html>

<script>
    // Update product info when selected
    function updateProductInfo() {
        const select = document.getElementById('product_id');
        const option = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('product-info');

        if (!option.value) {
            infoDiv.innerHTML = '';
            return;
        }

        const category = option.getAttribute('data-category');
        const stock = option.getAttribute('data-stock');
        infoDiv.innerHTML = `<i class="fas fa-info-circle"></i> Kategoria: ${category || 'Brak'} | Stan magazynu: ${stock} szt.`;
    }

    // Update sale product info and auto-fill cost
    function updateSaleProductInfo() {
        const select = document.getElementById('sale_product_id');
        const option = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('sale-product-info');
        const costInput = document.getElementById('sale_cost');

        if (!option.value) {
            infoDiv.innerHTML = '';
            costInput.value = '';
            return;
        }

        const contribs = option.getAttribute('data-contribs');
        infoDiv.innerHTML = `<i class="fas fa-info-circle"></i> Łączne wkłady: ${contribs} zł`;
        costInput.value = contribs;
        updateProfit();
    }

    // Calculate profit in real-time
    function updateProfit() {
        const costInput = document.getElementById('sale_cost');
        const priceInput = document.getElementById('sale_price');
        const profitDisplay = document.getElementById('profit_display');

        const cost = parseFloat(costInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const profit = price - cost;

        profitDisplay.value = profit.toFixed(2) + ' zł';
    }

    // Update service share when checkboxes change
    function updateServiceShare() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="team_members[]"]');
        const priceInput = document.getElementById('service_price');
        const display = document.getElementById('service-share-display');

        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        const price = parseFloat(priceInput.value) || 0;

        if (selected.length === 0 || price === 0) {
            display.style.display = 'none';
            return;
        }

        const perPerson = price / selected.length;

        let html = '<div style="background: #e8f4f8; border-left: 4px solid #667eea; padding: 12px; border-radius: 5px;"><strong><i class="fas fa-users"></i> Podział wynagrodzeń:</strong><br><br>';
        selected.forEach(checkbox => {
            const label = checkbox.nextElementSibling.textContent;
            html += `<div style="margin: 6px 0;"><i class="fas fa-arrow-right"></i> <strong>${label}:</strong> <span style="color: #667eea; font-weight: bold;">${perPerson.toFixed(2)} zł</span></div>`;
        });
        html += '</div>';

        display.innerHTML = html;
        display.style.display = 'block';
    }

    // Set initial service price from selected service
    document.addEventListener('DOMContentLoaded', function () {
        const serviceSelect = document.querySelector('select[name="service_id"]');
        if (serviceSelect) {
            serviceSelect.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                const price = option.getAttribute('data-price');
                if (price) {
                    document.getElementById('service_price').value = price;
                    updateServiceShare();
                }
            });
        }
    });
</script>

</body>

</html>