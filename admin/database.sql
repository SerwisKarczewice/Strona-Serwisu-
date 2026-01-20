-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 16, 2026 at 05:28 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `serwis_komputerowy`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `created_at`, `last_login`) VALUES
(2, 'Administrator', '$2y$10$VAjbyrBn/TT1dSFvYpFOcelOtx5bn1Scd350peTu1sTE215.sRAZ2', 'admin@serwis.pl', '2026-01-10 01:12:53', '2026-01-16 15:31:30'),
(3, 'admin', '$2y$10$YourHashWillBeHere', 'admin@serwis.pl', '2026-01-11 12:32:38', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `status` enum('nowa','przeczytana','odpowiedziana') DEFAULT 'nowa',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_type` enum('paragon','faktura') DEFAULT 'paragon',
  `client_id` int(11) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `client_address` text DEFAULT NULL,
  `client_nip` varchar(20) DEFAULT NULL,
  `client_company` varchar(255) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('gotówka','karta','przelew','blik') DEFAULT 'gotówka',
  `payment_status` enum('nieopłacona','opłacona','częściowo') DEFAULT 'nieopłacona',
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `invoice_type`, `client_id`, `client_name`, `client_email`, `client_phone`, `client_address`, `client_nip`, `client_company`, `subtotal`, `tax`, `total`, `payment_method`, `payment_status`, `notes`, `created_at`, `created_by`) VALUES
(6, 'PAR/2026/01/0001', 'paragon', NULL, 'przyklad', 'aaa@gmail.com', '667767', '434', '', '', 28.46, 6.54, 35.00, 'gotówka', 'opłacona', '', '2026-01-16 15:33:10', 2),
(7, 'FV/2026/01/0001', 'faktura', NULL, 'przyklad', 'aaa@gmail.com', '667767', '434', '', '', 28.46, 6.54, 35.00, 'gotówka', 'opłacona', '', '2026-01-16 17:14:20', 2);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_type` enum('usługa','produkt','część','inne') DEFAULT 'usługa',
  `name` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 23.00,
  `total` decimal(10,2) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item_type`, `name`, `description`, `quantity`, `unit_price`, `tax_rate`, `total`, `service_id`, `product_id`) VALUES
(8, 6, 'usługa', 'Czyszczenie komputera', NULL, 1.00, 35.00, 23.00, 35.00, 9, NULL),
(9, 7, 'usługa', 'Czyszczenie komputera', NULL, 1.00, 35.00, 23.00, 35.00, 9, NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `slug` varchar(500) NOT NULL,
  `published` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image_path` varchar(500) DEFAULT NULL,
  `olx_link` varchar(500) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `category` enum('single','package') DEFAULT 'single',
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `discount_price`, `category`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Składanie zestawu komputerowego', 'Profesjonalne złożenie komputera z testowaniem', 120.00, NULL, 'single', 1, 1, '2026-01-10 01:45:37', '2026-01-10 11:48:21'),
(2, 'Instalacja systemu operacyjnego', 'Windows 10 lub 11, pełna aktywacja', 40.00, NULL, 'single', 1, 2, '2026-01-10 01:45:37', NULL),
(3, 'Przygotowanie stanowiska komputerowego', 'Konfiguracja sprzętu i podłączenie urządzeń', 40.00, NULL, 'single', 1, 3, '2026-01-10 01:45:37', NULL),
(4, 'Optymalizacja BIOS', 'Konfiguracja ustawień, aktualizacja', 20.00, NULL, 'single', 1, 4, '2026-01-10 01:45:37', NULL),
(5, 'Instalacja sterowników', 'Wszystkie sterowniki, najnowsze wersje', 15.00, NULL, 'single', 1, 5, '2026-01-10 01:45:37', NULL),
(6, 'Instalacja programów', 'Wybrane programy, legalne oprogramowanie', 10.00, NULL, 'single', 1, 6, '2026-01-10 01:45:37', NULL),
(7, 'Formatowanie dysku', 'Bezpieczne usunięcie danych', 10.00, NULL, 'single', 1, 7, '2026-01-10 01:45:37', NULL),
(8, 'Partycjonowanie dysku', 'Podział na partycje, optymalna organizacja', 10.00, NULL, 'single', 1, 8, '2026-01-10 01:45:37', NULL),
(9, 'Czyszczenie komputera', 'Czyszczenie mechaniczne i systemowe', 35.00, NULL, 'single', 1, 9, '2026-01-10 01:45:37', NULL),
(10, 'Wymiana pasty termoprzewodzącej', 'Profesjonalna pasta, czyszczenie układu', 40.00, NULL, 'single', 1, 10, '2026-01-10 01:45:37', NULL),
(11, 'Naprawa drobnych usterek', 'Diagnoza i naprawa', 20.00, NULL, 'single', 1, 11, '2026-01-10 01:45:37', NULL),
(12, 'Wymiana końcówek RJ-45', 'Profesjonalny zacisk, test połączenia', 20.00, NULL, 'single', 1, 12, '2026-01-10 01:45:37', NULL),
(13, 'Modernizacja urządzenia', 'Wymiana/dodanie podzespołów (cena za 1 usługę)', 15.00, NULL, 'single', 1, 13, '2026-01-10 01:45:37', NULL),
(14, 'Usuwanie wirusów', 'Skanowanie i usunięcie zagrożeń', 15.00, NULL, 'single', 1, 14, '2026-01-10 01:45:37', NULL),
(15, 'Pakiet Podstawowy', 'Składanie PC + Windows 11 + Przygotowanie stanowiska + Instalacja programów', 190.00, NULL, 'package', 1, 1, '2026-01-10 01:45:37', NULL),
(16, 'Pakiet Rozszerzony', 'Podstawowy + Optymalizacja BIOS + Partycjonowanie + Sterowniki', 220.00, NULL, 'package', 1, 2, '2026-01-10 01:45:37', NULL),
(17, 'Pakiet Odświeżenie i Konserwacja', 'Czyszczenie + Wymiana pasty + Optymalizacja + Usuwanie wirusów', 85.00, NULL, 'package', 1, 3, '2026-01-10 01:45:37', NULL),
(18, 'Pakiet Drugie Życie Komputera', 'Modernizacja + Formatowanie + Reinstalacja + Sterowniki i programy', 100.00, NULL, 'package', 1, 4, '2026-01-10 01:45:37', NULL),
(19, 'Pakiet Przenosiny', 'Kopia zapasowa + Reinstalacja systemu + Instalacja sterowników', 70.00, NULL, 'package', 1, 5, '2026-01-10 01:45:37', NULL);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indeksy dla tabeli `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indeksy dla tabeli `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeksy dla tabeli `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indeksy dla tabeli `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_invoice_number` (`invoice_number`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_client` (`client_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeksy dla tabeli `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeksy dla tabeli `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_published` (`published`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `author_id` (`author_id`);

--
-- Indeksy dla tabeli `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_stock` (`stock`);

--
-- Indeksy dla tabeli `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_order` (`display_order`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `financial_contributions`
--

CREATE TABLE `financial_contributions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `team_member_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `contributed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `product_sales`
--

CREATE TABLE `product_sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_cost` decimal(10,2) NOT NULL,
  `profit` decimal(10,2) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `sold_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `profit_distributions`
--

CREATE TABLE `profit_distributions` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `team_member_id` int(11) NOT NULL,
  `contribution_percentage` decimal(5,2) NOT NULL,
  `profit_share` decimal(10,2) NOT NULL,
  `distributed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `service_executions`
--

CREATE TABLE `service_executions` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `service_price` decimal(10,2) NOT NULL,
  `executed_at` datetime NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `service_team`
--

CREATE TABLE `service_team` (
  `id` int(11) NOT NULL,
  `execution_id` int(11) NOT NULL,
  `team_member_id` int(11) NOT NULL,
  `payment_share` decimal(10,2) NOT NULL,
  `assigned_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Indeksy dla tabeli `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeksy dla tabeli `financial_contributions`
--
ALTER TABLE `financial_contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_member` (`team_member_id`),
  ADD KEY `idx_contributed_at` (`contributed_at`);

--
-- Indeksy dla tabeli `product_sales`
--
ALTER TABLE `product_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_sold_at` (`sold_at`);

--
-- Indeksy dla tabeli `profit_distributions`
--
ALTER TABLE `profit_distributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sale` (`sale_id`),
  ADD KEY `idx_member` (`team_member_id`),
  ADD KEY `idx_distributed_at` (`distributed_at`);

--
-- Indeksy dla tabeli `service_executions`
--
ALTER TABLE `service_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_executed_at` (`executed_at`);

--
-- Indeksy dla tabeli `service_team`
--
ALTER TABLE `service_team`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_execution` (`execution_id`),
  ADD KEY `idx_member` (`team_member_id`);

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `financial_contributions`
--
ALTER TABLE `financial_contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `product_sales`
--
ALTER TABLE `product_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `profit_distributions`
--
ALTER TABLE `profit_distributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `service_executions`
--
ALTER TABLE `service_executions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `service_team`
--
ALTER TABLE `service_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for table `financial_contributions`
--
ALTER TABLE `financial_contributions`
  ADD CONSTRAINT `fc_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fc_member_fk` FOREIGN KEY (`team_member_id`) REFERENCES `team_members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sales`
--
ALTER TABLE `product_sales`
  ADD CONSTRAINT `ps_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ps_invoice_fk` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `profit_distributions`
--
ALTER TABLE `profit_distributions`
  ADD CONSTRAINT `pd_sale_fk` FOREIGN KEY (`sale_id`) REFERENCES `product_sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pd_member_fk` FOREIGN KEY (`team_member_id`) REFERENCES `team_members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_executions`
--
ALTER TABLE `service_executions`
  ADD CONSTRAINT `se_service_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `se_invoice_fk` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_team`
--
ALTER TABLE `service_team`
  ADD CONSTRAINT `st_execution_fk` FOREIGN KEY (`execution_id`) REFERENCES `service_executions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `st_member_fk` FOREIGN KEY (`team_member_id`) REFERENCES `team_members` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
