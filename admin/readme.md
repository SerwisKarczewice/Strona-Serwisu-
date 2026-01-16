# 🖥️ Serwis Komputerowy - Strona WWW

Profesjonalna strona internetowa dla serwisu komputerowego z pełnym panelem administracyjnym i systemem zarządzania treścią.

---

## 📋 Spis Treści

- [Opis projektu](#-opis-projektu)
- [Funkcjonalności](#-funkcjonalności)
- [Wymagania](#-wymagania)
- [Instalacja](#-instalacja)
- [Struktura plików](#-struktura-plików)
- [Logowanie do panelu](#-logowanie-do-panelu)
- [Konfiguracja](#-konfiguracja)
- [Personalizacja](#-personalizacja)
- [SEO](#-seo)
- [Rozwiązywanie problemów](#-rozwiązywanie-problemów)
- [TODO](#-todo)

---

## 🎯 Opis Projektu

Kompleksowa strona internetowa stworzona dla serwisów komputerowych, zawierająca:
- 🎨 Nowoczesny, responsywny design z ciepłą kolorystyką (pomarańczowo-żółtą)
- 🛠️ Pełny panel administracyjny do zarządzania treścią
- 📱 W pełni responsywna na wszystkie urządzenia
- ⚡ Szybka i zoptymalizowana pod SEO
- 🔐 Bezpieczny system logowania administratora

---

## ✨ Funkcjonalności

### 🌐 Strona Główna (Frontend)

#### **Strona Główna (`index.php`)**
- Hero section z animowanymi ikonami w tle
- Sekcja "Dlaczego My?" z 4 kluczowymi wartościami
- Automatyczny slider usług pobieranych z bazy danych
- Aktualności (ostatnie 3 wpisy z bazy)
- Formularz kontaktowy z walidacją
- Responsywna nawigacja z hamburger menu

#### **Podstrony**
- **O Nas** (`o-nas.php`)
  - Historia i misja firmy
  - Wartości firmy (4 karty)
  - 5-punktowa strategia rozwoju
  - CTA do kontaktu

- **Oferta** (`oferta.php`)
  - Usługi pojedyncze (grid z cenami)
  - Pakiety usług (3 kolumny, środkowy wyróżniony)
  - Ceny regularne i promocyjne
  - Wszystko pobierane dynamicznie z bazy

- **Produkty** (`produkty.php`)
  - Katalog produktów z obrazkami
  - Filtrowanie po 9 kategoriach
  - Wyświetlanie cen, stanów magazynowych
  - Badge'e: Bestseller, Ostatnie sztuki, Brak
  - Linki do OLX lub przycisk "Zapytaj"
  - **✅ PEŁNE WSPARCIE DLA ZDJĘĆ**

- **Galeria** (`galeria.php`)
  - Siatka zdjęć z 3 kategoriami
  - Filtrowanie (Wszystkie, Zestawy PC, Naprawy, Warsztat)
  - Lightbox z pełnym podglądem
  - **✅ PEŁNE WSPARCIE DLA ZDJĘĆ**

- **Kontakt** (`kontakt.php`)
  - Dane kontaktowe (adres, telefon, email, godziny)
  - Formularz kontaktowy z walidacją
  - Mapa Google Maps
  - Linki do social media

- **Szczegóły Aktualności** (`news-detail.php`)
  - Pełna treść aktualności
  - Data publikacji i licznik wyświetleń
  - Sekcja z powiązanymi aktualnościami

### 🔧 Panel Administracyjny

Dostęp: `/admin/login.php`

#### **Dashboard** (`admin/index.php`)
- 📊 Statystyki w kartach:
  - Nowe wiadomości
  - Liczba aktualności
  - Liczba produktów
  - Liczba zdjęć w galerii
  - Aktywne usługi
- 📋 Tabela z ostatnimi 5 wiadomościami
- 📰 Lista ostatnich 5 aktualności

#### **Wiadomości** (`admin/messages.php`)
- 📧 Lista wszystkich wiadomości z formularza
- 🔍 Podgląd pełnej treści wiadomości
- 🏷️ Statusy: Nowa, Przeczytana, Odpowiedziana
- ✉️ Bezpośrednie linki do odpowiedzi email
- 🗑️ Usuwanie wiadomości

#### **Aktualności** (`admin/news.php`)
- ➕ Dodawanie nowych aktualności
- ✏️ Edycja istniejących
- 👁️ Publikuj/Ukryj (toggle visibility)
- 📊 Licznik wyświetleń
- 🗑️ Usuwanie

#### **Galeria** (`admin/gallery.php`)
- ➕ **Dodawanie zdjęć z uploadem**
- 🖼️ **Pełne wsparcie dla obrazków (JPG, PNG, GIF, WEBP)**
- 📁 Kategorie: Zestawy PC, Naprawy, Warsztat
- 🔢 Kolejność wyświetlania
- ✏️ Edycja (z możliwością zmiany zdjęcia)
- 🗑️ Usuwanie
- 👁️ **Podgląd miniaturek w panelu**
- ⚡ **Automatyczne tworzenie folderów**

#### **Produkty** (`admin/products.php`)
- ➕ **Dodawanie produktów z obrazkami**
- 🖼️ **Pełne wsparcie dla zdjęć produktów**
- 💰 Ceny i stany magazynowe
- 🏷️ 12 kategorii produktów
- ⭐ Wyróżnianie bestsellerów
- 🔗 Linki do aukcji OLX
- ✏️ Edycja (z możliwością zmiany zdjęcia)
- 🗑️ Usuwanie
- 👁️ **Miniaturki w panelu admina**

#### **Usługi** (`admin/services.php`)
- ➕ Dodawanie usług pojedynczych i pakietów
- 💵 Ceny regularne i promocyjne
- 🔢 Kolejność wyświetlania
- ✅ Aktywuj/Dezaktywuj
- 🎯 Filtrowanie: Wszystkie, Pojedyncze, Pakiety
- ✏️ Edycja
- 🗑️ Usuwanie

---

## 💻 Wymagania

### Wymagania Serwerowe
- **PHP**: 7.4 lub nowszy
- **MySQL**: 5.7 lub nowszy (lub MariaDB 10.2+)
- **Serwer**: Apache lub Nginx
- **Rozszerzenia PHP**:
  - PDO
  - pdo_mysql
  - GD lub Imagick (dla przetwarzania obrazów)
  - mbstring
  - fileinfo

### Wymagania Systemowe
- **Przestrzeń dyskowa**: min. 100MB (+ miejsce na zdjęcia)
- **Uprawnienia**: Możliwość tworzenia katalogów i zapisywania plików

---

## 🚀 Instalacja

### Krok 1: Pobierz pliki
```bash
# Rozpakuj wszystkie pliki do katalogu głównego serwera
# Struktura powinna wyglądać tak:
/public_html/
  ├── admin/
  ├── css/
  ├── js/
  ├── includes/
  ├── uploads/        # Ten folder zostanie utworzony automatycznie
  ├── index.php
  ├── config.php
  └── ...
```

### Krok 2: Utwórz bazę danych
```sql
-- Otwórz phpMyAdmin lub MySQL CLI i wykonaj:
-- Plik database.sql zawiera całą strukturę
```

**Lub zaimportuj plik SQL:**
```bash
mysql -u root -p < database.sql
```

### Krok 3: Konfiguracja połączenia z bazą
Edytuj plik `config.php`:
```php
<?php
$host = 'localhost';           // Zwykle localhost
$dbname = 'serwis_komputerowy'; // Nazwa bazy danych
$username = 'root';             // Użytkownik MySQL
$password = '';                 // Hasło MySQL (jeśli jest)
```

### Krok 4: Utwórz foldery na zdjęcia
```bash
# Z terminala (Linux/Mac):
mkdir -p uploads/gallery uploads/products
chmod 777 uploads/gallery uploads/products

# Lub przez FTP - utwórz foldery:
# /uploads/gallery/
# /uploads/products/
# I ustaw uprawnienia 777
```

### Krok 5: Utwórz konto administratora

**Opcja A: Użyj pliku `create_admin.php`**
1. Otwórz w przeglądarce: `http://twoja-domena.pl/create_admin.php`
2. Wypełnij formularz
3. **USUŃ PLIK** `create_admin.php` po utworzeniu konta!

**Opcja B: Ręcznie przez MySQL**
```sql
-- Wygeneruj hash hasła na: https://bcrypt-generator.com/
-- Następnie:
INSERT INTO admin_users (username, password, email, created_at) 
VALUES ('admin', '$2y$10$TWOJ_HASH_TUTAJ', 'admin@serwis.pl', NOW());
```

### Krok 6: Przetestuj instalację
1. Sprawdź stronę główną: `http://twoja-domena.pl/`
2. Zaloguj się do panelu: `http://twoja-domena.pl/admin/login.php`
3. Dodaj testowe zdjęcie w galerii
4. Dodaj testowy produkt z obrazkiem

---

## 📁 Struktura Plików

```
📦 Projekt
├── 📂 admin/                    # Panel administracyjny
│   ├── 📂 css/
│   │   └── admin.css           # Style panelu admina
│   ├── index.php               # Dashboard
│   ├── login.php               # Logowanie
│   ├── logout.php              # Wylogowanie
│   │
│   ├── 📰 AKTUALNOŚCI
│   ├── news.php                # Lista aktualności
│   ├── add_news.php            # Dodaj aktualność
│   ├── edit_news.php           # Edytuj aktualność
│   ├── toggle_news.php         # Publikuj/ukryj
│   ├── delete_news.php         # Usuń
│   │
│   ├── 💬 WIADOMOŚCI
│   ├── messages.php            # Lista wiadomości
│   ├── view_message.php        # Podgląd wiadomości
│   ├── mark_answered.php       # Oznacz jako odpowiedziana
│   ├── delete_message.php      # Usuń wiadomość
│   │
│   ├── 🖼️ GALERIA
│   ├── gallery.php             # Lista zdjęć
│   ├── add_gallery.php         # ✅ Dodaj zdjęcie (UPLOAD)
│   ├── edit_gallery.php        # ✅ Edytuj zdjęcie (UPLOAD)
│   ├── delete_gallery.php      # Usuń zdjęcie
│   │
│   ├── 📦 PRODUKTY
│   ├── products.php            # Lista produktów
│   ├── add_product.php         # ✅ Dodaj produkt (UPLOAD)
│   ├── edit_product.php        # ✅ Edytuj produkt (UPLOAD)
│   ├── delete_product.php      # Usuń produkt
│   ├── toggle_featured.php     # Wyróżnij/usuń wyróżnienie
│   │
│   ├── 🛠️ USŁUGI
│   ├── services.php            # Lista usług
│   ├── add_service.php         # Dodaj usługę
│   ├── edit_service.php        # Edytuj usługę
│   ├── delete_service.php      # Usuń usługę
│   └── toggle_service.php      # Aktywuj/dezaktywuj
│
├── 📂 css/
│   ├── home.css               # Style strony głównej
│   └── style.css              # Style podstron
│
├── 📂 js/
│   ├── home.js                # JavaScript strony głównej
│   └── main.js                # JavaScript podstron
│
├── 📂 includes/
│   ├── nav.php                # Nawigacja
│   └── footer.php             # Stopka
│
├── 📂 uploads/                 # ✅ Automatycznie tworzone
│   ├── 📂 gallery/            # Zdjęcia galerii
│   └── 📂 products/           # Zdjęcia produktów
│
├── 🌐 STRONY FRONTENDOWE
├── index.php                  # Strona główna
├── o-nas.php                  # O nas
├── oferta.php                 # Oferta
├── produkty.php               # Produkty (z obrazkami)
├── galeria.php                # Galeria (z obrazkami)
├── kontakt.php                # Kontakt
├── news-detail.php            # Szczegóły aktualności
│
├── ⚙️ KONFIGURACJA
├── config.php                 # Połączenie z bazą
├── database.sql               # Struktura bazy danych
├── create_admin.php           # Tworzenie konta admina
├── send_message.php           # Obsługa formularza
├── get_random_services.php    # Losowe usługi
│
└── 📄 README.md               # Ten plik
```

---

## 🔐 Logowanie do Panelu

### Dane Domyślne
- **URL**: `http://twoja-domena.pl/admin/login.php`
- **Login**: `admin`
- **Hasło**: To, które ustawiłeś w `create_admin.php`

### ⚠️ WAŻNE - Bezpieczeństwo!

1. **Zmień hasło natychmiast** po pierwszym logowaniu
2. **Usuń plik** `create_admin.php` po utworzeniu konta
3. **Nie używaj** domyślnych danych logowania w produkcji

### Zmiana hasła administratora
```sql
-- Wygeneruj nowy hash na https://bcrypt-generator.com/
UPDATE admin_users 
SET password = '$2y$10$NOWY_HASH_TUTAJ' 
WHERE username = 'admin';
```

---

## ⚙️ Konfiguracja

### Konfiguracja Email
W pliku `send_message.php` zmień adres email:
```php
$to = 'twoj@email.pl';  // Twój rzeczywisty email
```

### Dane Kontaktowe
Zaktualizuj w plikach:
- `index.php` - sekcja kontaktowa na stronie głównej
- `kontakt.php` - pełne dane kontaktowe
- `includes/footer.php` - stopka z danymi

**Przykład:**
```html
<p><i class="fas fa-map-marker-alt"></i> ul. Twoja 123, 00-000 Miasto</p>
<p><i class="fas fa-phone"></i> +48 123 456 789</p>
<p><i class="fas fa-envelope"></i> twoj@email.pl</p>
```

### Mapa Google
W `kontakt.php` zmień URL mapy:
```html
<iframe 
    src="https://www.google.com/maps/embed?pb=TWOJ_EMBED_KOD_TUTAJ"
    ...
</iframe>
```

**Jak uzyskać embed code:**
1. Otwórz Google Maps
2. Znajdź swoją lokalizację
3. Kliknij "Udostępnij" → "Umieść mapę"
4. Skopiuj kod iframe

---

## 🎨 Personalizacja

### Zmiana Kolorów
Edytuj zmienne CSS w `css/home.css` i `css/style.css`:
```css
:root {
    --primary-color: #ff6b35;      /* Główny kolor (pomarańczowy) */
    --secondary-color: #f7931e;    /* Drugorzędny (ciepły pomarańczowy) */
    --accent-color: #ffc107;       /* Akcent (żółty) */
    --dark-color: #2c3e50;         /* Ciemny tekst */
    --light-color: #ecf0f1;        /* Jasłe tło */
}
```

### Zmiana Logo
Edytuj w `includes/nav.php` i `admin/` plikach:
```html
<a href="index.php" class="logo">
    <i class="fas fa-laptop-code"></i>  <!-- Zmień ikonę -->
    <span>Twoja<strong>Nazwa</strong></span>  <!-- Zmień nazwę -->
</a>
```

### Dodanie Google Analytics
W `<head>` wszystkich stron dodaj:
```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

---

## 📈 SEO

### Wbudowane Funkcje SEO
✅ Meta tagi description i keywords na każdej stronie  
✅ Tagi Open Graph dla social media  
✅ Semantyczny HTML5  
✅ Responsywny design  
✅ Canonical URLs  
✅ Optymalizowane ładowanie obrazków (lazy loading)  
✅ Structured data ready  

### Dodatkowe Kroki SEO

#### 1. Utwórz `robots.txt`
```txt
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /uploads/

Sitemap: https://twoja-domena.pl/sitemap.xml
```

#### 2. Utwórz `sitemap.xml`
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://twoja-domena.pl/</loc>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://twoja-domena.pl/o-nas.php</loc>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>https://twoja-domena.pl/oferta.php</loc>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://twoja-domena.pl/produkty.php</loc>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://twoja-domena.pl/galeria.php</loc>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://twoja-domena.pl/kontakt.php</loc>
    <priority>0.8</priority>
  </url>
</urlset>
```

#### 3. Zarejestruj w Google Search Console
1. Wejdź na https://search.google.com/search-console
2. Dodaj swoją domenę
3. Zweryfikuj własność
4. Prześlij sitemap.xml

---

## 🔧 Rozwiązywanie Problemów

### ❌ Błędy połączenia z bazą danych
**Problem**: `Błąd połączenia z bazą danych`

**Rozwiązanie**:
1. Sprawdź dane w `config.php`
2. Upewnij się, że baza danych istnieje
3. Sprawdź uprawnienia użytkownika MySQL:
```sql
GRANT ALL PRIVILEGES ON serwis_komputerowy.* TO 'uzytkownik'@'localhost';
FLUSH PRIVILEGES;
```

### ❌ Formularz nie wysyła wiadomości
**Problem**: Formularz nie zapisuje wiadomości

**Rozwiązanie**:
1. Sprawdź, czy tabela `contact_messages` istnieje
2. Otwórz konsolę przeglądarki (F12) i sprawdź błędy JavaScript
3. Sprawdź uprawnienia do pliku `send_message.php`
4. Sprawdź logi błędów PHP: `/var/log/apache2/error.log`

### ❌ Zdjęcia nie wczytują się
**Problem**: Zdjęcia pokazują tylko ikony

**Rozwiązanie**:
1. Sprawdź, czy foldery istnieją:
   - `uploads/gallery/`
   - `uploads/products/`
2. Ustaw uprawnienia:
```bash
chmod 777 uploads/gallery
chmod 777 uploads/products
```
3. Sprawdź, czy plik faktycznie został przesłany
4. Sprawdź limit `upload_max_filesize` w `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### ❌ Panel admin nie działa
**Problem**: Nie można się zalogować / strony się nie ładują

**Rozwiązanie**:
1. Sprawdź, czy sesje PHP są włączone w `php.ini`:
```ini
session.save_path = "/tmp"
```
2. Sprawdź, czy konto admina istnieje:
```sql
SELECT * FROM admin_users WHERE username = 'admin';
```
3. Wyczyść cookies przeglądarki
4. Wyczyść sesje PHP:
```bash
rm -rf /tmp/sess_*
```

### ❌ Błąd "Call to undefined function"
**Problem**: `Call to undefined function password_hash()`

**Rozwiązanie**:
- Zaktualizuj PHP do wersji 7.4 lub nowszej
- Sprawdź wersję: `php -v`

### ❌ Obrazki nie wyświetlają się na stronie
**Problem**: W panelu admina są, ale na stronie nie

**Rozwiązanie**:
1. Sprawdź ścieżki w bazie danych:
```sql
SELECT image_path FROM products;
SELECT image_path FROM gallery;
```
2. Ścieżki powinny być relatywne: `uploads/products/nazwa.jpg`
3. NIE powinny zawierać `../`

---

## 🎯 TODO - Przyszłe Funkcje

### Planowane Ulepszenia
- [ ] 📧 System Newsletter z zapisem subskrybentów
- [ ] 📅 System rezerwacji wizyt online
- [ ] 🧮 Kalkulator wyceny napraw
- [ ] 💬 Chat online (LiveChat / Tawk.to)
- [ ] 🛒 Koszyk i system zamówień
- [ ] 📄 Strona produktu ze szczegółami
- [ ] 💬 System komentarzy pod aktualnościami
- [ ] 👤 Panel klienta z historią zgłoszeń
- [ ] 🧾 Generowanie faktur online
- [ ] 📊 Rozszerzone statystyki w panelu
- [ ] 🔍 Wyszukiwarka produktów
- [ ] ⭐ System ocen i recenzji
- [ ] 📧 Automatyczne emaile potwierdzające
- [ ] 📱 Aplikacja mobilna PWA
- [ ] 🌍 Wersje językowe (EN, DE)

### Możliwe Integracje
- [ ] Płatności online (PayU, Stripe, PayPal)
- [ ] Integracja z Facebook Pixel
- [ ] Google Shopping Feed
- [ ] Instagram Feed
- [ ] WhatsApp Business API
- [ ] SMS notifications

---

## 📞 Wsparcie

### Logi Błędów
```bash
# Apache
tail -f /var/log/apache2/error.log

# PHP
tail -f /var/log/php/error.log

# MySQL
tail -f /var/log/mysql/error.log
```

### Przydatne Komendy
```bash
# Sprawdź uprawnienia
ls -la uploads/

# Napraw uprawnienia
chmod -R 777 uploads/

# Sprawdź wersję PHP
php -v

# Sprawdź moduły PHP
php -m

# Restart Apache
sudo systemctl restart apache2
```

### Jeśli masz problemy:
1. ✅ Sprawdź logi błędów PHP i Apache
2. ✅ Sprawdź konsolę przeglądarki (F12)
3. ✅ Sprawdź uprawnienia do plików i folderów
4. ✅ Sprawdź konfigurację `php.ini`
5. ✅ Upewnij się, że wszystkie wymagane rozszerzenia PHP są włączone

---

## 📄 Licencja

Ten projekt jest tworzony na zamówienie dla serwisu komputerowego.  
**Wszelkie prawa zastrzeżone © 2026**

---

## 🙏 Podziękowania

Strona wykorzystuje następujące biblioteki i narzędzia:
- **Font Awesome** - ikony
- **Google Fonts** - czcionki
- **PHP** - backend
- **MySQL** - baza danych
- **Vanilla JavaScript** - interaktywność (bez frameworków!)

---

## 📝 Historia Zmian

### v1.1.0 (2026-01-13)
- ✅ **Naprawiono upload zdjęć w galerii**
- ✅ **Naprawiono upload zdjęć w produktach**
- ✅ Dodano automatyczne tworzenie folderów
- ✅ Dodano walidację rozmiaru i formatu plików
- ✅ Dodano podgląd przed przesłaniem
- ✅ Dodano usuwanie starych zdjęć przy aktualizacji
- ✅ Zaktualizowano dokumentację

### v1.0.0 (2026-01-10)
- 🎉 Pierwsza wersja strony
- ✅ Panel administracyjny
- ✅ System aktualności
- ✅ Galeria (z problemem uploadów)
- ✅ Produkty (z problemem uploadów)
- ✅ Usługi
- ✅ Formularz kontaktowy

---

## 🚀 Szybki Start

```bash
# 1. Rozpakuj pliki
unzip serwis-komputerowy.zip

# 2. Utwórz bazę danych
mysql -u root -p < database.sql

# 3. Edytuj config.php
nano config.php

# 4. Utwórz foldery
mkdir -p uploads/gallery uploads/products
chmod 777 uploads/gallery uploads/products

# 5. Utwórz konto admina
# Otwórz: http://localhost/create_admin.php

# 6. USUŃ create_admin.php
rm create_admin.php

# 7. Gotowe! 🎉
# Panel: http://localhost/admin/login.php
```

---

**Stworzone z ❤️ dla Twojego Serwisu Komputerowego**

*Powodzenia w rozwijaniu biznesu!* 🚀