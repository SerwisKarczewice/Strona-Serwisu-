<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Poznaj nasz serwis komputerowy - doświadczenie, profesjonalizm i uczciwe ceny. Sprawdź dlaczego warto nam zaufać!">
    <meta name="keywords" content="o nas serwis komputerowy, profesjonalny serwis IT, zaufany serwis komputerowy">
    <title>O Nas - Profesjonalny Serwis Komputerowy | TechService</title>
    <link rel="canonical" href="https://twojadomena.pl/o-nas.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="O Nas - Profesjonalny Serwis Komputerowy | TechService">
    <meta property="og:description"
        content="Poznaj nasz zespół i naszą misję. TechService to profesjonalny serwis komputerowy z wieloletnim doświadczeniem. Sprawdź, dlaczego warto nam zaufać!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://twojadomena.pl/o-nas.php">
    <meta property="og:image" content="https://twojadomena.pl/images/og-image.jpg">
    <!-- Upewnij się, że masz taki obrazek -->
    <meta property="og:locale" content="pl_PL">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "TechService - Serwis Komputerowy",
      "image": "https://twojadomena.pl/images/logo.png",
      "url": "https://twojadomena.pl/o-nas.php",
      "telephone": "+48 123 456 789",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "ul. Przykładowa 123",
        "addressLocality": "Warszawa",
        "postalCode": "00-001",
        "addressCountry": "PL"
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
        "https://olx.pl/oferty/uzytkownik/TwojID"
      ]
    }
    </script>
</head>

