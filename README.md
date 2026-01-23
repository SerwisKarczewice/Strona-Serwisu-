🖥️ Serwis Komputerowy - Strona WWW

Profesjonalna strona internetowa dla serwisu komputerowego z rozbudowanym panelem administracyjnym, systemem finansowym i automatycznym skalowaniem UI.

---

## 📋 Szybkie Linki

- [Wymagania](#-wymagania)
- [Instalacja i Admin](#-instalacja-i-zarządzanie-adminem)
- [Struktura plików](#-struktura-plików)
- [Panel Administracyjny](#-panel-administracyjny)
- [Wygląd i Estetyka](#-wygląd-i-estetyka)
- [TODO](#-todo)

---

## ⚙️ Wymagania

- **PHP**: 7.4+ (zalecane 8.x)
- **MySQL/MariaDB**: 5.7+
- **Rozszerzenia**: PDO, GD (do obróbki zdjęć), mbstring
- **Przestrzeń**: min. 100MB + miejsce na załączniki/zdjęcia

---

## � Instalacja i Zarządzanie Adminem

### Pierwsza konfiguracja (Kreowanie Admina)
Projekt zawiera dedykowany skrypt do bezpiecznego tworzenia pierwszego konta administratora.

1. **Baza danych**: Zaimportuj plik `admin/database.sql` (zawiera strukturę tabel dla newsów, produktów, galerii, finansów i użytkowników).
2. **Konfiguracja**: Ustaw dane dostępowe w `config.php`.
3. **Tworzenie konta**:
   - Uruchom skrypt `http://podtwojadomena.pl/admin/create_admin.php`.
   - Podaj nazwę użytkownika, bezpieczne hasło (jest automatycznie haszowane przez `password_hash()`) oraz email.
   - **⚠️ CRITICAL SECURITY**: Po poprawnym utworzeniu konta, **NATYCHMIAST USUŃ** plik `admin/create_admin.php` z serwera. Skrypt ten nie posiada autentykacji (abyś mógł stworzyć pierwszego admina) i zostawienie go otwiera lukę bezpieczeństwa.

### Konta i Uprawnienia
System przechowuje użytkowników w tabeli `admin_users`. Każde logowanie aktualizuje pole `last_login`, co pozwala śledzić aktywność w panelu.

---

## 📁 Struktura Plików

```
📦 Projekt
├── admin/                          # Panel administracyjny (Backend)
│   ├── index.php                   # Dashboard ze statystykami i licznikami
│   ├── finances.php                # System rozliczeń, wkładów i zysków (Team System)
│   ├── calculator.php              # Zaawansowany kalkulator wycen i usług
│   ├── database.sql                # Schemat bazy danych
│   └── [moduły].php                # Zarządzanie wiadomościami, newsami, produktami itd.
│
├── css/
│   ├── home.css                    # Style strony głównej (Hero, Visit Section, Animacje)
│   └── style.css                   # Style globalne, karty produktów, pakiety ofertowe
│
├── includes/
│   ├── nav.php                     # Inteligentne menu (zaznacza aktywną stronę)
│   ├── footer.php                  # Stopka z danymi kontaktowymi
│   └── visit_counter.php           # Logika licznika odwiedzin (unikalne sesje)
│
├── index.php                        # Strona główna (Landing Page)
├── oferta.php                       # Interaktywny cennik usług
├── produkty.php                     # Sklep/Katalog podzespołów
├── product-detail.php               # Szczegółowy opis produktu (specyfikacja)
└── config.php                       # Globalne połączenie PDO i start sesji
```

---

## 🔐 Panel Administracyjny

Panel został zaprojektowany w ciemno-pomarańczowej estetyce (Dark-Orange Premium), zapewniającej komfort pracy:

- **Dashboard**: Podgląd na żywo liczby wiadomości, aktywnych usług i **całkowitej liczby odwiedzin strony**.
- **System Finansowy**: Unikalna funkcja zarządzania "Wkładami Członków Zespołu". Pozwala na:
    - Dodawanie wkładów finansowych do konkretnych produktów (np. kto kupił procesor, kto płytę).
    - Automatyczne wyliczanie zysku netto po sprzedaży.
    - Dzielenie zysku między członków zespołu na podstawie procentowego udziału w kosztach.
- **Kalkulator & Faktury**: Możliwość tworzenia ofert dla klientów i generowania ich do formatu PDF.

---

## ✨ Wygląd i Estetyka

### Design System
Strona oparta jest o nowoczesny **Design System** z silnym naciskiem na "Wow Factor":
- **Kolorystyka**: Głęboki pomarańcz (`#ff6b35`) połączony z czystym białym tłem i delikatnymi szarościami w sekcjach tekstowych.
- **Efekty**: Glassmorphism (szklane elementy), płynne gradienty oraz cienie typu `Soft-Shadow` dla kart produktów.
- **Animacje**: Mikro-interakcje na przyciskach, gładkie hover-efekty obrazków i animowane ikony pływające w tle (Hero Section).

### 📏 Adaptive Scaling (Inteligentny Zoom)
Wdrożyliśmy niestandardowy system skalowania, który rozwiązuje problem "zbyt wielkich elementów" na standardowych laptopach:
- **Widok 1440p+**: Strona wyświetla się w pełnej krasie z bazowym fontem `18px`.
- **Widok 1080p (Standard Laptop)**: Strona stosuje **automatyczny zoom 80%** (baza `14.4px`). Dzięki temu na rozdzielczości 1920x1080 witryna wygląda tak, jakby użytkownik ręcznie pomniejszył widok w przeglądarce – staje się bardziej zwarta, profesjonalna i "skondensowana".
- **Mobile First**: Układy typu Grid automatycznie przełączają się w tryb jednokolumnowy na telefonach, zachowując czytelność przycisków.

---

## 🎨 Paleta Kolorystyczna (Brand Identity)

Projekt wykorzystuje spójną paletę barw, która definiuje nowoczesny i profesjonalny charakter serwisu:

| Kolor | Nazwa | Hex | Zastosowanie |
|:---:|:---|:---:|:---|
| ![#ff6b35](https://img.placeholder.com/15/ff6b35?text=+) | **Primary** | `#ff6b35` | Główne przyciski, branding, akcenty. |
| ![#f7931e](https://img.placeholder.com/15/f7931e?text=+) | **Secondary** | `#f7931e` | Gradienty, elementy uzupełniające. |
| ![#ffc107](https://img.placeholder.com/15/ffc107?text=+) | **Accent** | `#ffc107` | Gwiazdki, wyróżnienia, ostrzeżenia. |
| ![#2c3e50](https://img.placeholder.com/15/2c3e50?text=+) | **Dark** | `#2c3e50` | Nagłówki, tła paneli, ciemne teksty. |
| ![#ecf0f1](https://img.placeholder.com/15/ecf0f1?text=+) | **Light** | `#ecf0f1` | Tła sekcji, delikatne separatory. |
| ![#333333](https://img.placeholder.com/15/333333?text=+) | **Text Dark** | `#333333` | Główny tekst strony. |
| ![#666666](https://img.placeholder.com/15/666666?text=+) | **Text Light** | `#666666` | Opisy pomocnicze, daty, meta-dane. |

**Główne Gradienty:**
- **Primary Gradient:** `linear-gradient(135deg, #ff6b35 0%, #f7931e 100%)`
- **Secondary Gradient:** `linear-gradient(135deg, #ffc107 0%, #ff9800 100%)`

---

## ⚙️ Konfiguracja

Zmienne CSS znajdują się w nagłówku plików styli – możesz jednym kliknięciem zmienić kolorystykę całej marki:
```css
:root {
    --primary-color: #ff6b35; /* Kolor główny (Brand) */
    --gradient-primary: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
}
```

---

## � Typografia (Fonts)

Strona wykorzystuje czytelny i nowoczesny system typograficzny oparty na fontach systemowych, co zapewnia błyskawiczne ładowanie strony:

- **Główny Font:** `'Segoe UI'` (standard dla Windows, zapewniający świetną czytelność).
- **Fallback:** `Tahoma`, `Geneva`, `Verdana`, `sans-serif`.
- **Ikony:** `Font Awesome 6.4.0` (używane w menu, kartach produktów i panelu admina).

---

## �📋 TODO

- [x] Zaawansowany system finansowy (podział zysków).
- [x] Inteligentne skalowanie UI dla 1080p.
- [x] Generator faktur PDF.
