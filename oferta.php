<?php
require_once 'config.php';

// Pobierz usługi pojedyncze z bazy
$stmt = $pdo->query("SELECT * FROM services WHERE category = 'single' AND is_active = 1 ORDER BY display_order ASC");
$single_services = $stmt->fetchAll();

// Pobierz pakiety z bazy
$stmt = $pdo->query("SELECT * FROM services WHERE category = 'package' AND is_active = 1 ORDER BY display_order ASC");
$packages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Oferta SKK: składanie komputerów, kupowanie PC, naprawa laptopów, czyszczenie, serwis sprzętu IT. Ceny konkurencyjne, najlepsi lokalni specjaliści!">
    <meta name="keywords"
        content="składanie PC, cena składania komputera, kupowanie komputera, czyszczenie komputera, naprawa laptopów, serwis IT Karczewice, usługi serwisowe">
    <title>Oferta i Cennik - Profesjonalny Serwis Komputerowy | TechService</title>
    <link rel="canonical" href="https://twojadomena.pl/oferta.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="Oferta Usług - Składanie PC, Naprawa, Czyszczenie | SKK Karczewice">
    <meta property="og:description"
        content="Pełna oferta usług: składanie komputerów, kupowanie PC, naprawa sprzętu, czyszczenie. Profesjonalny serwis, konkurencyjne ceny, najlepszy support!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://twojadomena.pl/oferta.php">
    <meta property="og:image" content="https://twojadomena.pl/images/oferta-og.jpg">
    <meta property="og:locale" content="pl_PL">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Service",
      "name": "Naprawa i Serwis Komputerowy",
      "provider": {
        "@type": "LocalBusiness",
        "name": "TechService - Serwis Komputerowy Karczewice",
        "image": "https://twojadomena.pl/images/logo.png",
        "telephone": "+48 662 993 490",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "ul. Nadrzeczna 3b",
          "addressLocality": "Karczewice",
          "postalCode": "42-270",
          "addressCountry": "PL"
        },
        "priceRange": "$$"
      },
      "areaServed": {
        "@type": "City",
        "name": "Karczewice"
      },
      "hasOfferCatalog": {
        "@type": "OfferCatalog",
        "name": "Usługi Serwisowe",
        "itemListElement": [
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Naprawa Laptopów"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Składanie Komputerów PC"
            }
          },
          {
            "@type": "Offer",
            "itemOffered": {
              "@type": "Service",
              "name": "Instalacja Oprogramowania"
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
            <h1>Nasza Oferta</h1>
            <p>Profesjonalne usługi serwisowe w konkurencyjnych cenach</p>
        </div>
    </section>

    <section class="services-section">
        <div class="container">
            <h2 class="section-title">Usługi Pojedyncze</h2>

            <div class="pricing-grid">
                <?php foreach ($single_services as $service): ?>
                    <a href="service-detail.php?id=<?php echo intval($service['id']); ?>" class="price-card" style="text-decoration: none; color: inherit; transition: all 0.3s ease; cursor: pointer;">
                        <div class="price-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                        <div class="price">
                            <?php if ($service['discount_price']): ?>
                                <span class="old-price"><?php echo number_format($service['price'], 0); ?> zł</span>
                                <span class="new-price"><?php echo number_format($service['discount_price'], 0); ?> zł</span>
                            <?php else: ?>
                                <?php echo number_format($service['price'], 0); ?> zł
                            <?php endif; ?>
                        </div>
                        <?php if ($service['description']): ?>
                            <p class="service-desc"><?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>...</p>
                        <?php endif; ?>
                        <p style="color: var(--primary-color); font-weight: 600; margin-top: 1rem;">
                            Więcej szczegółów <i class="fas fa-arrow-right"></i>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="packages-section">
        <div class="container">
            <h2 class="section-title">Pakiety Usług</h2>
            <p class="section-subtitle">Wybierz gotowy pakiet i oszczędź!</p>

            <div class="packages-grid">
                <?php
                $package_index = 0;
                foreach ($packages as $package):
                    $is_featured = ($package_index === 1); // Drugi pakiet będzie wyróżniony
                    $package_index++;
                    ?>
                    <a href="service-detail.php?id=<?php echo intval($package['id']); ?>" class="package-card <?php echo $is_featured ? 'featured' : ''; ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; transition: all 0.3s ease;">
                        <?php if ($is_featured): ?>
                            <div class="package-badge">Polecane</div>
                        <?php endif; ?>

                        <div class="package-header">
                            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                            <div class="package-price">
                                <?php if ($package['discount_price']): ?>
                                    <span class="price-old"><?php echo number_format($package['price'], 0); ?></span>
                                    <span class="price-value"><?php echo number_format($package['discount_price'], 0); ?></span>
                                <?php else: ?>
                                    <span class="price-value"><?php echo number_format($package['price'], 0); ?></span>
                                <?php endif; ?>
                                <span class="price-currency">zł</span>
                            </div>
                        </div>

                        <?php if ($package['description']): ?>
                            <div class="package-description">
                                <?php
                                // Rozdziel opis na linie i wyświetl jako listę
                                $lines = explode("\n", $package['description']);
                                echo '<ul class="package-features">';
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (!empty($line)) {
                                        // Usuń znaki + lub - z początku jeśli są
                                        $line = preg_replace('/^[+\-]\s*/', '', $line);
                                        echo '<li><i class="fas fa-check-circle"></i> ' . htmlspecialchars($line) . '</li>';
                                    }
                                }
                                echo '</ul>';
                                ?>
                            </div>
                        <?php endif; ?>

                        <p style="color: var(--primary-color); font-weight: 600; margin-top: auto; padding-top: 1rem;">
                            Dowiedz się więcej <i class="fas fa-arrow-right"></i>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Nieznalazłeś usługi, która Cię interesuje?</h2>
                <p>Skontaktuj się z nami, a my zrobimy wszystko co w naszej mocy aby Ci pomóc! </p>
                <div class="cta-buttons">

                    <a href="kontakt.php" class="btn btn-primary">
                        <i class="fas fa-phone"></i>
                        Skontaktuj się
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>

</html>

<style>
    .service-desc {
        font-size: 0.95rem;
        color: var(--text-light);
        line-height: 1.6;
    }

    .old-price {
        display: block;
        text-decoration: line-through;
        color: #999;
        font-size: 1.5rem;
        margin-bottom: 5px;
    }

    .new-price {
        display: block;
        color: #28a745;
        font-size: 2.5rem;
        font-weight: 700;
    }

    .package-description {
        margin-bottom: 20px;
    }

    .package-description ul {
        list-style: none;
        padding: 0;
    }

    .package-description ul li {
        padding: 8px 0;
        color: var(--text-light);
    }

    .package-card.featured .package-description ul li {
        color: rgba(255, 255, 255, 0.95);
    }

    .price-old {
        text-decoration: line-through;
        color: #999;
        font-size: 1.5rem;
        display: block;
        margin-bottom: 5px;
    }

    /* self-added 8*/

    .cta-section {
        padding: 50px 0;
        background: var(--gradient-primary);
        color: #fff;
    }

    .cta-content {
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

    .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .about-grid {
            grid-template-columns: 1fr;
        }

        .about-image {
            height: 300px;
        }

        .about-image i {
            font-size: 6rem;
        }

        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
    }
</style>