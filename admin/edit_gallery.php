<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$item = null;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $item = $stmt->fetch();

    if (!$item) {
        header('Location: gallery.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $display_order = intval($_POST['display_order']);

    $image_path = $item['image_path'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
            $upload_path = '../../GalleryPhotos/';

            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $new_filename)) {
                if ($item['image_path'] && $item['image_path'] !== 'placeholder.jpg' && file_exists('../../' . $item['image_path'])) {
                    unlink('../../' . $item['image_path']);
                }
                $image_path = 'GalleryPhotos/' . $new_filename;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE gallery SET title = :title, description = :description, category = :category, display_order = :display_order, image_path = :image_path WHERE id = :id");
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':category' => $category,
        ':display_order' => $display_order,
        ':image_path' => $image_path,
        ':id' => $_GET['id']
    ]);

    header('Location: gallery.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj Zdjęcie - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Edytuj Zdjęcie</h1>
                <a href="gallery.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Powrót
                </a>
            </header>

            <div class="content-section full-width">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-group">
                        <label for="title">Tytuł Zdjęcia *</label>
                        <input type="text" id="title" name="title"
                            value="<?php echo htmlspecialchars($item['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Opis</label>
                        <textarea id="description" name="description" rows="4"
                            placeholder="Opcjonalny opis zdjęcia"><?php echo htmlspecialchars($item['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Kategoria</label>
                            <select id="category" name="category">
                                <option value="builds" <?php echo ($item['category'] == 'builds') ? 'selected' : ''; ?>>
                                    Zestawy PC</option>
                                <option value="repairs" <?php echo ($item['category'] == 'repairs') ? 'selected' : ''; ?>>
                                    Naprawy</option>
                                <option value="workshop" <?php echo ($item['category'] == 'workshop') ? 'selected' : ''; ?>>Warsztat</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="display_order">Kolejność wyświetlania</label>
                            <input type="number" id="display_order" name="display_order"
                                value="<?php echo $item['display_order']; ?>" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Zdjęcie</label>
                        <?php if ($item['image_path'] && $item['image_path'] !== 'placeholder.jpg'): ?>
                            <div class="current-image">
                                <img src="../../<?php echo htmlspecialchars($item['image_path']); ?>"
                                    alt="Aktualne zdjęcie">
                                <p>Aktualne zdjęcie</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Akceptowane formaty: JPG, PNG, GIF, WEBP (max 5MB)</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Zapisz Zmiany
                        </button>
                        <a href="gallery.php" class="btn btn-secondary">
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

<style>
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

    .current-image {
        margin-bottom: 15px;
        text-align: center;
    }

    .current-image img {
        max-width: 400px;
        max-height: 400px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .current-image p {
        margin-top: 10px;
        color: #666;
        font-size: 0.9rem;
    }
</style>