CREATE DATABASE IF NOT EXISTS serwis_komputerowy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE serwis_komputerowy;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address VARCHAR(500) NULL,
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('nowa', 'przeczytana', 'odpowiedziana') DEFAULT 'nowa',
    created_at DATETIME NOT NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    last_login DATETIME NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    content TEXT NOT NULL,
    excerpt VARCHAR(500) NULL,
    slug VARCHAR(500) NOT NULL UNIQUE,
    published BOOLEAN DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    author_id INT NULL,
    views INT DEFAULT 0,
    INDEX idx_published (published),
    INDEX idx_slug (slug),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image_path VARCHAR(500) NOT NULL,
    category VARCHAR(100) NULL,
    uploaded_at DATETIME NOT NULL,
    display_order INT DEFAULT 0,
    INDEX idx_category (category),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) NULL,
    stock INT DEFAULT 0,
    image_path VARCHAR(500) NULL,
    featured BOOLEAN DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    INDEX idx_category (category),
    INDEX idx_featured (featured),
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuń istniejącego użytkownika admin jeśli istnieje
DELETE FROM admin_users WHERE username = 'admin';

-- Dodaj użytkownika admin z prostym hasłem "admin123"
-- Hash dla hasła "admin123"
INSERT INTO admin_users (username, password, email, created_at) 
VALUES ('admin', '$2y$10$YourHashWillBeHere', 'admin@serwis.pl', NOW());

-- UWAGA: Poniżej są 3 sposoby na utworzenie konta admina
-- Użyj JEDNEJ z poniższych metod:

-- METODA 1: Prosty INSERT z hasłem "admin123" (już zahashowane)
UPDATE admin_users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';

INSERT INTO news (title, content, excerpt, slug, published, created_at, author_id, views) VALUES
('Promocja Noworoczna!', 'Z okazji Nowego Roku oferujemy 20% zniżki na czyszczenie laptopów i wymianę past termoprzewodzących. Oferta ważna do końca stycznia! Zapraszamy wszystkich klientów do skorzystania z promocji.', 'Z okazji Nowego Roku oferujemy 20% zniżki na czyszczenie laptopów i wymianę past termoprzewodzących.', 'promocja-noworoczna', 1, '2026-01-05 10:00:00', 1, 0),
('Nowe Podzespoły w Ofercie', 'Rozszerzyliśmy naszą ofertę o najnowsze karty graficzne NVIDIA RTX 4000 oraz procesory Intel Core Ultra. W naszym sklepie znajdziecie państwo najnowsze komponenty w atrakcyjnych cenach. Zapraszamy!', 'Rozszerzyliśmy naszą ofertę o najnowsze karty graficzne NVIDIA RTX 4000 oraz procesory Intel Core Ultra.', 'nowe-podzespoly-w-ofercie', 1, '2025-12-28 14:30:00', 1, 0),
('Godziny Otwarcia w Święta', 'Informujemy, że w okresie świątecznym nasz serwis będzie pracował w zmienionych godzinach. Szczegóły w zakładce kontakt. Dziękujemy za zrozumienie i życzymy wesołych świąt!', 'Informujemy, że w okresie świątecznym nasz serwis będzie pracował w zmienionych godzinach.', 'godziny-otwarcia-w-swieta', 1, '2025-12-15 09:00:00', 1, 0);