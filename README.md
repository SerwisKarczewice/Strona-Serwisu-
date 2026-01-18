 🖥️ Serwis Komputerowy - Strona WWW

Profesjonalna strona internetowa dla serwisu komputerowego z pełnym panelem administracyjnym.

---

## 📋 Szybkie Linki

- [Wymagania](#-wymagania)
- [Instalacja](#-instalacja)
- [Struktura plików](#-struktura-plików)
- [Panel Admin](#-panel-administracyjny)
- [TODO](#-todo)

---

## ⚙️ Wymagania

- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Rozszerzenia**: PDO, pdo_mysql, GD, mbstring
- **Przestrzeń**: min. 100MB + miejsce na zdjęcia

---

## � Instalacja

1. **Rozpakuj pliki** do katalogu serwera
2. **Utwórz bazę**: `mysql -u root -p < admin/database.sql`
3. **Edytuj** `config.php` z danymi MySQL
4. **Utwórz foldery**: 
   ```bash
   mkdir -p uploads/gallery uploads/products
   chmod 777 uploads/gallery uploads/products
   ```
5. **Utwórz admina**: Otwórz `http://localhost/create_admin.php` i **usuń plik**
6. **Zaloguj się**: `http://localhost/admin/login.php`

---

## 📁 Struktura Plików

```
📦 Projekt
├── admin/                          # Panel administracyjny
│   ├── css/admin.css
│   ├── includes/sidebar.php
│   ├── index.php                   # Dashboard
│   ├── login.php / logout.php      # Autentykacja
│   ├── messages.php / view_message.php / delete_message.php
│   ├── news.php / add_news.php / edit_news.php / toggle_news.php / delete_news.php
│   ├── gallery.php / add_gallery.php / edit_gallery.php / delete_gallery.php
│   ├── products.php / add_product.php / edit_product.php / delete_product.php / toggle_featured.php
│   ├── services.php / add_service.php / edit_service.php / delete_service.php / toggle_service.php
│   ├── invoices.php / save_invoice.php / view_invoice.php / delete_invoice.php / generate_pdf.php
│   ├── calculator.php / calculator.js
│   ├── mark_answered.php
│   └── database.sql / create_admin.txt
│
├── css/
│   ├── home.css                    # Style strony głównej
│   └── style.css                   # Style podstron
│
├── js/
│   ├── home.js
│   └── main.js
│
├── includes/
│   ├── nav.php                     # Nawigacja
│   └── footer.php                  # Stopka
│
├── uploads/                         # Zdjęcia (tworzone automatycznie)
│   ├── gallery/                    # Zdjęcia galerii
│   └── products/                   # Zdjęcia produktów
│
├── STRONY FRONTEND
├── index.php                        # Strona główna
├── o-nas.php                        # O nas
├── oferta.php                       # Oferta usług
├── produkty.php                     # Katalog produktów
├── galeria.php                      # Galeria zdjęć
├── kontakt.php                      # Kontakt
├── news-detail.php                  # Szczegóły aktualności
│
├── KONFIGURACJA
├── config.php                       # Ustawienia bazy danych
├── send_message.php                 # Obsługa formularza kontaktowego
│
└── README.md                        # Dokumentacja
```

---

## 🔐 Panel Administracyjny

**URL**: `http://localhost/admin/login.php`

| Sekcja | Funkcje |
|--------|---------|
| **Wiadomości** | Odbieranie wiadomości z formularza, statusy, odpowiadanie |
| **Aktualności** | Dodawanie, edycja, publikowanie newsów |
| **Galeria** | Upload zdjęć, kategorie, edycja, usuwanie |
| **Produkty** | Upload zdjęć, ceny, kategorie, bestsellery |
| **Usługi** | Usługi pojedyncze, pakiety, ceny promocyjne |
| **Faktury** | Kalkulator, generowanie PDF |

⚠️ **Bezpieczeństwo**: Zmień hasło administratora po pierwszym logowaniu

---

## ⚙️ Konfiguracja

1. **Email** - edytuj `send_message.php`:
   ```php
   $to = 'twoj@email.pl';
   ```

2. **Dane kontaktowe** - edytuj w `kontakt.php` i `includes/footer.php`

3. **Logo** - zmień w `includes/nav.php`

4. **Kolory** - zmień zmienne CSS w `css/home.css` i `css/style.css`:
   ```css
   --primary-color: #ff6b35;
   --secondary-color: #f7931e;
   --accent-color: #ffc107;
   ```

---

## 📋 TODO


- Kalkulator do wyceny produktów i cala struktura z tym związana