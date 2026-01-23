<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$team_members = $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY name")->fetchAll();

// Pobierz komponenty z magazynu dla PC Builder
$warehouse_components = [];
$component_contributions = [];
$component_categories = ['cpu', 'gpu', 'ram', 'storage', 'motherboard', 'psu', 'cooling', 'case'];

foreach ($component_categories as $cat) {
    $stmt = $pdo->prepare("SELECT id, name, price, category FROM products WHERE is_visible = 0 AND category = ? ORDER BY name");
    $stmt->execute([$cat]);
    $items = $stmt->fetchAll();
    $warehouse_components[$cat] = $items;

    // Pobierz wkłady dla tych komponentów
    foreach ($items as $item) {
        $stmt_contrib = $pdo->prepare("SELECT fc.*, tm.name as member_name FROM financial_contributions fc JOIN team_members tm ON fc.team_member_id = tm.id WHERE fc.product_id = ?");
        $stmt_contrib->execute([$item['id']]);
        $contribs = $stmt_contrib->fetchAll();
        if (!empty($contribs)) {
            $component_contributions[$item['id']] = $contribs;
        }
    }
}

// Pobierz usługi (tylko pojedyncze, bez pakietów)
$all_services = $pdo->query("SELECT id, name, price, category FROM services WHERE is_active = 1 AND category != 'package' ORDER BY display_order, name")->fetchAll();

$main_services = [];
$other_services = [];

$main_keywords = ['montaż', 'instalacja', 'serwis', 'testy', 'czyszczenie', 'składanie'];

