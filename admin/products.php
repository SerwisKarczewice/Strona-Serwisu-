<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Pobierz produkty widoczne i niewidoczne osobno
$stmt_visible = $pdo->query("SELECT * FROM products WHERE is_visible = 1 ORDER BY created_at DESC");
$visible_products = $stmt_visible->fetchAll();

// Pobierz tylko podzespoły komputerowe z magazynu (bez laptopów, komputerów i monitorów)
$component_categories = ['gpu', 'cpu', 'ram', 'storage', 'motherboard', 'psu', 'cooling', 'case', 'other'];
$placeholders = str_repeat('?,', count($component_categories) - 1) . '?';
$stmt_warehouse = $pdo->prepare("SELECT * FROM products WHERE is_visible = 0 AND category IN ($placeholders) ORDER BY category, name");
$stmt_warehouse->execute($component_categories);
$warehouse_items = $stmt_warehouse->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Produktami - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="content-header">
            <h1>Produkty i Magazyn</h1>
            <a href="add_product.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Dodaj Produkt
            </a>
        </header>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php
                echo $_GET['success'] == 'added' ? 'Produkt został pomyślnie dodany!' : 'Produkt został zaktualizowany!';
                ?>
            </div>
        <?php endif; ?>

        <div class="content-section full-width">
            <!-- Zakładki -->
            <div class="tabs-container">
                <button class="tab-btn active" onclick="switchTab('products')">
                    <i class="fas fa-store"></i> Produkty (<?php echo count($visible_products); ?>)
                </button>
                <button class="tab-btn" onclick="switchTab('warehouse')">
                    <i class="fas fa-warehouse"></i> Magazyn (<?php echo count($warehouse_items); ?>)
                </button>
            </div>

            <!-- Sekcja Produktów -->
            <div id="products-tab" class="tab-content active">
                <div class="tab-header">
                    <h2><i class="fas fa-store"></i> Produkty widoczne na stronie</h2>
                    <p>Te produkty są widoczne dla klientów na stronie internetowej</p>
                </div>

                <?php if (!empty($visible_products)): ?>
                    <div class="products-admin-grid">
                        <?php foreach ($visible_products as $product): ?>
                            <div class="product-admin-card">
                                <div class="product-admin-image">
                                    <?php if ($product['image_path'] && file_exists('../' . $product['image_path'])): ?>
                                        <img src="../<?php echo htmlspecialchars($product['image_path']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-microchip"></i>
                                        <small style="color: rgba(255,255,255,0.7); margin-top: 10px;">Brak zdjęcia</small>
                                    <?php endif; ?>
                                </div>

                                <?php if ($product['featured']): ?>
                                    <div class="product-badge">Bestseller</div>
                                <?php endif; ?>

                                <?php if ($product['stock'] <= 0): ?>
                                    <div class="product-badge out">Brak</div>
                                <?php elseif ($product['stock'] <= 3): ?>
                                    <div class="product-badge low">Ostatnie</div>
                                <?php endif; ?>

                                <div class="product-admin-info">
                                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <?php if ($product['description']): ?>
                                        <p><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                    <?php endif; ?>

                                    <div class="meta-info">
                                        <?php if ($product['category']): ?>
                                            <span
                                                class="category-badge"><?php echo htmlspecialchars($product['category']); ?></span>
                                        <?php endif; ?>
                                        <span class="price-badge"><?php echo number_format($product['price'], 2); ?> zł</span>
                                        <span
                                            class="stock-badge <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                            <?php echo $product['stock']; ?> szt.
                                        </span>
                                    </div>

                                    <?php if ($product['olx_link']): ?>
                                        <a href="<?php echo htmlspecialchars($product['olx_link']); ?>" target="_blank"
                                            class="olx-link">
                                            <i class="fas fa-shopping-bag"></i> Zobacz na OLX
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="product-admin-actions">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="Edytuj">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="toggle_featured.php?id=<?php echo $product['id']; ?>" class="btn-icon"
                                        title="Wyróżnij/Usuń wyróżnienie">
                                        <i class="fas fa-star"></i>
                                    </a>
                                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn-icon delete"
                                        title="Usuń" onclick="return confirm('Czy na pewno chcesz usunąć ten produkt?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-store-slash"></i>
                        <h3>Brak widocznych produktów</h3>
                        <p>Dodaj produkty lub zmień ustawienia widoczności w edycji produktu</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sekcja Magazynu -->
            <div id="warehouse-tab" class="tab-content">
                <div class="tab-header">
                    <h2><i class="fas fa-warehouse"></i> Podzespoły w magazynie</h2>
                    <p>Tylko podzespoły komputerowe - niewidoczne na stronie internetowej</p>
                </div>

                <!-- Filtry kategorii -->
                <div class="warehouse-filters">
                    <button class="filter-btn active" onclick="filterWarehouse('all')">
                        <i class="fas fa-th"></i> Wszystkie (<?php echo count($warehouse_items); ?>)
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('gpu')">
                        <i class="fas fa-microchip"></i> GPU
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('cpu')">
                        <i class="fas fa-microchip"></i> CPU
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('ram')">
                        <i class="fas fa-memory"></i> RAM
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('storage')">
                        <i class="fas fa-hdd"></i> Dyski
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('motherboard')">
                        <i class="fas fa-server"></i> Płyty
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('psu')">
                        <i class="fas fa-plug"></i> Zasilacze
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('cooling')">
                        <i class="fas fa-fan"></i> Chłodzenie
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('case')">
                        <i class="fas fa-box"></i> Obudowy
                    </button>
                    <button class="filter-btn" onclick="filterWarehouse('other')">
                        <i class="fas fa-ellipsis-h"></i> Inne
                    </button>
                </div>

                <?php if (!empty($warehouse_items)): ?>
                    <div class="warehouse-table-container">
                        <table class="warehouse-table">
                            <thead>
                                <tr>
                                    <th>Kategoria</th>
                                    <th>Nazwa</th>
                                    <th>Cena</th>
                                    <th>Stan</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $category_names = [
                                    'gpu' => 'Karty Graficzne',
                                    'cpu' => 'Procesory',
                                    'ram' => 'Pamięci RAM',
                                    'storage' => 'Dyski',
                                    'motherboard' => 'Płyty Główne',
                                    'psu' => 'Zasilacze',
                                    'cooling' => 'Chłodzenie',
                                    'case' => 'Obudowy',
                                    'other' => 'Inne'
                                ];

                                foreach ($warehouse_items as $product):
                                    $cat_display = $category_names[$product['category']] ?? $product['category'];
                                    ?>
                                    <tr data-category="<?php echo htmlspecialchars($product['category']); ?>">
                                        <td>
                                            <span class="category-tag"><?php echo htmlspecialchars($cat_display); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <?php if ($product['description']): ?>
                                                <br><small
                                                    class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="price-text"><?php echo number_format($product['price'], 2); ?>
                                                zł</span>
                                        </td>
                                        <td>
                                            <?php if ($product['stock'] <= 0): ?>
                                                <span class="stock-indicator out">Brak (0)</span>
                                            <?php elseif ($product['stock'] <= 3): ?>
                                                <span class="stock-indicator low"><?php echo $product['stock']; ?> szt.</span>
                                            <?php else: ?>
                                                <span class="stock-indicator ok"><?php echo $product['stock']; ?> szt.</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>"
                                                    class="btn-icon-small" title="Edytuj">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_product.php?id=<?php echo $product['id']; ?>"
                                                    class="btn-icon-small delete" title="Usuń"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć ten produkt?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-microchip"></i>
                        <h3>Brak podzespołów w magazynie</h3>
                        <p>Podzespoły komputerowe oznaczone jako niewidoczne pojawią się tutaj</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    </div>

    <script>
        function switchTab(tabName) {
            // Ukryj wszystkie zakładki
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Usuń aktywny stan z przycisków
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Pokaż wybraną zakładkę
            if (tabName === 'products') {
                document.getElementById('products-tab').classList.add('active');
                document.querySelectorAll('.tab-btn')[0].classList.add('active');
            } else {
                document.getElementById('warehouse-tab').classList.add('active');
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
            }
        }

        function filterWarehouse(category) {
            // Usuń aktywny stan ze wszystkich przycisków filtrów
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Dodaj aktywny stan do klikniętego przycisku
            event.target.closest('.filter-btn').classList.add('active');

            // Pobierz wszystkie wiersze tabeli
            const rows = document.querySelectorAll('.warehouse-table tbody tr');

            // Filtruj wiersze
            rows.forEach(row => {
                if (category === 'all') {
                    row.style.display = '';
                } else {
                    if (row.getAttribute('data-category') === category) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }
    </script>
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
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    /* Tabs */
    .tabs-container {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 0;
    }

    .tab-btn {
        background: transparent;
        border: none;
        padding: 12px 25px;
        font-size: 1rem;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        bottom: -2px;
    }

    .tab-btn:hover {
        color: #ff6b35;
        background: rgba(255, 107, 53, 0.05);
    }

    .tab-btn.active {
        color: #ff6b35;
        border-bottom-color: #ff6b35;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tab-header {
        margin-bottom: 25px;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        border-left: 4px solid #ff6b35;
    }

    .tab-header h2 {
        margin: 0 0 8px 0;
        color: #2c3e50;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tab-header p {
        margin: 0;
        color: #666;
        font-size: 0.95rem;
    }

    .products-admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }

    .product-admin-card {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
    }

    .product-admin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .product-admin-card.warehouse-item {
        border: 2px solid #6c757d;
    }

    .product-admin-card.warehouse-item .product-admin-image {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    .product-admin-image {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .product-admin-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-admin-image i {
        font-size: 4rem;
        color: rgba(255, 255, 255, 0.8);
    }

    .product-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        z-index: 1;
        text-transform: uppercase;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        color: #fff;
    }

    .product-badge.warehouse {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }

    .product-badge.out {
        background: #dc3545;
    }

    .product-badge.low {
        background: #ffc107;
        color: #333;
    }

    .product-admin-info {
        padding: 20px;
    }

    .product-admin-info h3 {
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 1.2rem;
    }

    .product-admin-info p {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .meta-info {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .category-badge {
        display: inline-block;
        background: #e7f3ff;
        color: #004085;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .price-badge {
        display: inline-block;
        background: #d4edda;
        color: #155724;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .stock-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .stock-badge.in-stock {
        background: #d4edda;
        color: #155724;
    }

    .stock-badge.out-stock {
        background: #f8d7da;
        color: #721c24;
    }

    .olx-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #ff6b35;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .olx-link:hover {
        text-decoration: underline;
    }

    .product-admin-actions {
        display: flex;
        gap: 10px;
        padding: 15px 20px;
        border-top: 1px solid #f0f0f0;
        background: #f8f9fa;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 5rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #666;
        margin-bottom: 25px;
    }

    /* Warehouse Table Styles */
    .warehouse-table-container {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .warehouse-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .warehouse-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .warehouse-table th {
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .warehouse-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s ease;
    }

    .warehouse-table tbody tr:hover {
        background: #f8f9fa;
    }

    .warehouse-table td {
        padding: 10px 15px;
        vertical-align: middle;
    }

    .category-tag {
        display: inline-block;
        background: #e7f3ff;
        color: #004085;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .text-muted {
        color: #999;
        font-size: 0.85rem;
    }

    .price-text {
        font-weight: 600;
        color: #155724;
        white-space: nowrap;
    }

    .stock-indicator {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .stock-indicator.ok {
        background: #d4edda;
        color: #155724;
    }

    .stock-indicator.low {
        background: #fff3cd;
        color: #856404;
    }

    .stock-indicator.out {
        background: #f8d7da;
        color: #721c24;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
    }

    .btn-icon-small {
        padding: 6px 10px;
        background: #f8f9fa;
        color: #666;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        font-size: 0.85rem;
    }

    .btn-icon-small:hover {
        color: #ff6b35;
        border-color: #ff6b35;
        background: #fff5f0;
    }

    .btn-icon-small.delete:hover {
        color: #dc3545;
        border-color: #dc3545;
        background: #fff5f5;
    }

    /* Warehouse Filters */
    .warehouse-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .filter-btn {
        padding: 8px 16px;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        color: #666;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .filter-btn:hover {
        border-color: #ff6b35;
        color: #ff6b35;
        background: #fff5f0;
        transform: translateY(-2px);
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        border-color: #ff6b35;
        color: white;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }

    .filter-btn i {
        font-size: 0.9rem;
    }
</style>