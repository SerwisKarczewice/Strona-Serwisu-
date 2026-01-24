<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Skontaktuj się z naszym serwisem komputerowym. Zadzwoń, napisz lub odwiedź nas osobiście. Jesteśmy do Twojej dyspozycji!">
    <meta name="keywords" content="kontakt serwis komputerowy, adres serwisu IT, telefon serwis komputerowy">
    <title>Kontakt - Serwis Komputerowy | TechService</title>
    <link rel="canonical" href="https://twojadomena.pl/kontakt.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

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
                            <div class="form-message <?php echo $_SESSION['form_status']; ?>" id="formResponse" style="display: block; margin-bottom: 25px;">
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
                                document.addEventListener('DOMContentLoaded', function() {
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

</body>

</html>

<style>
    .contact-main {
        padding: 80px 0;
        background: var(--light-color);
    }

    .contact-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .contact-info-box,
    .contact-form-box {
        background: #fff;
        padding: 40px;
        border-radius: 20px;
        box-shadow: var(--shadow);
    }

    .contact-info-box h2,
    .contact-form-box h2 {
        font-size: 2rem;
        color: var(--dark-color);
        margin-bottom: 30px;
    }

    .contact-items {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .contact-item-large {
        display: flex;
        gap: 20px;
        align-items: start;
        padding: 20px;
        background: var(--light-color);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .contact-item-large:hover {
        transform: translateX(10px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .contact-icon-large {
        width: 60px;
        height: 60px;
        background: var(--gradient-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .contact-icon-large i {
        font-size: 1.8rem;
        color: #fff;
    }

    .contact-item-large h3 {
        font-size: 1.3rem;
        color: var(--dark-color);
        margin-bottom: 8px;
    }

    .contact-item-large p {
        color: var(--text-light);
        line-height: 1.8;
        font-size: 1.05rem;
    }

    .contact-item-large a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .contact-item-large a:hover {
        color: var(--secondary-color);
    }

    .social-section {
        margin-top: 40px;
        padding-top: 30px;
        border-top: 2px solid var(--light-color);
    }

    .social-section h3 {
        font-size: 1.5rem;
        color: var(--dark-color);
        margin-bottom: 20px;
    }

    .social-links-large {
        display: flex;
        gap: 15px;
    }

    .social-links-large a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        background: var(--gradient-primary);
        color: #fff;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .social-links-large a:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
    }

    .social-links-large i {
        font-size: 1.2rem;
    }

    .form-contact {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark-color);
        font-size: 1.05rem;
    }

    .form-group input,
    .form-group textarea {
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .checkbox-group {
        flex-direction: row;
        align-items: start;
    }

    .checkbox-label {
        display: flex;
        align-items: start;
        gap: 10px;
        cursor: pointer;
        font-size: 0.95rem;
    }

    .checkbox-label input[type="checkbox"] {
        margin-top: 3px;
        cursor: pointer;
        width: auto;
    }

    .full-width {
        width: 100%;
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

    @media (max-width: 968px) {
        .contact-wrapper {
            grid-template-columns: 1fr;
        }

        .social-links-large {
            flex-wrap: wrap;
        }
    }
</style>