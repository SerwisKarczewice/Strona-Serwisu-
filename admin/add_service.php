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
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $category = $_POST['category'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $display_order = intval($_POST['display_order']);

    if ($edit_mode) {
        $stmt = $pdo->prepare("UPDATE services SET name = :name, description = :description, price = :price, discount_price = :discount_price, category = :category, is_active = :is_active, display_order = :display_order, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':discount_price' => $discount_price,
            ':category' => $category,
            ':is_active' => $is_active,
            ':display_order' => $display_order,
            ':id' => $_GET['id']
        ]);
        $message = 'Usługa została zaktualizowana!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, price, discount_price, category, is_active, display_order, created_at) VALUES (:name, :description, :price, :discount_price, :category, :is_active, :display_order, NOW())");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':discount_price' => $discount_price,
            ':category' => $category,
            ':is_active' => $is_active,
            ':display_order' => $display_order
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
</head>

<body>
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
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="name">Nazwa Usługi *</label>
                        <input type="text" id="name" name="name"
                            value="<?php echo $service ? htmlspecialchars($service['name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Opis Usługi</label>
                        <textarea id="description" name="description" rows="3"
                            placeholder="Krótki opis usługi wyświetlany na stronie"><?php echo $service ? htmlspecialchars($service['description']) : ''; ?></textarea>
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