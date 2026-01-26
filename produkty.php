<?php
require_once 'config.php';

$stmt = $pdo->query("SELECT * FROM products WHERE is_visible = 1 ORDER BY featured DESC, created_at DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Sklep z podzespołami komputerowymi - karty graficzne, procesory, pamięci RAM, dyski SSD. Wysokiej jakości komponenty w atrakcyjnych cenach!">
    <meta name="keywords"
        content="sklep komputerowy, podzespoły PC, karta graficzna, procesor, RAM, dysk SSD, komponenty PC">
    <link rel="icon" type="image/svg+xml" href="uploads/icons/favicon.svg">
    <title>Produkty - Podzespoły Komputerowe | TechService</title>
    <link rel="canonical" href="https://twojadomena.pl/produkty.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="Produkty - Podzespoły Komputerowe | TechService">
    <meta property="og:description"
        content="Sklep z podzespołami komputerowymi. Karty graficzne, procesory, pamięci RAM, dyski SSD wysokiej jakości. Sprawdź naszą ofertę!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://twojadomena.pl/produkty.php">
    <meta property="og:image" content="https://twojadomena.pl/images/produkty-og.jpg">
    <meta property="og:locale" content="pl_PL">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": "Sklep z Podzespołami Komputerowymi",
      "description": "Szeroki wybór wysokiej jakości podzespołów komputerowych w atrakcyjnych cenach.",
      "url": "https://twojadomena.pl/produkty.php",
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "Strona Główna",
            "item": "https://twojadomena.pl/"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Produkty",
            "item": "https://twojadomena.pl/produkty.php"
          }
        ]
      }
    }
    </script>
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>Nasze Produkty</h1>
            <p>Wysokiej jakości podzespoły komputerowe w atrakcyjnych cenach</p>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <div class="products-filter">
                <button class="filter-btn active" data-category="all">Wszystkie</button>
                <button class="filter-btn" data-category="laptopy">Laptopy</button>
                <button class="filter-btn" data-category="komputery">Komputery</button>
                <button class="filter-btn" data-category="monitory">Monitory</button>
                <button class="filter-btn" data-category="gpu">Karty Graficzne</button>
                <button class="filter-btn" data-category="cpu">Procesory</button>
                <button class="filter-btn" data-category="ram">Pamięci RAM</button>
                <button class="filter-btn" data-category="storage">Dyski</button>
                <button class="filter-btn" data-category="other">Inne</button>
            </div>

            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card"
                        data-category="<?php echo htmlspecialchars($product['category'] ?: 'other'); ?>">
                        <div class="card-badges">
                            <?php if ($product['featured']): ?>
                                <span class="badge hot">Bestseller</span>
                            <?php endif; ?>

                            <?php if ($product['stock'] <= 0): ?>
                                <span class="badge out">Brak</span>
                            <?php elseif ($product['stock'] <= 3): ?>
                                <span class="badge low">Ostatnie sztuki</span>
                            <?php endif; ?>
                        </div>

                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-image-box">
                            <div class="product-image">
                                <?php if ($product['image_path'] && file_exists($product['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="no-img"><i class="fas fa-microchip"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="image-overlay">
                                <span>Szczegóły <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </a>

                        <div class="product-details-new">
                            <div class="product-meta">
                                <span class="cat-tag"><?php echo htmlspecialchars($product['category'] ?: 'Inne'); ?></span>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="stock-tag"><i class="fas fa-check"></i> <?php echo $product['stock']; ?>
                                        szt.</span>
                                <?php endif; ?>
                            </div>

                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="title-link">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            </a>

                            <div class="product-bottom">
                                <div class="price-display">
                                    <span
                                        class="price-amount"><?php echo number_format($product['price'], 0, ',', ' '); ?></span>
                                    <span class="price-currency">zł</span>
                                </div>
                                <div class="card-actions">
                                    <?php if ($product['olx_link']): ?>
                                        <a href="<?php echo htmlspecialchars($product['olx_link']); ?>" target="_blank"
                                            class="action-btn olx" title="Kup na OLX">
                                            <i class="fas fa-shopping-bag"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="action-btn explore"
                                        title="Zobacz szczegóły">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <div class="empty-products">
                        <i class="fas fa-box-open"></i>
                        <h3>Brak produktów</h3>
                        <p>Wkrótce uzupełnimy ofertę!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Nie znalazłeś tego czego szukasz?</h2>
                <p>Skontaktuj się z nami - pomożemy dobrać odpowiednie podzespoły!</p>
                <a href="kontakt.php" class="btn btn-primary">
                    <i class="fas fa-phone"></i>
                    Skontaktuj się
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const category = this.dataset.category;
                    document.querySelectorAll('.product-card').forEach(card => {
                        if (category === 'all' || card.dataset.category === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>

<style>
    .products-section {
        padding: 80px 0;
        background: #fdfdfd;
    }

    .products-filter {
        display: flex;
        gap: 12px;
        margin-bottom: 50px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .filter-btn {
        padding: 10px 24px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        border-radius: 30px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .filter-btn.active {
        background: var(--gradient-primary);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    .product-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        display: flex;
        flex-direction: column;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
    }

    .card-badges {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .badge {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .badge.hot {
        background: #ff6b35;
        color: #fff;
    }

    .badge.out {
        background: #1e293b;
        color: #fff;
    }

    .badge.low {
        background: #f59e0b;
        color: #fff;
    }

    .product-image-box {
        display: block;
        position: relative;
        padding: 20px;
        background: #fff;
        text-decoration: none;
        overflow: hidden;
    }

    .product-image {
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fcfcfc;
        border-radius: 16px;
        overflow: hidden;
    }

    .product-image img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }

    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .image-overlay span {
        color: #fff;
        font-weight: 700;
        padding: 8px 18px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border-radius: 30px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-size: 0.9rem;
    }

    .product-card:hover .image-overlay {
        opacity: 1;
    }


    .product-details-new {
        padding: 0 25px 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .cat-tag {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .stock-tag {
        font-size: 0.75rem;
        font-weight: 700;
        color: #10b981;
    }

    .title-link {
        text-decoration: none;
        color: #1e293b;
    }

    .product-details-new h3 {
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 20px;
        transition: color 0.3s;
    }

    .product-card:hover h3 {
        color: var(--primary-color);
    }

    .product-bottom {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #f1f5f9;
    }

    .price-display {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .price-amount {
        font-size: 1.7rem;
        font-weight: 900;
        color: #1e293b;
    }

    .price-currency {
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 700;
    }

    .card-actions {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }

    .action-btn.olx {
        background: linear-gradient(135deg, #002f34 0%, #23e5db 100%);
        color: #fff;
    }

    .action-btn.explore {
        background: #f1f5f9;
        color: #64748b;
    }


    .action-btn.olx:hover {
        box-shadow: 0 8px 15px rgba(0, 47, 52, 0.2);
    }

    .action-btn.explore:hover {
        background: var(--primary-color);
        color: #fff;
    }

    .cta-section {
        padding: 100px 0;
        background: var(--gradient-primary);
        color: #fff;
        text-align: center;
    }

    .cta-content h2 {
        font-size: 2.5rem;
        margin-bottom: 20px;
    }

    .cta-content p {
        font-size: 1.3rem;
        margin-bottom: 40px;
        opacity: 0.95;
    }

    .empty-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 0;
        color: #94a3b8;
    }

    .empty-products i {
        font-size: 4rem;
        margin-bottom: 20px;
    }

    @media (max-width: 600px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

</html>