<?php
require_once 'config.php';

// Pobierz produkty
$stmt = $pdo->query("SELECT id, name FROM products WHERE is_visible = 1 ORDER BY name ASC");
$products = $stmt->fetchAll();

// Pobierz usługi - WSZYSTKIE, podzielimy je w HTML
$stmt = $pdo->query("SELECT id, name, category FROM services WHERE is_active = 1 ORDER BY name ASC");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Skontaktuj się z SKK - Serwis Komputerowy Karczewice. Składanie komputerów, kupowanie PC, naprawa, czyszczenie. Zarezerwuj usługę online!">
    <meta name="keywords"
        content="kontakt serwis komputerowy, zarezerwuj składanie PC, wycena naprawa komputera, czyszczenie komputera Karczewice, serwis IT kontakt">
    <title>Kontakt - Serwis Komputerowy | TechService</title>
    <link rel="canonical" href="https://twojadomena.pl/kontakt.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="Kontakt - Serwis Komputerowy | TechService">
    <meta property="og:description"
        content="Skontaktuj się z nami - chętnie odpowiemy na wszystkie pytania. Adres, telefon, email i formularz kontaktowy.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://twojadomena.pl/kontakt.php">
    <meta property="og:image" content="https://twojadomena.pl/images/kontakt-og.jpg">
    <meta property="og:locale" content="pl_PL">

    <style>
        :root {
            --primary-color: #ff6b35;
            --primary-dark: #d95a2b;
            --light-bg: #f5f7fa;
            --border-color: #e0e0e0;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .contact-main {
            padding: 80px 0;
            background: var(--light-bg);
        }

        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .contact-info-box {
            background: var(--white);
            padding: 50px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .contact-form-box {
            background: var(--white);
            padding: 50px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .contact-info-box h2,
        .contact-form-box h2 {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin-bottom: 30px;
            font-weight: 600;
        }

        .contact-items {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .contact-item-large {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            padding: 20px;
            background: var(--light-bg);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .contact-item-large:hover {
            background: #ebebf0;
            transform: translateX(5px);
        }

        .contact-icon-large {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .contact-item-large h3 {
            margin: 0 0 8px 0;
            color: var(--text-dark);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .contact-item-large p {
            margin: 0;
            color: var(--text-light);
            line-height: 1.6;
        }

        .contact-item-large b {
            display: block;
            color: var(--text-dark);
            margin: 10px 0 0 0;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .social-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--border-color);
        }

        .social-section h3 {
            margin: 0 0 20px 0;
            color: var(--text-dark);
            font-weight: 600;
        }

        .social-links-large {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .social-links-large a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            background: var(--light-bg);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .social-links-large a:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .social-links-large i {
            font-size: 1rem;
        }

        .form-contact {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group label {
            margin-bottom: 0.8rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            padding: 1.1rem 1.3rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: var(--white);
            color: var(--text-dark);
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #bbb;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ff6b35' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.3em;
            padding-right: 2.5rem;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            background-color: #fafafa;
        }

        .form-group small {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .checkbox-group {
            flex-direction: row;
            align-items: flex-start;
        }

        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .checkbox-label input[type="checkbox"] {
            margin-top: 4px;
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .full-width {
            width: 100%;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            padding: 1.1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            flex: 1;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-secondary {
            background: var(--light-bg);
            color: var(--text-dark);
            border: 2px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #e8ecf1;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .form-message {
            padding: 20px 25px;
            border-radius: 12px;
            display: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-message.success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 5px solid #28a745;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1);
            display: flex;
        }

        .form-message.error,
        .form-message.spam {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 5px solid #dc3545;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.1);
            display: flex;
        }

        .message-icon {
            font-size: 1.5rem;
        }

        .message-text {
            font-size: 1.05rem;
            line-height: 1.4;
        }

        .map-section {
            padding: 80px 0;
            background: #fff;
        }

        .map-container {
            box-shadow: var(--shadow);
            border-radius: 15px;
            overflow: hidden;
        }

        .selected-services {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .service-chip {
            background: var(--primary-color);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            animation: slideDown 0.3s ease-out;
        }

        .service-chip button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            display: flex;
            align-items: center;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .service-chip button:hover {
            opacity: 1;
        }

        /* Zakładki */
        .tab-content {
            display: none;
            width: 100%;
            max-width: 100%;
            overflow: visible;
        }

        .tab-content.active {
            display: block;
            width: 100%;
            max-width: 100%;
        }

        .service-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
            width: 100%;
        }

        .select-row {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            width: 100%;
            align-items: stretch;
        }

        .select-row select {
            flex: 1 1 auto;
            min-width: 0;
            width: 100%;
        }

        .select-row button {
            flex: 0 0 auto;
            padding: 1.1rem 1.5rem;
            white-space: nowrap;
        }

        .tab-btn {
            flex: 1;
            padding: 12px 16px;
            background: var(--light-bg);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tab-btn:hover {
            background: #e8ecf1;
            border-color: var(--primary-color);
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .tab-content {
            display: none;
            width: 100%;
            min-width: 100%;
        }

        .tab-content.active {
            display: block;
            width: 100%;
        }

        .service-selector {
            flex: 1;
        }

        @media (max-width: 968px) {
            .contact-wrapper {
                grid-template-columns: 1fr;
            }

            .social-links-large {
                flex-wrap: wrap;
            }
        }
    </style>

<body>
    <?php include 'includes/nav.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>Kontakt</h1>
            <p>Skontaktuj się z nami - chętnie odpowiemy na wszystkie pytania i przyjmiemy wszystkie zgłoszenia!</p>
        </div>
    </section>

    <section class="contact-main">
        <div class="container">
            <div class="contact-wrapper">
                <div class="contact-info-box">
                    <h2>Dane Kontaktowe</h2>
                    <div class="contact-items">
                        <div class="contact-item-large">
                            <div class="contact-icon-large">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h3>Adres</h3>
                                <p>ul. Nadrzeczna 3b<br>42-270 Karczewice<br>Polska</p>
                            </div>
                        </div>
                        <div class="contact-item-large">
                            <div class="contact-icon-large">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h3>Telefon</h3>
                                <p>+48 662 993 490 / 536 200 332</p>
                            </div>
                        </div>
                        <div class="contact-item-large">
                            <div class="contact-icon-large">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h3>Email</h3>
                                <p>SerwisBiuroKarczewice@gmail.com</p>
                            </div>
                        </div>
                        <div class="contact-item-large">
                            <div class="contact-icon-large">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h3>Godziny Otwarcia</h3>
                                <p>Poniedziałek - Piątek: 16:00 - 20:00<br>
                                    Sobota: 9:00 - 20:00<br>
                                    Niedziela: 9:00 - 20:00</p>
                            </div>
                        </div>
                        <b>*Po odczytaniu Twojej wiadomości oddzwonimy do Ciebie w ciągu 24h!
                            Jeśli nieodbierzesz, wyślemy sms-a.
                            <br />
                            *Jeśli podasz swój email, wyślemy Ci niezłocznie proponowane rozwiązanie problemu oraz
                            szacowaną wycenę!
                        </b>
                    </div>

                    <div class="social-section">
                        <h3>Znajdź Nas</h3>
                        <div class="social-links-large">
                            <a href="https://www.facebook.com/groups/905215172192288" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                                <span>Facebook</span>
                            </a>
                            <a href="https://www.olx.pl/oferty/uzytkownik/2MB74f/?my_ads=0" target="_blank" title="OLX">
                                <i class="fas fa-shopping-bag"></i>
                                <span>OLX</span>
                            </a>

                        </div>
                    </div>
                </div>

                <div class="contact-form-box">
                    <h2>Wyślij Wiadomość</h2>
                    <form class="form-contact" id="contactForm" method="POST" action="send_message.php">
                        <!-- Dodatkowe zabezpieczenie przeciw botom (honeypot) -->
                        <div style="display:none !important;">
                            <input type="text" name="website_url" autocomplete="off" tabindex="-1">
                        </div>

                        <?php if (isset($_SESSION['form_status'])): ?>
                            <div class="form-message <?php echo $_SESSION['form_status']; ?>" id="formResponse"
                                style="display: block; margin-bottom: 25px;">
                                <div class="message-icon">
                                    <?php if ($_SESSION['form_status'] === 'success'): ?>
                                        <i class="fas fa-check-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="message-text">
                                    <?php
                                    echo $_SESSION['form_message'];
                                    unset($_SESSION['form_status']);
                                    unset($_SESSION['form_message']);
                                    ?>
                                </div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const response = document.getElementById('formResponse');
                                    if (response) {
                                        response.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                });
                            </script>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="name">Imię i Nazwisko *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Adres Zamieszkania</label>
                            <input type="text" id="address" name="address"
                                placeholder="np. ul. Przykładowa 123, 00-001 Twoja miejscowość">
                        </div>
                        <div class="form-group">
                            <label for="phone">Numer Telefonu *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email (opcjonalnie)</label>
                            <input type="email" id="email" name="email" placeholder="Twój adres email">
                        </div>
                        <div class="form-group">
                            <label for="subject">Temat Wiadomości *</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Treść Wiadomości *</label>
                            <textarea id="message" name="message" rows="6" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="service">Interesujące Cię Usługi/Produkty</label>

                            <!-- Zakładki -->
                            <div class="service-tabs">
                                <button type="button" class="tab-btn active" onclick="switchTab('single')">
                                    <i class="fas fa-wrench"></i> Usługi Pojedyncze
                                </button>
                                <button type="button" class="tab-btn" onclick="switchTab('package')">
                                    <i class="fas fa-box"></i> Pakiety
                                </button>
                                <button type="button" class="tab-btn" onclick="switchTab('product')">
                                    <i class="fas fa-shopping-cart"></i> Produkty
                                </button>
                            </div>

                            <!-- Sekcja Usług Pojedynczych -->
                            <div class="tab-content active" id="tab-single">
                                <div class="select-row">
                                    <select id="select-single">
                                        <option value="">-- Wybierz usługę --</option>
                                        <?php foreach ($services as $s): ?>
                                            <?php if (isset($s['category']) && $s['category'] == 'single'): ?>
                                                <option value="service_<?php echo $s['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($s['name']); ?>">
                                                    <?php echo htmlspecialchars($s['name']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-secondary" onclick="addItem('single')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Sekcja Pakietów -->
                            <div class="tab-content" id="tab-package">
                                <div class="select-row">
                                    <select id="select-package">
                                        <option value="">-- Wybierz pakiet --</option>
                                        <?php foreach ($services as $s): ?>
                                            <?php if (isset($s['category']) && $s['category'] == 'package'): ?>
                                                <option value="service_<?php echo $s['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($s['name']); ?>">
                                                    <?php echo htmlspecialchars($s['name']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-secondary" onclick="addItem('package')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Sekcja Produktów -->
                            <div class="tab-content" id="tab-product">
                                <div class="select-row">
                                    <select id="select-product">
                                        <option value="">-- Wybierz produkt --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="product_<?php echo $p['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($p['name']); ?>">
                                                <?php echo htmlspecialchars($p['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-secondary" onclick="addItem('product')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="selectedServices" class="selected-services"></div>
                            <input type="hidden" id="selectedServicesHidden" name="selected_services" value="">
                        </div>
                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="privacy" name="privacy" required>
                                <span>Akceptuję politykę prywatności i wyrażam zgodę na przetwarzanie moich danych
                                    osobowych *</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary full-width">
                            <i class="fas fa-paper-plane"></i>
                            Wyślij Wiadomość
                        </button>
                        <div id="formMessage" class="form-message"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">
        <div class="container">
            <h2 class="section-title">Nasza Lokalizacja</h2>
            <div class="map-container">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2516.702270546602!2d19.43112187715532!3d50.892220571679985!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47174e03808e791f%3A0x256a83367f852636!2sNadrzeczna%203B%2C%2042-270%20Karczewice!5e0!3m2!1spl!2spl!4v1768516365613!5m2!1spl!2spl"
                    width="100%" height="450" style="border:0; border-radius: 15px;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>

    <script>
        // Przełączanie zakładek
        function switchTab(tabName) {
            // Usuń active z przycisków
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            // Usuń active z contentu
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Dodaj active
            event.target.closest('.tab-btn').classList.add('active');
            document.getElementById('tab-' + tabName).classList.add('active');
        }

        // Przechowywanie wybranych elementów
        let selectedItems = {};

        // Dodawanie elementu
        function addItem(type) {
            const select = document.getElementById('select-' + type);
            if (!select.value) return;

            const value = select.value;
            const name = select.options[select.selectedIndex].getAttribute('data-name');

            if (selectedItems[value]) {
                alert('Ten element jest już dodany!');
                return;
            }

            selectedItems[value] = name;
            updateChips();
            select.value = '';
        }

        // Aktualizacja chipsów
        function updateChips() {
            const container = document.getElementById('selectedServices');
            const hidden = document.getElementById('selectedServicesHidden');

            container.innerHTML = '';

            Object.keys(selectedItems).forEach(key => {
                const chip = document.createElement('div');
                chip.className = 'service-chip';

                let icon = 'fa-wrench';
                if (key.startsWith('product_')) icon = 'fa-shopping-cart';
                else if (key.includes('package')) icon = 'fa-box';

                chip.innerHTML = `
                    <i class="fas ${icon}"></i>
                    ${selectedItems[key]}
                    <button type="button" onclick="removeItem('${key}')">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(chip);
            });

            hidden.value = Object.keys(selectedItems).join(',');
        }

        // Usuwanie elementu
        function removeItem(key) {
            delete selectedItems[key];
            updateChips();
        }
    </script>

</body>

</html>