<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $display_order = intval($_POST['display_order']);

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
            $upload_path = '../uploads/gallery/';
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            // Wygeneruj unikalną nazwę
            $new_filename = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
            $full_path = $upload_path . $new_filename;

            // Przenieś plik
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
                $image_path = 'uploads/gallery/' . $new_filename;

                // Zapisz do bazy danych
                try {
                    $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path, category, display_order, uploaded_at) VALUES (:title, :description, :image_path, :category, :display_order, NOW())");
                    $stmt->execute([
                        ':title' => $title,
                        ':description' => $description,
                        ':image_path' => $image_path,
                        ':category' => $category,
                        ':display_order' => $display_order
                    ]);

                    header('Location: gallery.php?success=added');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Błąd bazy danych: ' . $e->getMessage();
                    // Usuń przesłany plik
                    if (file_exists($full_path)) {
                        unlink($full_path);
                    }
                }
            } else {
                $error = 'Błąd podczas przesyłania pliku. Sprawdź uprawnienia do zapisu.';
            }
        }
    } else {
        $error = 'Nie wybrano pliku lub wystąpił błąd podczas przesyłania.';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Zdjęcie - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Dodaj Zdjęcie</h1>
                <a href="gallery.php" class="btn btn-secondary">
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
                        <label for="title">Tytuł Zdjęcia *</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Opis</label>
                        <textarea id="description" name="description" rows="4"
                            placeholder="Opcjonalny opis zdjęcia"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Kategoria</label>
                            <select id="category" name="category">
                                <option value="builds">Zestawy PC</option>
                                <option value="repairs">Naprawy</option>
                                <option value="workshop">Warsztat</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="display_order">Kolejność wyświetlania</label>
                            <input type="number" id="display_order" name="display_order" value="0" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Zdjęcie * (maks. 5MB)</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                        <small>Akceptowane formaty: JPG, PNG, GIF, WEBP</small>
                        <div id="imagePreview" style="margin-top: 15px;"></div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Dodaj Zdjęcie
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
                            <img src="${e.target.result}" style="max-width: 400px; max-height: 400px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
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
    </style>
</body>

</html>