foreach ($all_services as $service) {
    $is_main = false;
    foreach ($main_keywords as $keyword) {
        if (stripos($service['name'], $keyword) !== false) {
            $is_main = true;
            break;
        }
    }

    if ($is_main) {
        $main_services[] = $service;
    } else {
        $other_services[] = $service;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $pc_builder_mode = isset($_POST['pc_builder_mode']);

    if (empty($category) && !$pc_builder_mode) {
        $error = 'Wybierz kategorię produktu!';
    }

    // Jeśli tryb PC Builder, wymuś kategorię 'komputery'
    if ($pc_builder_mode) {
        $category = 'komputery';
    }

    $stock = intval($_POST['stock']);
    $olx_link = trim($_POST['olx_link']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    $image_path = null;

    // Sprawdź czy plik został przesłany
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Sprawdź maksymalny rozmiar (50MB)
        if ($_FILES['image']['size'] > 50 * 1024 * 1024) {
            $error = 'Plik jest za duży! Maksymalny rozmiar to 50MB.';
        }
        // Sprawdź rozszerzenie
        elseif (!in_array($ext, $allowed)) {
            $error = 'Nieprawidłowy format pliku! Dozwolone: JPG, PNG, GIF, WEBP';
        } else {
            // Utwórz katalog jeśli nie istnieje
            $upload_path = '../uploads/products/';
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            // Wygeneruj unikalną nazwę
            $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
            $full_path = $upload_path . $new_filename;

            // Przenieś plik
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
                $image_path = 'uploads/products/' . $new_filename;
            } else {
                $error = 'Błąd podczas przesyłania pliku. Sprawdź uprawnienia do zapisu.';
            }
        }
    }

    // Zapisz do bazy tylko jeśli nie było błędu
    if (empty($error)) {
        try {
            $featured = isset($_POST['featured']) ? 1 : 0;
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, stock, image_path, olx_link, featured, is_visible, created_at, updated_at) VALUES (:name, :description, :price, :category, :stock, :image_path, :olx_link, :featured, :is_visible, NOW(), NOW())");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category' => $category,
                ':stock' => $stock,
                ':image_path' => $image_path,
                ':olx_link' => $olx_link,
                ':featured' => $featured,
                ':is_visible' => $is_visible
            ]);

            $product_id = $pdo->lastInsertId();

            // Add contributions if provided
            if (isset($_POST['contributions']) && is_array($_POST['contributions'])) {
                foreach ($_POST['contributions'] as $contrib) {
                    if (!empty($contrib['member_id']) && !empty($contrib['amount'])) {
                        $stmt = $pdo->prepare("INSERT INTO financial_contributions (product_id, team_member_id, amount, description, contributed_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([$product_id, intval($contrib['member_id']), floatval($contrib['amount']), trim($contrib['description'] ?? '')]);
                    }
                }
            }

            // Handle component consumption if PC Builder mode
            if ($pc_builder_mode && isset($_POST['used_components']) && is_array($_POST['used_components'])) {
                foreach ($_POST['used_components'] as $comp_id) {
                    if (!empty($comp_id)) {
                        // Mark source contributions as transferred
                        $stmt = $pdo->prepare("UPDATE financial_contributions SET is_transferred = 1 WHERE product_id = ?");
                        $stmt->execute([intval($comp_id)]);

                        // Decrease stock
                        $stmt = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - 1) WHERE id = ?");
                        $stmt->execute([intval($comp_id)]);
                    }
                }
            }

            header('Location: products.php?success=added');
            exit;
        } catch (PDOException $e) {
            $error = 'Błąd bazy danych: ' . $e->getMessage();
            // Usuń przesłany plik
            if ($image_path && file_exists($full_path)) {
                unlink($full_path);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Produkt - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Dodaj Produkt</h1>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Powrót
                </a>
            </header>

            <div class="content-section full-width">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-group">
                        <label for="name">Nazwa Produktu *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Opis Produktu</label>
                        <textarea id="description" name="description" rows="4"
                            placeholder="Krótki opis produktu"></textarea>
                        <div class="form-hint" style="margin-top: 5px; font-size: 0.85rem; color: #666;">
                            <i class="fas fa-info-circle"></i> <strong>WSKAZÓWKA FORMATOWANIA:</strong><br>
                            • <code>-</code> (myślnik) = prosta kreska (minus)<br>
                            • <code>*</code> (gwiazdka) = ikona gwiazdki (ważne)<br>
                            • <code>•</code> (kropka/alt+7) = zielony ptaszek (potwierdzenie)<br>
                            <em>(WIELKIE LITERY lub dwukropek <code>:</code> na końcu linii tworzą nagłówek)</em>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Cena (zł) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="0.00" required
                                oninput="deselectCards()">
                        </div>

                        <div class="form-group">
                            <label for="stock">Stan Magazynowy *</label>
                            <input type="number" id="stock" name="stock" min="0" value="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategoria *</label>
                        <select id="category" name="category" required>
                            <option value="">Wybierz kategorię</option>
                            <option value="laptopy">Laptopy</option>
                            <option value="komputery">Komputery</option>
                            <option value="monitory">Monitory</option>
                            <option value="gpu">Karty Graficzne</option>
                            <option value="cpu">Procesory</option>
                            <option value="ram">Pamięci RAM</option>
                            <option value="storage">Dyski</option>
                            <option value="motherboard">Płyty Główne</option>
                            <option value="psu">Zasilacze</option>
                            <option value="cooling">Chłodzenie</option>
                            <option value="case">Obudowy</option>
                            <option value="other">Inne</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Zdjęcie Produktu (maks. 50MB)</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Akceptowane formaty: JPG, PNG, GIF, WEBP</small>
                        <div id="imagePreview" style="margin-top: 15px;"></div>
                    </div>

                    <div class="form-group">
                        <label for="olx_link">Link do OLX</label>
                        <input type="url" id="olx_link" name="olx_link" placeholder="https://www.olx.pl/...">
                        <small>Wklej link do aukcji na OLX</small>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured">
                            <span>Wyróżniony produkt (bestseller)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_visible" checked>
                            <span>Widoczny dla klientów *Jeśli odznaczysz, produkt trafia do magazynu</span>
                        </label>
                    </div>

                    <!-- Przełącznik trybu PC Builder -->
                    <div class="mode-toggle-section">
                        <label class="mode-toggle-label">
                            <input type="checkbox" id="pcBuilderMode" name="pc_builder_mode"
                                onchange="togglePCBuilder()">
                            <span><i class="fas fa-desktop"></i> Tryb PC Builder (Kalkulator zestawu
                                komputerowego)</span>
                        </label>
                    </div>

                    <!-- Sekcja PC Builder (ukryta domyślnie) -->
                    <div id="pcBuilderSection" class="pc-builder-section" style="display: none;">
                        <h3><i class="fas fa-calculator"></i> Kalkulator PC Builder</h3>
                        <p class="section-description">Wybierz komponenty z magazynu i usługi, aby automatycznie
                            obliczyć cenę zestawu</p>

                        <!-- Komponenty Obowiązkowe -->
                        <div class="builder-group">
                            <h4><i class="fas fa-exclamation-circle"></i> Komponenty Obowiązkowe</h4>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Procesor (CPU) *</label>
                                    <select id="cpu" onchange="updatePrice()">
                                        <option value="">-- Wybierz CPU --</option>
                                        <?php foreach ($warehouse_components['cpu'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="cpu_state" value="new" onchange="updatePrice()"> Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="cpu_state" value="used" checked
                                            onchange="updatePrice()">
                                        Używana
                                    </label>
                                </div>
                            </div>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Płyta Główna (Motherboard) *</label>
                                    <select id="motherboard" onchange="updatePrice()">
                                        <option value="">-- Wybierz płytę główną --</option>
                                        <?php foreach ($warehouse_components['motherboard'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="motherboard_state" value="new"
                                            onchange="updatePrice()"> Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="motherboard_state" value="used" checked
                                            onchange="updatePrice()"> Używana
                                    </label>
                                </div>
                            </div>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Pamięć RAM *</label>
                                    <select id="ram" onchange="updatePrice()">
                                        <option value="">-- Wybierz RAM --</option>
                                        <?php foreach ($warehouse_components['ram'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="ram_state" value="new" onchange="updatePrice()"> Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="ram_state" value="used" checked
                                            onchange="updatePrice()">
                                        Używana
                                    </label>
                                </div>
                            </div>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Zasilacz (PSU) *</label>
                                    <select id="psu" onchange="updatePrice()">
                                        <option value="">-- Wybierz zasilacz --</option>
                                        <?php foreach ($warehouse_components['psu'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="psu_state" value="new" onchange="updatePrice()"> Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="psu_state" value="used" checked
                                            onchange="updatePrice()">
                                        Używana
                                    </label>
                                </div>
                            </div>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Obudowa (Case) *</label>
                                    <select id="case" onchange="updatePrice()">
                                        <option value="">-- Wybierz obudowę --</option>
                                        <?php foreach ($warehouse_components['case'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="case_state" value="new" onchange="updatePrice()"> Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="case_state" value="used" checked
                                            onchange="updatePrice()">
                                        Używana
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Komponenty Opcjonalne -->
                        <div class="builder-group">
                            <h4><i class="fas fa-plus-circle"></i> Komponenty Opcjonalne</h4>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Karta Graficzna (GPU)</label>
                                    <select id="gpu" onchange="updatePrice()">
                                        <option value="">-- Brak --</option>
                                        <?php foreach ($warehouse_components['gpu'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="gpu_state" value="new" onchange="updatePrice()"> Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="gpu_state" value="used" checked
                                            onchange="updatePrice()">
                                        Używana
                                    </label>
                                </div>
                            </div>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Dysk (Storage)</label>
                                    <select id="storage" onchange="updatePrice()">
                                        <option value="">-- Brak --</option>
                                        <?php foreach ($warehouse_components['storage'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="storage_state" value="new" onchange="updatePrice()">
                                        Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="storage_state" value="used" checked
                                            onchange="updatePrice()">
                                        Używana
                                    </label>
                                </div>
                            </div>

                            <div class="component-row">
                                <div class="component-select">
                                    <label>Chłodzenie (Cooling)</label>
                                    <select id="cooling" onchange="updatePrice()">
                                        <option value="">-- Brak --</option>
                                        <?php foreach ($warehouse_components['cooling'] as $comp): ?>
                                            <option value="<?php echo $comp['id']; ?>"
                                                data-price="<?php echo $comp['price']; ?>">
                                                <?php echo htmlspecialchars($comp['name']); ?> -
                                                <?php echo number_format($comp['price'], 2); ?> zł
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="component-state">
                                    <label class="state-toggle">
                                        <input type="radio" name="cooling_state" value="new" onchange="updatePrice()">
                                        Nowa
                                    </label>
                                    <label class="state-toggle">
                                        <input type="radio" name="cooling_state" value="used" checked
                                            onchange="updatePrice()">
                                        Używana
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Usługi -->
                        <div class="builder-group">
                            <h4><i class="fas fa-wrench"></i> Usługi</h4>

                            <div class="main-services">
                                <h5>Główne usługi</h5>
                                <div class="services-grid">
                                    <?php foreach ($main_services as $service): ?>
                                        <label class="service-checkbox">
                                            <input type="checkbox" class="service-check"
                                                data-price="<?php echo $service['price']; ?>" onchange="updatePrice()">
                                            <span><?php echo htmlspecialchars($service['name']); ?></span>
                                            <strong><?php echo number_format($service['price'], 2); ?> zł</strong>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="additional-services"
                                style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ddd;">
                                <h5>Inne usługi</h5>
                                <div class="form-group" style="margin-top: 10px;">
                                    <select id="additional-service" onchange="updatePrice()">
                                        <option value="" data-price="0">-- Wybierz dodatkową usługę --</option>
                                        <?php foreach ($other_services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>"
                                                data-price="<?php echo $service['price']; ?>">
                                                <?php echo htmlspecialchars($service['name']); ?>
                                                (+<?php echo number_format($service['price'], 2); ?> zł)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Podsumowanie Cenowe -->
                        <div class="price-summary">
                            <h4><i class="fas fa-calculator"></i> Podsumowanie Cenowe</h4>
                            <div class="price-breakdown">
                                <div class="price-row">
                                    <span>Koszt komponentów:</span>
                                    <strong id="componentsCost">0.00 zł</strong>
                                </div>
                                <div class="price-row">
                                    <span>Koszt usług:</span>
                                    <strong id="servicesCost">0.00 zł</strong>
                                </div>
                                <div class="price-row total-cost">
                                    <span>Koszt całkowity:</span>
                                    <strong id="totalCost">0.00 zł</strong>
                                </div>

                                <h5 style="margin: 20px 0 10px; color: #2c3e50;">Wybierz cenę końcową:</h5>
                                <div class="price-selection-grid">
                                    <!-- Min Price -->
                                    <div class="price-selection-card" id="price-card-min" onclick="selectPrice('min')"
                                        data-value="0.00">
                                        <div class="select-icon"><i class="fas fa-tags"></i></div>
                                        <div class="price-label">Minimalna (+10%)</div>
                                        <div class="price-value" id="min-price-value">0.00 zł</div>
                                    </div>

                                    <!-- Suggested Price -->
                                    <div class="price-selection-card selected" id="price-card-suggested"
                                        onclick="selectPrice('suggested')" data-value="0.00">
                                        <div class="select-icon"><i class="fas fa-star"></i></div>
                                        <div class="price-label">Sugerowana (+20%)</div>
                                        <div class="price-value" id="suggested-price-value">0.00 zł</div>
                                    </div>

                                    <!-- Max Price -->
                                    <div class="price-selection-card" id="price-card-max" onclick="selectPrice('max')"
                                        data-value="0.00">
                                        <div class="select-icon"><i class="fas fa-chart-line"></i></div>
                                        <div class="price-label">Maksymalna (+30%)</div>
                                        <div class="price-value" id="max-price-value">0.00 zł</div>
                                    </div>
                                </div>
                            </div>
                            <div class="summary-actions" style="display: flex; gap: 10px; margin-top: 20px;">
                                <button type="button" class="btn btn-secondary" onclick="generateDescription()"
                                    style="flex: 1;">
                                    <i class="fas fa-file-alt"></i> Generuj opis zestawu
                                </button>
                            </div>
                        </div>

                        <style>
                            .price-selection-grid {
                                display: grid;
                                grid-template-columns: 1fr 1fr 1fr;
                                gap: 15px;
                                margin-top: 15px;
                            }

                            .price-selection-card {
                                border: 2px solid #e0e0e0;
                                border-radius: 10px;
                                padding: 15px;
                                text-align: center;
                                cursor: pointer;
                                transition: all 0.2s ease;
                                background: #fff;
                                position: relative;
                            }

                            .price-selection-card:hover {
                                border-color: #ff6b35;
                                transform: translateY(-2px);
                                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                            }

                            .price-selection-card.selected {
                                border-color: #ff6b35;
                                background: #fff5f0;
                                box-shadow: 0 0 0 1px #ff6b35;
                            }

                            .select-icon {
                                font-size: 1.5rem;
                                color: #666;
                                margin-bottom: 10px;
                                transition: color 0.2s;
                            }

                            .price-selection-card.selected .select-icon {
                                color: #ff6b35;
                            }

                            .price-label {
                                font-size: 0.85rem;
                                color: #666;
                                margin-bottom: 5px;
                            }

                            .price-value {
                                font-size: 1.2rem;
                                font-weight: 800;
                                color: #2c3e50;
                            }

                            @media (max-width: 768px) {
                                .price-selection-grid {
                                    grid-template-columns: 1fr;
                                }
                            }
                        </style>
                    </div>

                    <!-- Sekcja wkładów finansowych -->
                    <div
                        style="background: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 10px; padding: 20px; margin: 20px 0;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #2c3e50;">
                            <i class="fas fa-money-bill-wave"></i> Wkłady finansowe (opcjonalnie)
                        </h3>
                        <div class="alert alert-info" style="margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Ważne:</strong> Dodanie wkładów finansowych jest <u>opcjonalne</u>. Jeśli dodasz
                            wkłady tutaj, będą automatycznie naliczane do finansów podczas rejestracji sprzedaży. Jeśli
                            nie dodasz wkładów, będziesz musiał je dodać ręcznie później w sekcji "Finansów".
                        </div>
                        <p style="color: #666; margin-bottom: 15px; font-size: 0.95rem;">
                            Dodaj osoby które inwestowały pieniądze w ten produkt. Wkłady będą automatycznie dzielić
                            zysk ze sprzedaży.
                        </p>

                        <?php if (!empty($team_members)): ?>
                            <div id="contributions-container">
                                <!-- Dynamically added contribution fields go here -->
                            </div>

                            <button type="button" id="add-contribution-btn" class="btn btn-secondary"
                                style="margin-top: 10px;">
                                <i class="fas fa-plus"></i> Dodaj wkład
                            </button>
                        <?php else: ?>
                            <div
                                style="padding: 15px; background: white; border-radius: 8px; color: #999; text-align: center;">
                                <i class="fas fa-info-circle"></i> Brak członków zespołu - dodaj ich w sekcji
                                <strong>Finanse → Zespół</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Dodaj Produkt
                        </button>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Anuluj
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Podgląd zdjęcia przed przesłaniem
        document.getElementById('image').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = `
                        <div style="text-align: center;">
                            <p style="margin-bottom: 10px; font-weight: 600; color: #2c3e50;">Podgląd:</p>
                            <img src="${e.target.result}" style="max-width: 300px; max-height: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });

        // PC Builder Functions
        function togglePCBuilder() {
            const checkbox = document.getElementById('pcBuilderMode');
            const section = document.getElementById('pcBuilderSection');
            const categoryField = document.getElementById('category');

            if (checkbox.checked) {
                section.style.display = 'block';
                categoryField.value = 'komputery';
                categoryField.disabled = true;
            } else {
                section.style.display = 'none';
                categoryField.disabled = false;
            }
        }

        function updatePrice() {
            let componentsCost = 0;
            let servicesCost = 0;

            // Komponenty obowiązkowe i opcjonalne
            const components = ['cpu', 'motherboard', 'ram', 'psu', 'case', 'gpu', 'storage', 'cooling'];

            components.forEach(comp => {
                const select = document.getElementById(comp);
                if (select && select.value) {
                    const option = select.options[select.selectedIndex];
                    let price = parseFloat(option.getAttribute('data-price')) || 0;

                    // Sprawdź czy używana (Logika zmieniona: cena pozostaje bez zmian, tylko oznaczenie)
                    const stateRadios = document.getElementsByName(comp + '_state');
                    for (let radio of stateRadios) {
                        if (radio.checked && radio.value === 'used') {
                            // price = price; // Cena bez zmian
                            break;
                        }
                    }

                    componentsCost += price;
                }
            });

            // Usługi z checkboxów
            const serviceChecks = document.querySelectorAll('.service-check');
            serviceChecks.forEach(check => {
                if (check.checked) {
                    servicesCost += parseFloat(check.getAttribute('data-price')) || 0;
                }
            });

            // Usługa z dropdowna
            const additionalServiceSelect = document.getElementById('additional-service');
            if (additionalServiceSelect && additionalServiceSelect.value) {
                const addOption = additionalServiceSelect.options[additionalServiceSelect.selectedIndex];
                servicesCost += parseFloat(addOption.getAttribute('data-price')) || 0;
            }

            // Koszt całkowity
            const totalCost = componentsCost + servicesCost;

            // Marże
            const minPrice = totalCost * 1.10;  // +10%
            const suggestedPrice = totalCost * 1.20;  // +20%
            const maxPrice = totalCost * 1.30;  // +30%

            // Aktualizuj wyświetlanie
            document.getElementById('componentsCost').textContent = componentsCost.toFixed(2) + ' zł';
            document.getElementById('servicesCost').textContent = servicesCost.toFixed(2) + ' zł';
            document.getElementById('totalCost').textContent = totalCost.toFixed(2) + ' zł';

            // Update values for selection cards
            document.getElementById('min-price-value').textContent = minPrice.toFixed(2) + ' zł';
            document.getElementById('suggested-price-value').textContent = suggestedPrice.toFixed(2) + ' zł';
            document.getElementById('max-price-value').textContent = maxPrice.toFixed(2) + ' zł';

            // Store raw values in data attributes for easy retrieval
            document.getElementById('price-card-min').dataset.value = minPrice.toFixed(2);
            document.getElementById('price-card-suggested').dataset.value = suggestedPrice.toFixed(2);
            document.getElementById('price-card-max').dataset.value = maxPrice.toFixed(2);
        }

        function selectPrice(type) {
            // Remove active class from all
            document.querySelectorAll('.price-selection-card').forEach(card => card.classList.remove('selected'));

            // Add to selected
            const card = document.getElementById('price-card-' + type);
            if (card) {
                card.classList.add('selected');
                const price = card.dataset.value;
                document.getElementById('price').value = price;
            }

            // Trigger contribution transfer whenever price is selected
            transferContributions();
        }

        function generateDescription() {
            let description = "Pełna specyfikacja zestawu:\n\n";
            const components = ['cpu', 'motherboard', 'ram', 'psu', 'case', 'gpu', 'storage', 'cooling'];

            components.forEach(comp => {
                const select = document.getElementById(comp);
                if (select && select.value) {
                    const option = select.options[select.selectedIndex];
                    const name = option.text.split(' - ')[0].trim();

                    const stateRadios = document.getElementsByName(comp + '_state');
                    let state = "Nowa";
                    for (let radio of stateRadios) {
                        if (radio.checked && radio.value === 'used') {
                            state = "Używana";
                            break;
                        }
                    }

                    const label = select.previousElementSibling ? select.previousElementSibling.textContent.replace(' *', '') : comp.toUpperCase();
                    description += `• ${label}: ${name} (${state})\n`;
                }
            });

            description += "\nUsługi wliczone w cenę:\n";
            const serviceChecks = document.querySelectorAll('.service-check');
            serviceChecks.forEach(check => {
                if (check.checked) {
                    description += `• ${check.nextElementSibling.textContent}\n`;
                }
            });

            const additionalServiceSelect = document.getElementById('additional-service');
            if (additionalServiceSelect && additionalServiceSelect.value) {
                const addOption = additionalServiceSelect.options[additionalServiceSelect.selectedIndex];
                const serviceName = addOption.text.split(' (+')[0].trim();
                description += `• ${serviceName}\n`;
            }

            document.getElementById('description').value = description;

            // Przewiń do opisu żeby użytkownik wiedział że się zmieniło
            document.getElementById('description').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Deprecated: applySuggestedPrice merged into selectPrice
        function applySuggestedPrice() {
            selectPrice('suggested');
        }

        const componentContributions = <?php echo json_encode($component_contributions); ?>;

        function transferContributions() {
            const container = document.getElementById('contributions-container');
            // Wyczyść istniejące wkłady (podzespołowe) - szukamy wkładów które mają w opisie "[AUTOMAT]"
            const existingAuto = container.querySelectorAll('.auto-contribution, .auto-contribution-component');
            existingAuto.forEach(el => el.remove());

            const components = ['cpu', 'motherboard', 'ram', 'psu', 'case', 'gpu', 'storage', 'cooling'];

            components.forEach(comp => {
                const select = document.getElementById(comp);
                if (select && select.value) {
                    const productId = select.value;
                    const option = select.options[select.selectedIndex];
                    const productName = option.text.split(' - ')[0].trim();
                    const label = select.previousElementSibling ? select.previousElementSibling.textContent.replace(' *', '') : comp.toUpperCase();

                    if (componentContributions[productId]) {
                        componentContributions[productId].forEach(contrib => {
                            addAutoContribution(contrib.team_member_id, contrib.amount, `${label}: ${productName}`);
                        });

                        // Add hidden field to track used component
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'used_components[]';
                        hiddenInput.value = productId;
                        hiddenInput.className = 'auto-contribution-component';
                        container.appendChild(hiddenInput);
                    }
                }
            });

            // Powiadom użytkownika jeśli dodano wkłady
            const newAuto = container.querySelectorAll('.auto-contribution');
            if (newAuto.length > 0) {
                // Skroluj do sekcji wkładów
                document.querySelector('.pc-builder-section').nextElementSibling.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function addAutoContribution(memberId, amount, description) {
            const container = document.getElementById('contributions-container');
            const id = contributionCount++;

            const html = `
                <div class="contribution-item auto-contribution" id="contribution-${id}" style="border-left: 4px solid #ff6b35; background: #fff9f5;">
                    <div class="form-group">
                        <label>Osoba</label>
                        <select name="contributions[${id}][member_id]">
                            <option value="">-- Wybierz --</option>
                            ${teamMembers.map(m => `<option value="${m.id}" ${m.id == memberId ? 'selected' : ''}>${m.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kwota (zł)</label>
                        <input type="number" name="contributions[${id}][amount]" step="0.01" min="0" value="${amount}">
                    </div>
                    <div class="form-group">
                        <label>Opis</label>
                        <input type="text" name="contributions[${id}][description]" value="${description}">
                    </div>
                    <button type="button" class="btn-remove-contribution" onclick="document.getElementById('contribution-${id}').remove()">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div style="grid-column: span 4; font-size: 0.75rem; color: #ff6b35; margin-top: -5px;">
                        <i class="fas fa-robot"></i> Automatycznie przeniesione z podzespołu
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
        }
    </script>

    <style>
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s ease;
            background: white;
        }

        select:focus {
            outline: none;
            border-color: #ff6b35;
        }

        .contribution-item {
            background: white;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 50px;
            gap: 10px;
            align-items: flex-end;
        }

        .contribution-item .form-group {
            margin-bottom: 0;
        }

        .btn-remove-contribution {
            background: #dc3545;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .btn-remove-contribution:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* PC Builder Styles */
        .mode-toggle-section {
            margin: 20px 0;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        .mode-toggle-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .mode-toggle-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .pc-builder-section {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
        }

        .pc-builder-section h3 {
            color: #667eea;
            margin-top: 0;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-description {
            color: #666;
            margin-bottom: 25px;
        }

        .builder-group {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .builder-group h4 {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .component-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            align-items: end;
        }

        .component-select label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .component-state {
            display: flex;
            gap: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .state-toggle {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .state-toggle input[type="radio"] {
            cursor: pointer;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }

        .service-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .service-checkbox:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .service-checkbox input[type="checkbox"] {
            cursor: pointer;
        }

        .service-checkbox span {
            flex: 1;
            font-weight: 500;
        }

        .service-checkbox strong {
            color: #155724;
        }

        .main-services h5,
        .additional-services h5 {
            margin-bottom: 12px;
            color: #2c3e50;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .main-services h5::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #28a745;
        }

        .additional-services h5::before {
            content: '\f055';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: #667eea;
        }

        .price-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            border-radius: 10px;
            color: white;
        }

        .price-summary h4 {
            margin-top: 0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .price-breakdown {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: #2c3e50;
        }

        .price-row.total-cost {
            font-size: 1.1rem;
            padding: 12px 0;
            border-top: 2px solid #e0e0e0;
            border-bottom: 2px solid #e0e0e0;
        }

        .price-row.margin-price {
            font-weight: 600;
        }

        .price-row.margin-price.suggested {
            background: #d4edda;
            padding: 12px;
            margin: 5px -15px;
            border-radius: 6px;
        }

        .price-row.margin-price.suggested strong {
            color: #155724;
            font-size: 1.2rem;
        }

        .price-summary hr {
            border: none;
            border-top: 2px dashed #e0e0e0;
            margin: 10px 0;
        }

        .price-summary .btn {
            width: 100%;
            justify-content: center;
        }
    </style>

    <script>
        const teamMembers = <?php echo json_encode($team_members); ?>;
        let contributionCount = 0;

        document.getElementById('add-contribution-btn')?.addEventListener('click', function () {
            addContributionField();
        });

        function addContributionField() {
            const container = document.getElementById('contributions-container');
            const id = contributionCount++;

            const html = `
                <div class="contribution-item" id="contribution-${id}">
                    <div class="form-group">
                        <label>Osoba</label>
                        <select name="contributions[${id}][member_id]">
                            <option value="">-- Wybierz --</option>
                            ${teamMembers.map(m => `<option value="${m.id}">${m.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kwota (zł)</label>
                        <input type="number" name="contributions[${id}][amount]" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Opis</label>
                        <input type="text" name="contributions[${id}][description]" placeholder="CPU, RAM, SSD...">
                    </div>
                    <button type="button" class="btn-remove-contribution" onclick="document.getElementById('contribution-${id}').remove()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
</body>

</html>