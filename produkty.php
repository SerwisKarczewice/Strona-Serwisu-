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
        content="Sklep z podzespołami komputerowymi - karty graficzne, procesory, pamięci RAM, dyski. Atrakcyjne ceny!">
    <meta name="keywords" content="sklep komputerowy, podzespoły PC, karta graficzna, procesor, RAM, dysk SSD">
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
      "description": "Szeroki wybór podzespołów komputerowych i akcesoriów.",
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
      },
      "mainEntity": {
        "@type": "OfferCatalog",
        "name": "Katalog Produktów",
        "itemListElement": [
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Product",
              "name": "Karty Graficzne"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Product",
              "name": "Procesory"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Product",
              "name": "Pamięci RAM"
            }
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
                        <?php if ($product['featured']): ?>
                            <div class="product-badge hot">Bestseller</div>
                        <?php endif; ?>

                        <?php if ($product['stock'] <= 0): ?>
                            <div class="product-badge out">Brak w magazynie</div>
                        <?php elseif ($product['stock'] <= 3): ?>
                            <div class="product-badge low">Ostatnie sztuki</div>
                        <?php endif; ?>

                        <div class="product-image">
                            <?php if ($product['image_path'] && file_exists($product['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fas fa-microchip"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <?php if ($product['description']): ?>
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <?php endif; ?>
                            <div class="product-footer">
                                <span class="product-price"><?php echo number_format($product['price'], 0); ?> zł</span>
                                <?php if ($product['olx_link']): ?>
                                    <a href="<?php echo htmlspecialchars($product['olx_link']); ?>" target="_blank"
                                        class="btn-olx" title="Zobacz na OLX">
                                        <i class="fas fa-shopping-bag"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn-small"
                                        onclick="contactAboutProduct('<?php echo htmlspecialchars($product['name']); ?>')">
                                        <i class="fas fa-shopping-cart"></i>
                                        Zapytaj
                                    </button>
                                <?php endif; ?>
                            </div>
                            <?php if ($product['stock'] > 0): ?>
                                <div class="product-stock">
                                    <i class="fas fa-box"></i>
                                    Dostępne: <?php echo $product['stock']; ?> szt.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($products)): ?>
                    <div class="empty-products">
                        <i class="fas fa-box-open"></i>
                        <h3>Brak produktów w ofercie</h3>
                        <p>Pracujemy nad uzupełnieniem naszego katalogu</p>
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

        function contactAboutProduct(productName) {
            window.location.href = 'kontakt.php?product=' + encodeURIComponent(productName);
        }
    </script>
</body>

</html>

<style>
    .products-section {
        padding: 80px 0;
        background: var(--light-color);
    }

    .products-filter {
        display: flex;
        gap: 15px;
        margin-bottom: 40px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .filter-btn {
        padding: 12px 25px;
        border: 2px solid var(--primary-color);
        background: transparent;
        color: var(--primary-color);
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: var(--primary-color);
        color: #fff;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }

    .product-card {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
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
    }

    .product-badge.hot {
        background: var(--gradient-primary);
        color: #fff;
    }

    .product-badge.out {
        background: #dc3545;
        color: #fff;
    }

    .product-badge.low {
        background: #ffc107;
        color: #333;
    }

    .product-image {
        height: 200px;
        background: var(--light-color);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-image i {
        font-size: 5rem;
        color: var(--primary-color);
    }

    .product-info {
        padding: 25px;
    }

    .product-info h3 {
        font-size: 1.3rem;
        color: var(--dark-color);
        margin-bottom: 10px;
    }

    .product-info p {
        color: var(--text-light);
        font-size: 0.95rem;
        margin-bottom: 20px;
        min-height: 45px;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .product-price {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .btn-olx {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #002f34 0%, #23e5db 100%);
        color: #fff;
        border: none;
        border-radius: 15px;
        font-size: 1.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 5px 15px rgba(0, 47, 52, 0.3);
    }

    .btn-olx:hover {
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 10px 25px rgba(0, 47, 52, 0.4);
    }

    .btn-small {
        padding: 10px 20px;
        background: var(--gradient-primary);
        color: #fff;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-small:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
    }

    .product-stock {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #28a745;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .product-stock i {
        color: #28a745;
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
        padding: 60px 20px;
    }

    .empty-products i {
        font-size: 5rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-products h3 {
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .empty-products p {
        color: #666;
    }

    @media (max-width: 768px) {
        .products-filter {
            justify-content: center;
        }

        .filter-btn {
            font-size: 0.9rem;
            padding: 10px 20px;
        }

        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>