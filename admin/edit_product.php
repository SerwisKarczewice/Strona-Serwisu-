<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$product = null;
$error = '';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $stock = intval($_POST['stock']);
    $olx_link = trim($_POST['olx_link']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    $image_path = $product['image_path'];

    // Sprawdź czy nowy plik został przesłany
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($_FILES['image']['size'] > 50 * 1024 * 1024) {
            $error = 'Plik jest za duży! Maksymalny rozmiar to 50MB.';
        } elseif (!in_array($ext, $allowed)) {
            $error = 'Nieprawidłowy format pliku!';
        } else {
            $upload_path = '../uploads/products/';
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $new_filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
            $full_path = $upload_path . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
                // Usuń stare zdjęcie
                if ($product['image_path'] && file_exists('../' . $product['image_path'])) {
                    unlink('../' . $product['image_path']);
                }
                $image_path = 'uploads/products/' . $new_filename;
            } else {
                $error = 'Błąd podczas przesyłania pliku.';
            }
        }
    }

    // Zapisz zmiany jeśli nie było błędu
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, category = :category, stock = :stock, image_path = :image_path, olx_link = :olx_link, featured = :featured, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category' => $category,
                ':stock' => $stock,
                ':image_path' => $image_path,
                ':olx_link' => $olx_link,
                ':featured' => $featured,
                ':id' => $_GET['id']
            ]);

            header('Location: products.php?success=updated');
            exit;
        } catch (PDOException $e) {
            $error = 'Błąd bazy danych: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Produkt - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
       <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Edytuj Produkt</h1>
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
                        <input type="text" id="name" name="name"
                            value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Opis Produktu</label>
                        <textarea id="description" name="description" rows="4"
                            placeholder="Krótki opis produktu"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Cena (zł) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0"
                                value="<?php echo $product['price']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stan Magazynowy *</label>
                            <input type="number" id="stock" name="stock" min="0"
                                value="<?php echo $product['stock']; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategoria</label>
                        <select id="category" name="category">
                            <option value="">Wybierz kategorię</option>
                            <option value="laptopy" <?php echo ($product['category'] == 'laptopy') ? 'selected' : ''; ?>>
                                Laptopy</option>
                            <option value="komputery" <?php echo ($product['category'] == 'komputery') ? 'selected' : ''; ?>>Komputery</option>
                            <option value="monitory" <?php echo ($product['category'] == 'monitory') ? 'selected' : ''; ?>>Monitory</option>
                            <option value="gpu" <?php echo ($product['category'] == 'gpu') ? 'selected' : ''; ?>>Karty
                                Graficzne</option>
                            <option value="cpu" <?php echo ($product['category'] == 'cpu') ? 'selected' : ''; ?>>Procesory
                            </option>
                            <option value="ram" <?php echo ($product['category'] == 'ram') ? 'selected' : ''; ?>>Pamięci
                                RAM</option>
                            <option value="storage" <?php echo ($product['category'] == 'storage') ? 'selected' : ''; ?>>
                                Dyski</option>
                            <option value="motherboard" <?php echo ($product['category'] == 'motherboard') ? 'selected' : ''; ?>>Płyty Główne</option>
                            <option value="psu" <?php echo ($product['category'] == 'psu') ? 'selected' : ''; ?>>Zasilacze
                            </option>
                            <option value="cooling" <?php echo ($product['category'] == 'cooling') ? 'selected' : ''; ?>>
                                Chłodzenie</option>
                            <option value="case" <?php echo ($product['category'] == 'case') ? 'selected' : ''; ?>>Obudowy
                            </option>
                            <option value="other" <?php echo ($product['category'] == 'other') ? 'selected' : ''; ?>>Inne
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Zdjęcie Produktu</label>
                        <?php if ($product['image_path'] && file_exists('../' . $product['image_path'])): ?>
                            <div class="current-image">
                                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>"
                                    alt="Aktualne zdjęcie">
                                <p>Aktualne zdjęcie</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Akceptowane formaty: JPG, PNG, GIF, WEBP (max 5MB)</small>
                        <div id="imagePreview" style="margin-top: 15px;"></div>
                    </div>

                    <div class="form-group">
                        <label for="olx_link">Link do OLX</label>
                        <input type="url" id="olx_link" name="olx_link"
                            value="<?php echo htmlspecialchars($product['olx_link'] ?? ''); ?>"
                            placeholder="https://www.olx.pl/...">
                        <small>Wklej link do aukcji na OLX</small>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured" <?php echo $product['featured'] ? 'checked' : ''; ?>>
                            <span>Wyróżniony produkt (bestseller)</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Zapisz Zmiany
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
        document.getElementById('image').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = `
                        <div style="text-align: center;">
                            <p style="margin-bottom: 10px; font-weight: 600; color: #2c3e50;">Nowy podgląd:</p>
                            <img src="${e.target.result}" style="max-width: 300px; max-height: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <style>
        .current-image {
            margin-bottom: 15px;
            text-align: center;
        }

        .current-image img {
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .current-image p {
            margin-top: 10px;
            color: #666;
            font-size: 0.9rem;
        }

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
    </style>
</body>

</html>