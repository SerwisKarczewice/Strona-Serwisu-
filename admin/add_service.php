<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$edit_mode = isset($_GET['id']);
$service = null;

if ($edit_mode) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $service = $stmt->fetch();

    if (!$service) {
        header('Location: services.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $detailed_description = trim($_POST['detailed_description'] ?? '');
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $category = $_POST['category'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $display_order = intval($_POST['display_order']);
    $execution_count = intval($_POST['execution_count'] ?? 0);

    $image_path = null;

    // Obsługa przesyłania zdjęcia głównego
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = '../uploads/services/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed) && $_FILES['image']['size'] <= 50 * 1024 * 1024) {
            $filename = 'service_' . time() . '_' . uniqid() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                $image_path = 'uploads/services/' . $filename;

                // Usuń stare zdjęcie jeśli istnieje
                if ($edit_mode && !empty($service['image_path']) && file_exists('../' . $service['image_path'])) {
                    unlink('../' . $service['image_path']);
                }
            }
        }
    } elseif ($edit_mode && !empty($service['image_path'])) {
        $image_path = $service['image_path'];
    }

    if ($edit_mode) {
        $stmt = $pdo->prepare("UPDATE services SET name = :name, description = :description, detailed_description = :detailed_description, image_path = :image_path, price = :price, discount_price = :discount_price, category = :category, is_active = :is_active, display_order = :display_order, execution_count = :execution_count, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':detailed_description' => $detailed_description,
            ':image_path' => $image_path,
            ':price' => $price,
            ':discount_price' => $discount_price,
            ':category' => $category,
            ':is_active' => $is_active,
            ':display_order' => $display_order,
            ':execution_count' => $execution_count,
            ':id' => $_GET['id']
        ]);
        $message = 'Usługa została zaktualizowana!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, detailed_description, image_path, price, discount_price, category, is_active, display_order, execution_count, created_at) VALUES (:name, :description, :detailed_description, :image_path, :price, :discount_price, :category, :is_active, :display_order, :execution_count, NOW())");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':detailed_description' => $detailed_description,
            ':image_path' => $image_path,
            ':price' => $price,
            ':discount_price' => $discount_price,
            ':category' => $category,
            ':is_active' => $is_active,
            ':display_order' => $display_order,
            ':execution_count' => $execution_count
        ]);
        $message = 'Usługa została dodana!';
    }

    $_SESSION['success_message'] = $message;
    header('Location: services.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edytuj' : 'Dodaj'; ?> Usługę - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">

    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.mce.com/1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#detailed_description',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic underline strikethrough | forecolor backcolor | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px }',
            language: 'pl'
        });
    </script>
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1><?php echo $edit_mode ? 'Edytuj' : 'Dodaj'; ?> Usługę</h1>
                <a href="services.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Powrót
                </a>
            </header>

            <div class="content-section full-width">
                <form method="POST" class="admin-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nazwa Usługi *</label>
                        <input type="text" id="name" name="name"
                            value="<?php echo $service ? htmlspecialchars($service['name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Krótki Opis (wyświetlany w ofercie)</label>
                        <textarea id="description" name="description" rows="2"
                            placeholder="Np. Profesjonalne złożenie komputera z testowaniem"><?php echo $service ? htmlspecialchars($service['description']) : ''; ?></textarea>
                        <small>Ta zawartość pojawia się na stronie oferty</small>
                    </div>

                    <div class="form-group">
                        <label for="detailed_description">Szczegółowy Opis (dla strony szczegółów usługi)</label>
                        <textarea id="detailed_description"
                            name="detailed_description"><?php echo $service ? htmlspecialchars($service['detailed_description'] ?? $service['description']) : ''; ?></textarea>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            💡 Użyj edytora do formatowania tekstu - listy, pogrubienia, kolory itp.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="image">Zdjęcie Usługi (max 50MB)</label>
                        <?php if ($service && !empty($service['image_path'])): ?>
                            <div style="margin-bottom: 15px;">
                                <img src="../<?php echo htmlspecialchars($service['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($service['name']); ?>"
                                    style="max-width: 300px; max-height: 200px; border-radius: 8px; object-fit: cover;">
                                <p style="color: #666; font-size: 0.85rem; margin-top: 5px;">Aktualne zdjęcie</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Dopuszczalne formaty: JPG, PNG, GIF, WebP (maks. rozmiar: 50MB)
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Cena Regularna (zł) *</label>
                            <input type="number" id="price" name="price" step="0.01" min="0"
                                value="<?php echo $service ? $service['price'] : '0.00'; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="discount_price">Cena Promocyjna (zł)</label>
                            <input type="number" id="discount_price" name="discount_price" step="0.01" min="0"
                                value="<?php echo $service && $service['discount_price'] ? $service['discount_price'] : ''; ?>"
                                placeholder="Opcjonalnie">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Typ Usługi *</label>
                            <select id="category" name="category" required>
                                <option value="single" <?php echo ($service && $service['category'] == 'single') ? 'selected' : ''; ?>>Usługa Pojedyncza</option>
                                <option value="package" <?php echo ($service && $service['category'] == 'package') ? 'selected' : ''; ?>>Pakiet Usług</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="display_order">Kolejność Wyświetlania</label>
                            <input type="number" id="display_order" name="display_order" min="0"
                                value="<?php echo $service ? $service['display_order'] : 0; ?>">
                            <small style="color: #666;">Niższa liczba = wyżej na liście</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="execution_count">Licznik Wykonań Usługi</label>
                        <input type="number" id="execution_count" name="execution_count" min="0"
                            value="<?php echo $service ? ($service['execution_count'] ?? 0) : 0; ?>">
                        <small style="color: #666;">Ile razy ta usługa została wykonana (wyświetlane na stronie
                            szczegółów)</small>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" <?php echo (!$service || $service['is_active']) ? 'checked' : ''; ?>>
                            <span>Usługa aktywna (widoczna na stronie)</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $edit_mode ? 'Zapisz Zmiany' : 'Dodaj Usługę'; ?>
                        </button>
                        <a href="services.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Anuluj
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>