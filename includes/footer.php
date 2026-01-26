<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-col">
                <h4>Serwis komputerowy - Karczewice</h4>
                <p>Zapraszamy do odwiedzenia nas na innych portalach internetowych!</p>
                <div class="social-links">
                    <a href="https://www.facebook.com/groups/905215172192288" target="_blank" aria-label="Facebook"><i
                            class="fab fa-facebook-f"></i></a>
                    <a href="https://www.olx.pl/oferty/uzytkownik/2MB74f/?my_ads=0" target="_blank" aria-label="OLX"><i
                            class="fas fa-shopping-bag"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Menu</h4>
                <ul>
                    <li><a href="index.php">Strona Główna</a></li>
                    <li><a href="oferta.php">Oferta</a></li>
                    <li><a href="produkty.php">Produkty</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Kontakt</h4>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i> ul. Nadrzeczna 3b, 42-270 Karczewice</li>
                    <li><i class="fas fa-phone"></i> +48 662 993 490 / 536 200 332</li>
                    <li><i class="fas fa-envelope"></i> SerwisBiuroKarczewice@gmail.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>Dawid Lechnaty & Norbert Wiewórowski</p>
        </div>
    </div>
</footer>

<style>
    .footer {
        background: #2c3e50;
        color: #ecf0f1;
        padding: 60px 0 20px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-col h4 {
        font-size: 1.3rem;
        margin-bottom: 20px;
        color: #fff;
    }

    .footer-col p {
        line-height: 1.8;
        margin-bottom: 20px;
        opacity: 0.9;
    }

    .footer-col ul {
        list-style: none;
    }

    .footer-col ul li {
        margin-bottom: 12px;
    }

    .footer-col ul li a {
        color: #ecf0f1;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-col ul li a:hover {
        color: #ff6b35;
    }

    .footer-contact li {
        display: flex;
        align-items: start;
        gap: 10px;
        margin-bottom: 15px;
    }

    .footer-contact i {
        color: #ff6b35;
        margin-top: 3px;
    }

    .social-links {
        display: flex;
        gap: 15px;
    }

    .social-links a {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        text-decoration: none;
        transition: transform 0.3s ease;
    }

    .social-links a:hover {
        transform: translateY(-5px);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        opacity: 0.8;
    }

    @media (max-width: 768px) {
        .footer {
            padding: 40px 0 20px;
        }

        .footer-content {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .footer-col h4 {
            font-size: 1.2rem;
        }

        .social-links {
            justify-content: center;
        }
    }
</style>