<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$product = null;
$error = '';
$team_members = [];

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit;
    }

    // Get team members for contributions
    $team_members = $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY name")->fetchAll();
}

// Handle contribution submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contribution'])) {
    try {
        $team_member_id = intval($_POST['team_member_id'] ?? 0);
        $amount = floatval($_POST['contribution_amount'] ?? 0);
        $description = trim($_POST['contribution_description'] ?? '');

        if ($team_member_id > 0 && $amount > 0) {
            $stmt = $pdo->prepare("INSERT INTO financial_contributions (product_id, team_member_id, amount, description, contributed_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$product['id'], $team_member_id, $amount, $description]);
            $_SESSION['contribution_added'] = true;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle contribution deletion
if (isset($_GET['delete_contribution'])) {
    try {
        $pdo->prepare("DELETE FROM financial_contributions WHERE id = ? AND product_id = ?")->execute([$_GET['delete_contribution'], $_GET['id']]);
        $_SESSION['contribution_deleted'] = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get existing contributions for this product
$contributions = [];
if ($product) {
    $stmt = $pdo->prepare("SELECT fc.id, fc.team_member_id, fc.amount, fc.description, tm.name FROM financial_contributions fc JOIN team_members tm ON fc.team_member_id = tm.id WHERE fc.product_id = ? ORDER BY tm.name");
    $stmt->execute([$product['id']]);
    $contributions = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_contribution'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);

    if (empty($category)) {
        $error = 'Wybierz kategorię produktu!';
    }

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
            $featured = isset($_POST['featured']) ? 1 : 0;
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, price = :price, category = :category, stock = :stock, image_path = :image_path, olx_link = :olx_link, featured = :featured, is_visible = :is_visible, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category' => $category,
                ':stock' => $stock,
                ':image_path' => $image_path,
                ':olx_link' => $olx_link,
                ':featured' => $featured,
                ':is_visible' => $is_visible,
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

                <?php if (isset($_SESSION['contribution_added'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Wkład finansowy został dodany!
                    </div>
                    <?php unset($_SESSION['contribution_added']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['contribution_deleted'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Wkład finansowy został usunięty!
                    </div>
                    <?php unset($_SESSION['contribution_deleted']); ?>
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
                        <label for="category">Kategoria *</label>
                        <select id="category" name="category" required>
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
                        <small>Akceptowane formaty: JPG, PNG, GIF, WEBP (max 50MB)</small>
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
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_visible" <?php echo $product['is_visible'] ? 'checked' : ''; ?>>
                            <span>Widoczny dla klientów *Jeśli odznaczysz, produkt trafia do magazynu</span>
                        </label>
                    </div>
                    <!-- Sekcja wkładów finansowych -->
                    <div
                        style="background: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 10px; padding: 20px; margin: 20px 0;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #2c3e50;">
                            <i class="fas fa-money-bill-wave"></i> Wkłady finansowe (opcjonalnie)
                        </h3>
                        <p style="color: #666; margin-bottom: 15px; font-size: 0.95rem;">
                            Dodaj osoby które inwestowały pieniądze w ten produkt. Wkłady będą automatycznie dzielić
                            zysk ze sprzedaży.
                        </p>

                        <!-- Forma dodania wkładu -->
                        <?php if (!empty($team_members)): ?>
                            <div class="form-row"
                                style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                <div class="form-group">
                                    <label for="team_member_id">Członek zespołu</label>
                                    <select id="team_member_id" name="team_member_id">
                                        <option value="">-- Wybierz osobę --</option>
                                        <?php foreach ($team_members as $member): ?>
                                            <option value="<?php echo $member['id']; ?>">
                                                <?php echo htmlspecialchars($member['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="contribution_amount">Kwota (zł)</label>
                                    <input type="number" id="contribution_amount" name="contribution_amount" step="0.01"
                                        min="0" placeholder="0.00">
                                </div>
                                <div class="form-group">
                                    <label for="contribution_description">Opis (np. CPU, RAM, SSD)</label>
                                    <input type="text" id="contribution_description" name="contribution_description"
                                        placeholder="Komponenty...">
                                </div>
                                <div class="form-group" style="display: flex; align-items: flex-end;">
                                    <button type="submit" name="add_contribution" value="1" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Dodaj
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Lista wkładów -->
                        <?php if (!empty($contributions)): ?>
                            <div class="contributions-table">
                                <table class="data-table" style="margin: 0;">
                                    <thead>
                                        <tr>
                                            <th>Osoba</th>
                                            <th>Kwota</th>
                                            <th>Procent</th>
                                            <th>Opis</th>
                                            <th>Akcja</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total_contribs = array_sum(array_column($contributions, 'amount'));
                                        foreach ($contributions as $contrib):
                                            $percent = ($contrib['amount'] / $total_contribs) * 100;
                                            ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($contrib['name']); ?></strong></td>
                                                <td><?php echo number_format($contrib['amount'], 2); ?> zł</td>
                                                <td><?php echo number_format($percent, 1); ?>%</td>
                                                <td style="color: #666; font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($contrib['description'] ?? '-'); ?>
                                                </td>
                                                <td>
                                                    <a href="?id=<?php echo $product['id']; ?>&delete_contribution=<?php echo $contrib['id']; ?>"
                                                        onclick="return confirm('Usunąć wkład?')" class="btn-icon delete"
                                                        title="Usuń">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr style="background: #f0f0f0; font-weight: 600;">
                                            <td colspan="1">RAZEM:</td>
                                            <td><?php echo number_format($total_contribs, 2); ?> zł</td>
                                            <td>100%</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                style="margin-top: 10px; padding: 10px; background: #e8f4f8; border-left: 4px solid #667eea; border-radius: 4px;">
                                <small><i class="fas fa-info-circle"></i> <strong>Razem wkładów:</strong>
                                    <?php echo number_format($total_contribs, 2); ?> zł - Ta kwota będzie kostów przy
                                    sprzedaży w systemie finansów</small>
                            </div>
                        <?php else: ?>
                            <div
                                style="padding: 15px; background: white; border-radius: 8px; color: #999; text-align: center;">
                                <i class="fas fa-inbox"></i> Brak wkładów dla tego produktu
                            </div>
                        <?php endif; ?>
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

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-icon {
            padding: 8px;
            background: #f8f9fa;
            color: #666;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-icon:hover {
            color: #ff6b35;
            border-color: #ff6b35;
            background: #fff5f0;
        }

        .btn-icon.delete:hover {
            color: #dc3545;
            border-color: #dc3545;
            background: #fff5f5;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table thead {
            background: #f8f9fa;
        }

        .data-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .contributions-table {
            margin: 15px 0;
        }
    </style>
</body>

</html>