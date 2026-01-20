<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$team_members = $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
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
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Cena (zł) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="0.00" required>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stan Magazynowy *</label>
                            <input type="number" id="stock" name="stock" min="0" value="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategoria</label>
                        <select id="category" name="category">
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
                            <span>Widoczny dla klientów</span>
                        </label>
                    </div>
                    <!-- Sekcja wkładów finansowych -->
                    <div style="background: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 10px; padding: 20px; margin: 20px 0;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #2c3e50;">
                            <i class="fas fa-money-bill-wave"></i> Wkłady finansowe (opcjonalnie)
                        </h3>
                        <div class="alert alert-info" style="margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Ważne:</strong> Dodanie wkładów finansowych jest <u>opcjonalne</u>. Jeśli dodasz wkłady tutaj, będą automatycznie naliczane do finansów podczas rejestracji sprzedaży. Jeśli nie dodasz wkładów, będziesz musiał je dodać ręcznie później w sekcji "Finansów".
                        </div>
                        <p style="color: #666; margin-bottom: 15px; font-size: 0.95rem;">
                            Dodaj osoby które inwestowały pieniądze w ten produkt. Wkłady będą automatycznie dzielić zysk ze sprzedaży.
                        </p>

                        <?php if (!empty($team_members)): ?>
                        <div id="contributions-container">
                            <!-- Dynamically added contribution fields go here -->
                        </div>
                        
                        <button type="button" id="add-contribution-btn" class="btn btn-secondary" style="margin-top: 10px;">
                            <i class="fas fa-plus"></i> Dodaj wkład
                        </button>
                        <?php else: ?>
                        <div style="padding: 15px; background: white; border-radius: 8px; color: #999; text-align: center;">
                            <i class="fas fa-info-circle"></i> Brak członków zespołu - dodaj ich w sekcji <strong>Finanse → Zespół</strong>
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
    </style>

    <script>
        const teamMembers = <?php echo json_encode($team_members); ?>;
        let contributionCount = 0;

        document.getElementById('add-contribution-btn')?.addEventListener('click', function() {
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