<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: produkty.php');
    exit;
}

$productId = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND is_visible = 1");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: produkty.php');
    exit;
}

// Function to format product description
function formatProductDescription($text)
{
    if (empty($text))
        return '';

    // Safety first
    $text = htmlspecialchars($text);

    // Split into lines
    $lines = explode("\n", $text);
    $html = "";
    $inList = false;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            if ($inList) {
                $html .= "</ul>";
                $inList = false;
            }
            continue;
        }

        // Check if line is a list item (starts with -, *, or •)
        if (preg_match('/^([\-\*\•])\s*(.*)$/u', $line, $matches)) {
            $bulletChar = $matches[1];
            $content = $matches[2];

            $typeClass = 'list-bullet';
            if ($bulletChar === '-')
                $typeClass = 'list-dash';
            elseif ($bulletChar === '*')
                $typeClass = 'list-star';

            if (!$inList) {
                $html .= "<ul class='product-spec-list'>";
                $inList = true;
            }
            $html .= "<li class='$typeClass'>" . $content . "</li>";
        } else {
            if ($inList) {
                $html .= "</ul>";
                $inList = false;
            }
            // Check if line looks like a header (all caps or ending with :)
            if (preg_match('/^[A-ZĄĆĘŁŃÓŚŹŻ\s]{3,}:?$/u', $line) || str_ends_with($line, ':')) {
                $html .= "<h4>" . $line . "</h4>";
            } else {
                $html .= "<p>" . $line . "</p>";
            }
        }
    }

    if ($inList) {
        $html .= "</ul>";
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="<?php echo htmlspecialchars(substr(strip_tags($product['description']), 0, 160)); ?>">
    <meta name="keywords"
        content="<?php echo htmlspecialchars($product['name']); ?>, podzespoły komputerowe, sklep, cena">
    <title><?php echo htmlspecialchars($product['name']); ?> - Sklep | TechService</title>
    <link rel="icon" type="image/svg+xml" href="uploads/icons/favicon.svg">
    <link rel="canonical" href="https://twojadomena.pl/product-detail.php?id=<?php echo intval($product['id']); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?> - Sklep">
    <meta property="og:description"
        content="<?php echo htmlspecialchars(substr(strip_tags($product['description']), 0, 160)); ?>">
    <meta property="og:type" content="product">
    <meta property="og:url"
        content="https://twojadomena.pl/product-detail.php?id=<?php echo intval($product['id']); ?>">
    <meta property="og:image" content="https://twojadomena.pl/images/product-og.jpg">
    <meta property="og:locale" content="pl_PL">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="product-detail-page">
    <?php include 'includes/nav.php'; ?>

    <section class="detail-hero">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php">Główna</a> / <a href="produkty.php">Produkty</a> /
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
        </div>
    </section>

    <section class="product-detail-content">
        <div class="container">
            <div class="detail-grid">
                <!-- Left: Image -->
                <div class="detail-image-box">
                    <?php if ($product['featured']): ?>
                        <div class="product-badge hot" style="top: 20px; left: 20px;">Bestseller</div>
                    <?php endif; ?>

                    <?php if ($product['image_path'] && file_exists($product['image_path'])): ?>
                        <div class="main-image">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>" id="mainProductImg" loading="lazy">
                        </div>
                    <?php else: ?>
                        <div class="no-image-big">
                            <i class="fas fa-microchip"></i>
                            <p>Brak zdjęcia produktu</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Info -->
                <div class="detail-info-box">
                    <div class="detail-header">
                        <span
                            class="detail-category"><?php echo htmlspecialchars($product['category'] ?: 'Inne'); ?></span>
                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                        <div class="detail-meta">
                            <div class="detail-price"><?php echo number_format($product['price'], 0, ',', ' '); ?>
                                <span>zł</span>
                            </div>
                            <div class="detail-stock <?php echo ($product['stock'] > 0) ? 'in' : 'out'; ?>">
                                <i
                                    class="fas <?php echo ($product['stock'] > 0) ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                <?php echo ($product['stock'] > 0) ? 'Dostępny (' . $product['stock'] . ' szt.)' : 'Produkt niedostępny'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="detail-actions">
                        <?php if ($product['olx_link']): ?>
                            <a href="<?php echo htmlspecialchars($product['olx_link']); ?>" target="_blank"
                                class="btn-detail btn-olx-new">
                                <i class="fas fa-shopping-bag"></i> Kup przez OLX
                            </a>
                        <?php endif; ?>

                        <a href="kontakt.php?product=<?php echo urlencode($product['name']); ?>"
                            class="btn-detail btn-contact-new">
                            <i class="fas fa-envelope"></i> Zapytaj o szczegóły
                        </a>
                    </div>

                    <div class="f-list-simple">
                        <div class="f-item-s">
                            <i class="fas fa-user-check"></i>
                            <span>Odbiór osobisty w naszym serwisie</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-tabs-section">
                <div class="tab-header">
                    <button class="tab-btn active">Opis i specyfikacja</button>
                </div>
                <div class="tab-content" id="desc">
                    <div class="description-rich">
                        <?php echo formatProductDescription($product['description']); ?>
                    </div>
                </div>
            </div>

            <div class="detail-back">
                <a href="produkty.php" class="btn-back"><i class="fas fa-arrow-left"></i> Powrót do oferty</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js" defer></script>

    <style>
        .product-detail-page {
            background: #fdfdfd;
            color: #2c3e50;
        }

        .detail-hero {
            padding: 100px 0 25px;
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
        }

        .breadcrumb {
            font-size: 0.85rem;
            color: #999;
        }

        .breadcrumb a {
            color: #999;
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: var(--primary-color);
        }

        .breadcrumb span {
            color: #2c3e50;
            font-weight: 600;
        }

        .product-detail-content {
            padding: 50px 0 80px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 50px;
            margin-bottom: 60px;
        }

        .detail-image-box {
            background: #fff;
            border-radius: 24px;
            padding: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #f0f0f0;
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .main-image img {
            max-width: 100%;
            max-height: 450px;
            object-fit: contain;
        }

        .detail-info-box {
            padding: 10px 0;
        }

        .detail-category {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--primary-color);
            letter-spacing: 1.5px;
            margin-bottom: 15px;
            display: block;
        }

        .detail-header h1 {
            font-size: 2.4rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #1a202c;
            line-height: 1.2;
        }

        .detail-meta {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid #f2f2f2;
        }

        .detail-price {
            font-size: 2.8rem;
            font-weight: 900;
            color: #1a202c;
        }

        .detail-price span {
            font-size: 1.1rem;
            color: #999;
            font-weight: 700;
            margin-left: 4px;
        }

        .detail-stock {
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-stock.in {
            color: #10b981;
        }

        .detail-stock.out {
            color: #ef4444;
        }

        .detail-actions {
            display: grid;
            gap: 12px;
            margin-bottom: 40px;
        }

        .btn-detail {
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-olx-new {
            background: linear-gradient(135deg, #002f34 0%, #23e5db 100%);
            color: #fff;
        }

        .btn-olx-new:hover {
            box-shadow: 0 10px 20px rgba(0, 47, 52, 0.2);
        }

        .btn-contact-new {
            background: #1a202c;
            color: #fff;
        }

        .btn-contact-new:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }


        .detail-tabs-section {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
            margin-bottom: 30px;
        }

        .tab-header {
            border-bottom: 2px solid #f2f2f2;
            margin-bottom: 30px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 0 0 15px;
            font-size: 1.2rem;
            font-weight: 800;
            color: #1a202c;
            position: relative;
        }

        .tab-btn::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-color);
        }

        .description-rich {
            line-height: 1.7;
            color: #475569;
            font-size: 1.05rem;
        }

        .description-rich p {
            margin-bottom: 18px;
        }

        .description-rich h4 {
            font-size: 1.4rem;
            margin: 30px 0 15px;
            color: #1a202c;
            font-weight: 800;
        }

        .product-spec-list {
            list-style: none;
            background: #f8fafc;
            padding: 25px;
            border-radius: 16px;
            margin: 25px 0;
        }

        .product-spec-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .product-spec-list li:last-child {
            border-bottom: none;
        }

        .product-spec-list li::before {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 12px;
        }

        .product-spec-list li.list-bullet::before {
            content: '\f058';
            color: #10b981;
        }

        .product-spec-list li.list-dash::before {
            content: '\f068';
            color: #64748b;
        }

        .product-spec-list li.list-star::before {
            content: '\f005';
            color: #f59e0b;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }

        @media (max-width: 900px) {
            .detail-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .detail-image-box {
                position: static;
            }
        }
    </style>
</body>

</html>