<body>
    <?php include 'includes/nav.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>O Nas</h1>
            <p>Poznaj zespół profesjonalistów, którzy dbają o Twój sprzęt</p>
        </div>
    </section>

    <section class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Kim Jesteśmy?</h2>
                    <p>Jesteśmy zespołem pasjonatów z Karczewic, dla których technologia nie ma tajemnic. Łączymy
                        młodzieńczą energię z profesjonalnym podejściem, oferując usługi na najwyższym poziomie, ale w
                        sąsiedzkiej atmosferze.</p>
                    <p>Naszą misją jest pomoc w świecie IT – bez skomplikowanego żargonu i naciągania. Chcemy, abyś czuł
                        się u nas bezpiecznie, wiedząc, że Twój sprzęt jest w dobrych rękach. Niezależnie czy masz 15
                        czy 75 lat, wytłumaczymy usterkę w prosty sposób i naprawimy ją skutecznie.</p>
                </div>
                <div class="about-image">
                    <i class="fas fa-laptop-code"></i>
                </div>
            </div>
        </div>
    </section>

    <section class="values-section">
        <div class="container">
            <h2 class="section-title">Nasze Wartości</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Profesjonalizm</h3>
                    <p>Każde zlecenie traktujemy z najwyższą starannością. Nasi specjaliści posiadają wieloletnie
                        doświadczenie i ciągle podnoszą swoje kwalifikacje.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Pasja</h3>
                    <p>Kochamy to, co robimy! Technologia to nasza pasja, dlatego każdy projekt realizujemy z pełnym
                        zaangażowaniem i entuzjazmem.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Uczciwość</h3>
                    <p>Transparentne ceny, brak ukrytych kosztów. Zawsze informujemy klienta o kosztach przed
                        rozpoczęciem prac.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Szybkość</h3>
                    <p>Rozumiemy, jak ważny jest Twój czas. Dlatego większość napraw realizujemy ekspresowo, bez
                        kompromisów w jakości.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Nasz Zespół</h2>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-photo">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="team-info">
                        <h3>Dawid Lechnaty</h3>
                        <p class="team-role">Technologia i Rozwój WWW</p>
                        <p class="team-description">Odpowiada za zaplecze techniczne serwisu oraz rozwój naszej strony
                            internetowej. To dzięki niemu możecie korzystać z wygodnych rozwiązań online i nowoczesnych
                            technologii.</p>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-photo">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="team-info">
                        <h3>Norbert Wiewiórowski</h3>
                        <p class="team-role">Główny Serwisant i Social Media</p>
                        <p class="team-description">Serce naszego warsztatu. Zajmuje się głównymi naprawami i
                            profesjonalnym składaniem komputerów. Prowadzi również nasze profile na Facebooku i OLX,
                            dbając o świetny kontakt z klientami.</p>
                    </div>
                </div>
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


    <section class="social-section">
        <div class="container">
            <h2 class="section-title">Gdzie Nas Znaleźć?</h2>
            <p class="social-subtitle">Śledź nas w mediach społecznościowych i bądź na bieżąco!</p>
            <div class="social-grid">

                <a href="https://www.facebook.com/groups/905215172192288" target="_blank" class="social-card facebook">
                    <div class="social-icon">
                        <i class="fab fa-facebook"></i>
                    </div>
                    <h3>Facebook Serwisu</h3>
                    <p>Aktualności, promocje i porady techniczne</p>
                </a>
                <a href="https://www.olx.pl/oferty/uzytkownik/2MB74f/?my_ads=0" target="_blank" class="social-card olx">
                    <div class="social-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3>OLX Serwisu</h3>
                    <p>Sprawdź nasze ogłoszenia i oferty specjalne</p>
                </a>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <div class="container">
            <h2 class="section-title" id="dodatkowe-informacje">Dodatkowe informacje</h2>
            <p class="faq-subtitle">Informacje o naszym serwisie i ofercie</p>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Jak długo trwa składanie komputera?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Zależy to od dostępności części, ale staramy się działać ekspresowo! Zazwyczaj, gdy mamy
                            wszystkie podzespoły, Twój wymarzony komputer jest gotowy do odbioru w ciągu 24-48 godzin.
                            Każdy zestaw jest też przez nas testowany pod obciążeniem.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Czy oferujecie gwarancję na usługi?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Tak! Wszystkie nasze naprawy objęte są gwarancją. Na wymienione części udzielamy gwarancji
                            zgodnie z warunkami producenta, a na wykonane usługi - minimum 30 dni gwarancji.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Czy mogę być obecny podczas diagnostyki?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Oczywiście! Zachęcamy klientów do obecności podczas diagnostyki. Możemy wspólnie omówić
                            problem i zaproponować najlepsze rozwiązanie. Jesteśmy transparentni w naszych działaniach.
                        </p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Jakie formy płatności akceptujecie?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Stawiamy na proste i przejrzyste zasady, dlatego obecnie przyjmujemy płatność gotówką przy
                            odbiorze sprzętu. Daje to pewność – płacisz dopiero wtedy, gdy widzisz naprawiony komputer.
                        </p>
                    </div>
                </div>


            </div>
        </div>

    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Gotowy na współpracę?</h2>
                <p>Skontaktuj się z nami już dziś i przekonaj się, jak możemy pomóc!</p>
                <div class="cta-buttons">
                    <a href="oferta.php" class="btn btn-primary">
                        <i class="fas fa-list"></i>
                        Zobacz Ofertę
                    </a>
                    <a href="kontakt.php" class="btn btn-outline">
                        <i class="fas fa-phone"></i>
                        Skontaktuj się
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
    <script>
        // FAQ Accordion functionality
        document.addEventListener('DOMContentLoaded', function () {
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');

                question.addEventListener('click', () => {
                    // Close other items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });

                    // Toggle current item
                    item.classList.toggle('active');
                });
            });
        });
    </script>
</body>

</html>

