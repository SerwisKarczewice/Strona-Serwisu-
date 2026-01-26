<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">
            <img src="uploads/icons/logo.svg" alt="Serwis Komputerowy Karczewice">
            <span class="logo-text">Serwis Komputerowy<br><small>Karczewice</small></span>
        </a>
        <ul class="nav-menu" id="navMenu">
            <li><a href="index.php">Start</a></li>
            <li><a href="oferta.php">Oferta</a></li>
            <li><a href="produkty.php">Produkty</a></li>
            <li><a href="galeria.php">Galeria</a></li>
            <li><a href="kontakt.php">Kontakt</a></li>
        </ul>
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

<style>
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .navbar .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        padding: 5px 0;
    }

    .logo img {
        height: 50px;
        width: auto;
        transition: transform 0.3s ease;
    }

    .logo-text {
        font-family: 'Poppins', sans-serif;
        color: #2c3e50;
        font-weight: 700;
        font-size: 1.1rem;
        line-height: 1.2;
        display: flex;
        flex-direction: column;
    }

    .logo-text small {
        color: #ff6b35;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .logo:hover img {
        transform: scale(1.05);
        /* Subtle zoom on hover */
    }

    .nav-menu {
        display: flex;
        list-style: none;
        gap: 30px;
    }

    .nav-menu a {
        text-decoration: none;
        color: #333;
        font-weight: 500;
        transition: color 0.3s ease;
        position: relative;
    }

    .nav-menu a::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 0;
        height: 3px;
        background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
        transition: width 0.3s ease;
    }

    .nav-menu a:hover::after {
        width: 100%;
    }

    .nav-menu a:hover {
        color: #ff6b35;
    }

    .hamburger {
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 5px;
    }

    .hamburger span {
        width: 25px;
        height: 3px;
        background: #ff6b35;
        transition: all 0.3s ease;
    }

    @media (max-width: 768px) {
        .hamburger {
            display: flex;
        }

        .nav-menu {
            position: fixed;
            left: -100%;
            top: 70px;
            flex-direction: column;
            background: #fff;
            width: 100%;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
            padding: 20px 0;
        }

        .nav-menu.active {
            left: 0;
        }
    }
</style>