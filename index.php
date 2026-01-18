<?php
require_once 'config.php';

// Pobierz aktualności z bazy danych
$stmt = $pdo->query("SELECT * FROM news WHERE published = 1 ORDER BY created_at DESC LIMIT 3");
$news_items = $stmt->fetchAll();

// Licznik odwiedzin
require_once 'includes/visit_counter.php';
increment_site_visits();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Profesjonalny serwis komputerowy - naprawa laptopów, komputerów stacjonarnych, serwis sprzętu IT. Szybko, tanio, profesjonalnie. Dowiedz się więcej!">
    <meta name="keywords"
        content="serwis komputerowy, naprawa laptopów, naprawa komputerów, serwis IT, naprawa sprzętu komputerowego">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Serwis Komputerowy">
    <meta property="og:title" content="Profesjonalny Serwis Komputerowy - Naprawa i Modernizacja">
    <meta property="og:description"
        content="Zajmujemy się kompleksowym serwisem komputerowym. Naprawa, modernizacja, doradztwo. Zaufaj profesjonalistom!">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pl_PL">
    <meta property="og:url" content="https://twojadomena.pl/">
    <meta property="og:image" content="https://twojadomena.pl/images/og-home.jpg">
    <meta property="og:site_name" content="TechService">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ComputerRepair",
      "name": "TechService - Serwis Komputerowy",
      "image": "https://twojadomena.pl/images/logo.png",
      "url": "https://twojadomena.pl/",
      "telephone": "+48 123 456 789",
      "priceRange": "$$",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "ul. Przykładowa 123",
        "addressLocality": "Warszawa",
        "postalCode": "00-001",
        "addressCountry": "PL"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 52.2297,
        "longitude": 21.0122
      },
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday"
          ],
          "opens": "09:00",
          "closes": "18:00"
        },
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": "Saturday",
          "opens": "10:00",
          "closes": "14:00"
        }
      ],
      "sameAs": [
        "https://facebook.com/TwojSerwis",
        "https://instagram.com/TwojSerwis"
      ]
    }
    </script>
    <title>Serwis Komputerowy - Profesjonalna Naprawa i Modernizacja | Szybko i Tanio</title>
    <link rel="canonical" href="https://twojadomena.pl/">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/home.css?v=1.1">
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <header class="hero">
        <div class="hero-overlay"></div>

        <!-- Animowane ikonki w tle -->
        <div class="floating-icons">
            <i class="fas fa-laptop floating-icon" style="top: 15%; left: 10%; animation-delay: 0s;"></i>
            <i class="fas fa-microchip floating-icon" style="top: 25%; left: 80%; animation-delay: 2s;"></i>
            <i class="fas fa-server floating-icon" style="top: 60%; left: 15%; animation-delay: 4s;"></i>
            <i class="fas fa-keyboard floating-icon" style="top: 70%; left: 75%; animation-delay: 1s;"></i>
            <i class="fas fa-hdd floating-icon" style="top: 40%; left: 85%; animation-delay: 3s;"></i>
            <i class="fas fa-memory floating-icon" style="top: 80%; left: 50%; animation-delay: 5s;"></i>
            <i class="fas fa-desktop floating-icon" style="top: 20%; left: 40%; animation-delay: 1.5s;"></i>
            <!-- <i class="fas fa-usb floating-icon" style="top: 50%; left: 25%; animation-delay: 3.5s;"></i> -->
            <i class="fas fa-mouse floating-icon" style="top: 35%; left: 60%; animation-delay: 2.5s;"></i>
            <i class="fas fa-wifi floating-icon" style="top: 65%; left: 90%; animation-delay: 4.5s;"></i>
        </div>

        <div class="container hero-content">
            <h1 class="hero-title">

                Serwis Komputerowy
                <span class="title-accent">Karczewice</span>
            </h1>
            <p class="hero-subtitle">Zajmiemy się Twoim komuterem szybko i profesjonalnie. Doświadczenie, jakość i
                uczciwe
                ceny to nasza wizytówka!</p>
            <div class="hero-buttons">
                <a href="oferta.php" class="btn btn-primary">
                    <i class="fas fa-tools"></i>
                    Zobacz Ofertę
                </a>
                <a href="kontakt.php" class="btn btn-secondary">
                    <i class="fas fa-phone"></i>
                    Skontaktuj się
                </a>
            </div>
            <div class="hero-features">
                <div class="hero-feature">
                    <i class="fas fa-clock"></i>
                    <span>Szybka realizacja</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-certificate"></i>
                    <span>Gwarancja jakości</span>
                </div>
                <div class="hero-feature">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Uczciwe ceny</span>
                </div>
            </div>
        </div>
        <div class="hero-scroll">
            <i class="fas fa-chevron-down"></i>
        </div>
    </header>

    <section class="why-us">
        <div class="container">
            <h2 class="section-title">Dlaczego My?</h2>
            <div class="why-grid">
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>Najtańsi na rynku</h3>
                    <p>Porównując ceny innych serwisów i sklepów z elektroniką jesteśmy w stanie zaoferować najniższe
                        ceny na rynku.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <h3>Specjaliści od PC</h3>
                    <p>Składanie komputerów to nasz konik. Dobieramy idealne podzespoły, dbamy o perfekcyjny cable
                        management i testujemy sprzęt, by służył Ci przez lata.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Zależy Nam Bardziej</h3>
                    <p>Dopiero budujemy swoją markę, dlatego każdy Klient jest dla nas VIP-em. Wkładamy w pracę 110%
                        zaangażowania, by zapracować na Twoją pozytywną opinię.</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Uczciwe Podejście</h3>
                    <p>Jasne zasady i konkurencyjne ceny. Nie narzucamy zbędnych usług. Doradzamy tak, jak sami
                        chcielibyśmy być obsłużeni.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="offer-preview">
        <div class="container">
            <h2 class="section-title">Nasza Oferta</h2>

            <div class="services-slider-wrapper">
                <div class="services-slider" id="servicesSlider">
                    <?php
                    // Pobierz wszystkie aktywne usługi pojedyncze
                    $stmt = $pdo->query("SELECT * FROM services WHERE category = 'single' AND is_active = 1 ORDER BY display_order ASC");
                    $all_services = $stmt->fetchAll();

                    if (!empty($all_services)):
                        $icons = ['fa-tools', 'fa-laptop', 'fa-desktop', 'fa-hdd', 'fa-microchip', 'fa-network-wired', 'fa-shield-virus', 'fa-cog', 'fa-wrench', 'fa-fan', 'fa-memory', 'fa-ethernet'];

                        // Duplikuj usługi dla ciągłej animacji
                        $services_to_display = array_merge($all_services, $all_services, $all_services);

                        foreach ($services_to_display as $index => $service):
                            $icon = $icons[$index % count($icons)];
                            ?>
                            <div class="service-slide">
                                <div class="offer-icon">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                                <div class="offer-price">
                                    <?php if ($service['discount_price']): ?>
                                        <span class="price-old"><?php echo number_format($service['price'], 0); ?> zł</span>
                                        <span class="price-new"><?php echo number_format($service['discount_price'], 0); ?>
                                            zł</span>
                                    <?php else: ?>
                                        <span class="price-regular"><?php echo number_format($service['price'], 0); ?> zł</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        endforeach;
                    else:
                        ?>
                        <div class="service-slide">
                            <div class="offer-icon"><i class="fas fa-laptop"></i></div>
                            <h3>Naprawa Laptopów</h3>
                            <div class="offer-price"><span class="price-regular">150 zł</span></div>
                        </div>
                        <div class="service-slide">
                            <div class="offer-icon"><i class="fas fa-desktop"></i></div>
                            <h3>Serwis PC</h3>
                            <div class="offer-price"><span class="price-regular">120 zł</span></div>
                        </div>
                        <div class="service-slide">
                            <div class="offer-icon"><i class="fas fa-hdd"></i></div>
                            <h3>Odzyskiwanie Danych</h3>
                            <div class="offer-price"><span class="price-regular">200 zł</span></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="offer-cta">
                <a href="oferta.php" class="btn btn-primary">
                    <i class="fas fa-list"></i>
                    Pełna Oferta
                </a>
            </div>
        </div>
    </section>

    <section class="news">
        <div class="container">
            <h2 class="section-title">Aktualności</h2>
            <div class="news-grid" id="newsContainer">
                <?php if (!empty($news_items)): ?>
                    <?php foreach ($news_items as $news): ?>
                        <div class="news-card">
                            <div class="news-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('d.m.Y', strtotime($news['created_at'])); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                            <p><?php echo htmlspecialchars($news['excerpt'] ?: substr($news['content'], 0, 150) . '...'); ?></p>
                            <a href="news-detail.php?slug=<?php echo urlencode($news['slug']); ?>" class="news-link">
                                Czytaj więcej <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-news-message"
                        style="grid-column: 1 / -1; text-align: center; color: var(--text-light); font-size: 1.2rem; padding: 40px;">
                        <i class="far fa-newspaper"
                            style="font-size: 3rem; margin-bottom: 20px; color: #ccc; display: block;"></i>
                        Brak aktualności na ten moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="visit-section">
        <div class="container">
            <h2 class="section-title">Jak Możemy Się Spotkać?</h2>
            <p class="visit-subtitle">Wybierz wygodną dla Ciebie opcję - dopasowujemy się do Twoich potrzeb!</p>
            <div class="visit-grid">
                <div class="visit-card">
                    <div class="visit-icon home">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3>Wizyta w Serwisie</h3>
                    <p>Zapraszamy do naszego profesjonalnie wyposażonego serwisu. Pełna diagnostyka, dostęp do
                        wszystkich narzędzi i szybka realizacja.</p>
                    <ul class="visit-benefits">
                        <li><i class="fas fa-check"></i> Po 24h możesz odebrać komputer</li>
                        <li><i class="fas fa-check"></i> Natychmiastowa diagnostyka</li>
                    </ul>
                </div>
                <div class="visit-card">
                    <div class="visit-icon mobile">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Wizyta u Klienta</h3>
                    <p>Przyjedziemy do Ciebie! Oszczędzasz czas i wygodę - naprawiamy sprzęt w Twoim domu lub firmie bez
                        konieczności transportu.</p>
                    <ul class="visit-benefits">
                        <li><i class="fas fa-check"></i> Oszczędność czasu</li>
                        <li><i class="fas fa-check"></i> Usługa wykonana na miejscu</li>
                        <li><i class="fas fa-times" style="color: red;"></i> 7 zł za dojazd</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-form">
        <div class="container">
            <h2 class="section-title">Formularz Kontaktowy</h2>
            <div class="contact-wrapper">

                <form class="form" id="contactForm" method="POST" action="send_message.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Imię i Nazwisko *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Numer Telefonu *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Adres Zamieszkania</label>
                        <input type="text" id="address" name="address"
                            placeholder="np. ul. Przykładowa 123, 00-001 Twoja miejscowość">
                    </div>
                    <div class="form-group">
                        <label for="subject">Temat Wiadomości *</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Treść Wiadomości *</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="privacy" name="privacy" required>
                            <span>Akceptuję politykę prywatności i wyrażam zgodę na przetwarzanie moich danych osobowych
                                *</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Wyślij Wiadomość
                    </button>
                    <div id="formMessage" class="form-message"></div>
                </form>
                <div class="contact-info">
                    <h3>Skontaktuj się z nami</h3>
                    <p>Masz pytania? Potrzebujesz wyceny? Wypełnij formularz, a my skontaktujemy się z Tobą najszybciej
                        jak to możliwe!</p>

                    <div class="contact-details">
                        <div class="contact-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <strong>Adres:</strong>
                                <p>ul. Nadrzeczna 3b<br>42-270 Karczewice</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Telefon:</strong>
                                <p>+48 662 993 490 / 536 200 332</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email:</strong>
                                <p>SerwisBiuroKarczewice@gmail.com</p>
                            </div>
                        </div>
                        <div class="contact-detail">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Godziny otwarcia:</strong>
                                <p>Pn-Pt: 16.00 - 20.00<br>Sob-Nd: 9.00 - 20.00</p>
                            </div>

                        </div>
                    </div>
                    <b>*Po odczytaniu Twojej wiadomości oddzwonimy do Ciebie w ciągu 24h!
                        Jeśli nieodbierzesz, wyślemy sms-a.
                    </b>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="js/home.js"></script>
