<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
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
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-laptop-code"></i>
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="messages.php" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    <span>Wiadomości</span>
                </a>
                <a href="news.php" class="nav-link">
                    <i class="fas fa-newspaper"></i>
                    <span>Aktualności</span>
                </a>
                <a href="gallery.php" class="nav-link">
                    <i class="fas fa-images"></i>
                    <span>Galeria</span>
                </a>
                <a href="products.php" class="nav-link active">
                    <i class="fas fa-box"></i>
                    <span>Produkty</span>
                </a>
                <a href="services.php" class="nav-link">
                    <i class="fas fa-tools"></i>
                    <span>Usługi</span>
                </a>
                <a href="calculator.php" class="nav-link ">
                    <i class="fas fa-calculator"></i>
                    <span>Kalkulator</span>
                </a>
                <a href="invoices.php" class="nav-link">
                    <i class="fas fa-file-invoice"></i>
                    <span>Faktury</span>
                </a>
                <a href="../index.php" class="nav-link" target="_blank">
                    <i class="fas fa-eye"></i>
                    <span>Zobacz stronę</span>
                </a>
                <a href="logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Wyloguj</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="content-header">
                <h1>Produkty</h1>
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
                <div class="products-admin-grid">
                    <?php foreach ($products as $product): ?>
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

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>Brak produktów</h3>
                        <p>Dodaj pierwszy produkt do swojego katalogu</p>
                        <a href="add_product.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Dodaj Produkt
                        </a>
                    </div>
                <?php endif; ?>
            </div>
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
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
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
</style>