<style>
    .about-content {
        padding: 80px 0;
        background: #fff;
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .about-text h2 {
        font-size: 2.5rem;
        color: var(--dark-color);
        margin-bottom: 25px;
    }

    .about-text p {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--text-light);
        margin-bottom: 20px;
    }

    .about-image {
        background: var(--gradient-primary);
        border-radius: 20px;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .about-image i {
        font-size: 10rem;
        color: rgba(255, 255, 255, 0.9);
    }

    .values-section {
        padding: 80px 0;
        background: var(--light-color);
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .value-card {
        background: #fff;
        padding: 40px 30px;
        border-radius: 15px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
    }

    .value-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
    }

    .value-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .value-icon i {
        font-size: 2rem;
        color: #fff;
    }

    .value-card h3 {
        font-size: 1.5rem;
        color: var(--dark-color);
        margin-bottom: 15px;
    }

    .value-card p {
        color: var(--text-light);
        line-height: 1.8;
    }

    .team-section {
        padding: 80px 0;
        background: #fff;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 40px;
        max-width: 900px;
        margin: 0 auto;
    }

    .team-card {
        background: var(--light-color);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
    }

    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
    }

    .team-photo {
        width: 150px;
        height: 150px;
        background: var(--gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        transition: all 0.3s ease;
    }

    .team-card:hover .team-photo {
        transform: scale(1.05);
    }

    .team-photo i {
        font-size: 4rem;
        color: #fff;
    }

    .team-info h3 {
        font-size: 1.8rem;
        color: var(--dark-color);
        margin-bottom: 10px;
    }

    .team-role {
        font-size: 1.1rem;
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 15px;
    }

    .team-description {
        font-size: 1rem;
        color: var(--text-light);
        line-height: 1.8;
    }

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

    .social-section {
        padding: 80px 0;
        background: var(--light-color);
    }

    .social-subtitle {
        text-align: center;
        font-size: 1.2rem;
        color: var(--text-light);
        margin-bottom: 50px;
        margin-top: -10px;
    }

    .social-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }

    .social-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
        text-decoration: none;
        color: inherit;
        display: block;
        position: relative;
        overflow: hidden;
    }

    .social-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .social-card:hover::before {
        transform: scaleX(1);
    }

    .social-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-hover);
    }

    .social-card.facebook .social-icon {
        background: linear-gradient(135deg, #1877f2, #0d5dbf);
    }

    .social-card.olx .social-icon {
        background: linear-gradient(135deg, #002f34, #00474f);
    }

    .social-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        transition: all 0.3s ease;
    }

    .social-card:hover .social-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .social-icon i {
        font-size: 3rem;
        color: #fff;
    }

    .social-card h3 {
        font-size: 1.5rem;
        color: var(--dark-color);
        margin-bottom: 15px;
        font-weight: 600;
    }

    .social-card p {
        color: var(--text-light);
        line-height: 1.6;
        font-size: 1rem;
    }

    .faq-section {
        padding: 80px 0;
        background: #fff;
    }

    .faq-subtitle {
        text-align: center;
        font-size: 1.2rem;
        color: var(--text-light);
        margin-bottom: 50px;
        margin-top: -10px;
    }

    .faq-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .faq-item {
        background: var(--light-color);
        border-radius: 15px;
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .faq-item:hover {
        box-shadow: var(--shadow);
    }

    .faq-question {
        padding: 25px 30px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
        user-select: none;
    }

    .faq-question:hover {
        background: rgba(0, 0, 0, 0.02);
    }

    .faq-question h3 {
        font-size: 1.2rem;
        color: var(--dark-color);
        margin: 0;
        font-weight: 600;
    }

    .faq-question i {
        color: var(--primary-color);
        font-size: 1.2rem;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }

    .faq-item.active .faq-question i {
        transform: rotate(180deg);
    }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
        padding: 0 30px;
    }

    .faq-item.active .faq-answer {
        max-height: 500px;
        padding: 0 30px 25px 30px;
    }

    .faq-answer p {
        color: var(--text-light);
        line-height: 1.8;
        margin: 0;
        font-size: 1.05rem;
    }

    .cta-section {
        padding: 100px 0;
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

    .btn-outline {
        background: transparent;
        border: 2px solid #fff;
        color: #fff;
    }

    .btn-outline:hover {
        background: #fff;
        color: var(--primary-color);
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