</body>
<style>
    .visit-section {
        padding: 80px 0;
        background: #fff;
    }

    .visit-subtitle {
        text-align: center;
        font-size: 1.2rem;
        color: var(--text-light);
        margin-bottom: 50px;
        margin-top: -10px;
    }

    .visit-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 40px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .visit-card {
        background: var(--light-color);
        border-radius: 20px;
        padding: 45px 40px;
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }

    .visit-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .visit-card:hover::before {
        transform: scaleX(1);
    }

    .visit-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    .visit-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        transition: all 0.3s ease;
    }

    .visit-icon.home {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .visit-icon.mobile {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .visit-card:hover .visit-icon {
        transform: scale(1.08) rotate(-5deg);
    }

    .visit-icon i {
        font-size: 3.5rem;
        color: #fff;
    }

    .visit-card h3 {
        font-size: 1.9rem;
        color: var(--dark-color);
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }

    .visit-card>p {
        color: var(--text-light);
        line-height: 1.8;
        margin-bottom: 25px;
        text-align: center;
        font-size: 1.05rem;
    }

    .visit-benefits {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .visit-benefits li {
        color: var(--dark-color);
        padding: 12px 0;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .visit-benefits li:last-child {
        border-bottom: none;
    }

    .visit-benefits li i {
        color: #4CAF50;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
</style>

</html>