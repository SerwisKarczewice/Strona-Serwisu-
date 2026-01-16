<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$edit_mode = isset($_GET['id']);
$news = null;

if ($edit_mode) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $news = $stmt->fetch();

    if (!$news) {
        header('Location: news.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $excerpt = trim($_POST['excerpt']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $title))));
    $published = isset($_POST['published']) ? 1 : 0;

    if ($edit_mode) {
        $stmt = $pdo->prepare("UPDATE news SET title = :title, content = :content, excerpt = :excerpt, slug = :slug, published = :published, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':excerpt' => $excerpt,
            ':slug' => $slug,
            ':published' => $published,
            ':id' => $_GET['id']
        ]);
        $message = 'Aktualność została zaktualizowana!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, excerpt, slug, published, created_at, author_id) VALUES (:title, :content, :excerpt, :slug, :published, NOW(), :author_id)");
        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':excerpt' => $excerpt,
            ':slug' => $slug,
            ':published' => $published,
            ':author_id' => $_SESSION['admin_id']
        ]);
        $message = 'Aktualność została dodana!';
    }

    $_SESSION['success_message'] = $message;
    header('Location: news.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edytuj' : 'Dodaj'; ?> Aktualność - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1><?php echo $edit_mode ? 'Edytuj' : 'Dodaj'; ?> Aktualność</h1>
                <a href="news.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Powrót
                </a>
            </header>

            <div class="content-section full-width">
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="title">Tytuł Aktualności *</label>
                        <input type="text" id="title" name="title"
                            value="<?php echo $news ? htmlspecialchars($news['title']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Krótki Opis (opcjonalnie)</label>
                        <textarea id="excerpt" name="excerpt" rows="3"
                            placeholder="Krótki opis wyświetlany na liście aktualności"><?php echo $news ? htmlspecialchars($news['excerpt']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Treść Aktualności *</label>
                        <textarea id="content" name="content" rows="15"
                            required><?php echo $news ? htmlspecialchars($news['content']) : ''; ?></textarea>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="published" <?php echo ($news && $news['published']) ? 'checked' : ''; ?>>
                            <span>Opublikuj aktualność</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $edit_mode ? 'Zapisz Zmiany' : 'Dodaj Aktualność'; ?>
                        </button>
                        <a href="news.php" class="btn btn-secondary">
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
    .admin-form {
        max-width: 900px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff6b35;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: 600;
    }

    .checkbox-label input {
        width: auto;
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 2px solid #ecf0f1;
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 25px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
</style>