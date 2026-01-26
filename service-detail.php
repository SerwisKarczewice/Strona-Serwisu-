<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: oferta.php');
    exit;
}

$serviceId = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id AND is_active = 1");
$stmt->execute([':id' => $serviceId]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: oferta.php');
    exit;
}

// Function to format service description (same as products)
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

// Licznik wykonań z tabeli services
$executionCount = $service['execution_count'] ?? 0;

// Pobierz powiązane usługi (tej samej kategorii)
$stmt = $pdo->prepare("
    SELECT * FROM services 
    WHERE is_active = 1 AND id != :id AND category = :category
    ORDER BY RAND() 
    LIMIT 3
");
$stmt->execute([':id' => $serviceId, ':category' => $service['category']]);
$relatedServices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="<?php echo htmlspecialchars(substr(strip_tags($service['description']), 0, 160)); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($service['name']); ?>, usługa, serwis komputerowy">
    <title><?php echo htmlspecialchars($service['name']); ?> - Usługi | TechService</title>
    <link rel="icon" type="image/svg+xml" href="uploads/icons/favicon.svg">
    <link rel="canonical" href="https://twojadomena.pl/service-detail.php?id=<?php echo intval($service['id']); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($service['name']); ?>">
    <meta property="og:description"
        content="<?php echo htmlspecialchars(substr(strip_tags($service['description']), 0, 160)); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url"
        content="https://twojadomena.pl/service-detail.php?id=<?php echo intval($service['id']); ?>">

    <style>
        :root {
            --primary: #ff6b35;
            --primary-dark: #d95a2b;
            --dark: #2c3e50;
        }

        body {
            background: #fdfdfd;
            color: #2c3e50;
        }

        .service-hero {
            padding: 120px 0 50px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-bottom: 3px solid #f0f0f0;
        }

        .service-hero .container {
            position: relative;
            z-index: 1;
        }

        .service-category-badge {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            color: white;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .service-hero h1 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 25px;
            color: #1a202c;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .service-hero p {
            font-size: 1.2rem;
            opacity: 0.85;
            max-width: 800px;
            line-height: 1.7;
            color: #475569;
            font-weight: 500;
        }

        .execution-counter {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            margin-top: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .execution-counter i {
            font-size: 1.2rem;
        }

        .service-main {
            padding: 50px 0 80px;
            background: #fdfdfd;
        }

        .service-layout {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 50px;
            margin-bottom: 60px;
        }

        .service-content-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #f0f0f0;
        }

        .service-image-section {
            margin: 30px 0;
            text-align: center;
        }

        .service-image-section img {
            max-width: 100%;
            max-height: 450px;
            object-fit: contain;
            border-radius: 16px;
        }

        .service-detailed-description {
            margin: 30px 0;
        }

        .service-detailed-description h2 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: #1a202c;
            font-weight: 800;
            padding-bottom: 15px;
            border-bottom: 2px solid #f2f2f2;
        }

        /* Style z product-detail.php dla formatowanych list */
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

        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
            padding: 25px;
            background: #f8fafc;
            border-radius: 16px;
        }

        .feature-item {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .feature-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .feature-text h4 {
            margin: 0 0 0.4rem 0;
            color: #1a202c;
            font-weight: 700;
            font-size: 1rem;
        }

        .feature-text p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* Sidebar - Price Box */
        .price-sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .price-card {
            background: white;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #f0f0f0;
        }

        .price-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
        }

        .price-amount {
            font-size: 2.8rem;
            font-weight: 900;
            color: #1a202c;
            line-height: 1;
            margin-bottom: 25px;
        }

        .price-amount small {
            font-size: 1.1rem;
            font-weight: 700;
            color: #999;
            margin-left: 4px;
        }

        .price-old {
            display: block;
            font-size: 1.2rem;
            color: #999;
            text-decoration: line-through;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .discount-badge {
            background: linear-gradient(135deg, #d32f2f 0%, #c62828 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cta-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 18px 24px;
            background: #1a202c;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cta-btn:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .price-features {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #f2f2f2;
        }

        .price-features ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .price-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            color: #475569;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .price-features li i {
            color: #10b981;
            font-size: 1.1rem;
        }

        /* Related Services */
        .related-section {
            padding: 60px 0;
            background: white;
            border-top: 1px solid #f0f0f0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 2.4rem;
            color: #1a202c;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #64748b;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .related-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .related-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        }

        .related-card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 25px 20px;
            color: white;
        }

        .related-card-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .related-card-body {
            padding: 20px;
        }

        .related-card-body p {
            margin: 0 0 20px 0;
            color: #64748b;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .related-card-footer {
            padding: 0 20px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .related-price {
            font-size: 1.8rem;
            font-weight: 900;
            color: #1a202c;
        }

        .related-link {
            flex: 1;
            padding: 12px 24px;
            background: #1a202c;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .related-link:hover {
            background: var(--primary);
        }

        /* Package Badge */
        .package-badge {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%) !important;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .service-layout {
                grid-template-columns: 1fr;
            }

            .price-sidebar {
                position: relative;
                top: 0;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .service-hero h1 {
                font-size: 2rem;
            }

            .service-content-card {
                padding: 25px 20px;
            }

            .price-amount {
                font-size: 2.2rem;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <!-- HERO -->
    <section class="service-hero">
        <div class="container">
            <div class="service-category-badge <?php echo $service['category'] == 'package' ? 'package-badge' : ''; ?>">
                <?php echo $service['category'] == 'package' ? 'Pakiet Usług' : 'Usługa Pojedyncza'; ?>
            </div>
            <h1><?php echo htmlspecialchars($service['name']); ?></h1>
            <?php if (!empty($service['description'])): ?>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
            <?php endif; ?>
            <?php if ($executionCount > 0): ?>
                <div class="execution-counter">
                    <i class="fas fa-check-circle"></i>
                    <span>Wykonano już <strong><?php echo $executionCount; ?></strong>
                        <?php echo $executionCount == 1 ? 'raz' : 'razy'; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- GŁÓWNA ZAWARTOŚĆ -->
    <section class="service-main">
        <div class="container">
            <div class="service-layout">
                <!-- LEWA KOLUMNA - TREŚĆ -->
                <div class="service-content-card">
                    <?php if (!empty($service['detailed_description'])): ?>
                        <div class="service-detailed-description">
                            <h2 style="font-size: 2rem; margin-bottom: 1.5rem; color: var(--dark);">Opis</h2>
                            <div class="description-rich">
                                <?php echo formatProductDescription($service['detailed_description']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($service['image_path']) && file_exists($service['image_path'])): ?>
                        <div class="service-image-section">
                            <img src="<?php echo htmlspecialchars($service['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($service['name']); ?>" loading="lazy">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PRAWA KOLUMNA - CENA -->
                <div class="price-sidebar">
                    <div class="price-card">
                        <div class="price-label">Cena Usługi</div>

                        <div class="price-amount">
                            <?php if ($service['discount_price']): ?>
                                <span class="price-old"><?php echo number_format($service['price'], 0); ?> zł</span>
                                <?php echo number_format($service['discount_price'], 0); ?> <small>zł</small>
                            <?php else: ?>
                                <?php echo number_format($service['price'], 0); ?> <small>zł</small>
                            <?php endif; ?>
                        </div>

                        <?php if ($service['discount_price']): ?>
                            <div class="discount-badge">
                                <i class="fas fa-tag"></i>
                                <?php
                                $discount = (($service['price'] - $service['discount_price']) / $service['price'] * 100);
                                echo 'Oszczędzasz ' . round($discount) . '%';
                                ?>
                            </div>
                        <?php endif; ?>

                        <a href="kontakt.php" class="cta-btn">
                            <i class="fas fa-envelope"></i>
                            Zapytaj o Usługę
                        </a>

                        <div class="price-features">
                            <ul>
                                <li><i class="fas fa-check-circle"></i> Bezpłatna konsultacja</li>
                                <li><i class="fas fa-check-circle"></i> Profesjonalna obsługa</li>
                                <li><i class="fas fa-check-circle"></i> Gwarancja satysfakcji</li>
                                <li><i class="fas fa-check-circle"></i> Szybka realizacja</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- POWIĄZANE USŁUGI -->
    <?php if ($relatedServices): ?>
        <section class="related-section">
            <div class="container">
                <div class="section-header">
                    <h2>Inne <?php echo $service['category'] == 'package' ? 'Pakiety' : 'Usługi'; ?></h2>
                    <p>Zobacz również nasze inne oferty</p>
                </div>
                <div class="related-grid">
                    <?php foreach ($relatedServices as $related): ?>
                        <div class="related-card">
                            <div
                                class="related-card-header <?php echo $related['category'] == 'package' ? 'package-badge' : ''; ?>">
                                <h4><?php echo htmlspecialchars($related['name']); ?></h4>
                            </div>
                            <div class="related-card-body">
                                <p><?php echo htmlspecialchars(substr(strip_tags($related['description']), 0, 120)); ?>...</p>
                            </div>
                            <div class="related-card-footer">
                                <span class="related-price"><?php echo number_format($related['price'], 0); ?> zł</span>
                                <a href="service-detail.php?id=<?php echo intval($related['id']); ?>" class="related-link">
                                    Zobacz więcej
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js" defer></script>
</body>

</html>