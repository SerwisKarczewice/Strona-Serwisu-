<?php 
require_once 'config.php';

// Pobierz wszystkie zdjęcia z galerii
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY display_order ASC, uploaded_at DESC");
$gallery_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Galeria zdjęć naszego serwisu komputerowego - zobacz zrealizowane projekty, naprawy i złożone zestawy PC.">
    <meta name="keywords" content="galeria serwis komputerowy, zrealizowane projekty, zdjęcia napraw PC">
    <title>Galeria - Nasze Realizacje | TechService</title>
    <link rel="canonical" href="https://twojadomena.pl/galeria.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="Galeria - Nasze Realizacje | TechService">
    <meta property="og:description" content="Galeria zdjęć naszego serwisu komputerowego - zobacz zrealizowane projekty, naprawy i złożone zestawy PC.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://twojadomena.pl/galeria.php">
    <meta property="og:image" content="https://twojadomena.pl/images/galeria-og.jpg">
    <meta property="og:locale" content="pl_PL">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <section class="page-hero">
        <div class="container">
            <h1>Galeria</h1>
            <p>Zobacz nasze najlepsze realizacje i projekty</p>
        </div>
    </section>

    <section class="gallery-section">
        <div class="container">
            <div class="gallery-filter">
                <button class="filter-btn active" data-filter="all">Wszystkie</button>
                <button class="filter-btn" data-filter="builds">Zestawy PC</button>
                <button class="filter-btn" data-filter="repairs">Naprawy</button>
                <button class="filter-btn" data-filter="workshop">Warsztat</button>
            </div>

            <div class="gallery-grid">
                <?php foreach ($gallery_items as $item): ?>
                <div class="gallery-item" data-category="<?php echo htmlspecialchars($item['category']); ?>">
                    <div class="gallery-image">
                        <?php if ($item['image_path'] && file_exists($item['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <i class="fas fa-image"></i>
                        <?php endif; ?>
                    </div>
                    <div class="gallery-overlay">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <?php if ($item['description']): ?>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        <button class="btn-view" 
                                data-image="<?php echo htmlspecialchars($item['image_path']); ?>"
                                data-title="<?php echo htmlspecialchars($item['title']); ?>" 
                                data-desc="<?php echo htmlspecialchars($item['description'] ?: 'Brak opisu'); ?>">
                            <i class="fas fa-search-plus"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($gallery_items)): ?>
                <div class="empty-gallery">
                    <i class="fas fa-images"></i>
                    <h3>Galeria będzie wkrótce dostępna</h3>
                    <p>Pracujemy nad dodaniem zdjęć naszych realizacji</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div id="lightbox" class="lightbox">
        <span class="lightbox-close">&times;</span>
        <div class="lightbox-content">
            <img id="lightbox-image" src="" alt="">
            <h3 id="lightbox-title">Tytuł</h3>
            <p id="lightbox-desc">Opis</p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtrowanie galerii
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.dataset.filter;
                    document.querySelectorAll('.gallery-item').forEach(item => {
                        if (filter === 'all' || item.dataset.category === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });

            // Lightbox
            const lightbox = document.getElementById('lightbox');
            const lightboxClose = document.querySelector('.lightbox-close');
            const lightboxImage = document.getElementById('lightbox-image');
            
            document.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const image = this.dataset.image;
                    const title = this.dataset.title;
                    const desc = this.dataset.desc;
                    
                    lightboxImage.src = image;
                    lightboxImage.alt = title;
                    document.getElementById('lightbox-title').textContent = title;
                    document.getElementById('lightbox-desc').textContent = desc;
                    lightbox.style.display = 'flex';
                });
            });

            lightboxClose.addEventListener('click', () => {
                lightbox.style.display = 'none';
            });

            lightbox.addEventListener('click', (e) => {
                if (e.target === lightbox) {
                    lightbox.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<style>
.gallery-section {
    padding: 80px 0;
    background: var(--light-color);
}

.gallery-filter {
    display: flex;
    gap: 15px;
    margin-bottom: 40px;
    flex-wrap: wrap;
    justify-content: center;
}

.filter-btn {
    padding: 12px 25px;
    border: 2px solid var(--primary-color);
    background: transparent;
    color: var(--primary-color);
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--primary-color);
    color: #fff;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.gallery-item {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    cursor: pointer;
    aspect-ratio: 4/3;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.gallery-image {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-image i {
    font-size: 5rem;
    color: rgba(255, 255, 255, 0.8);
}

.gallery-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
    padding: 30px 20px;
    color: #fff;
    transform: translateY(10px);
    opacity: 0;
    transition: all 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    transform: translateY(0);
    opacity: 1;
}

.gallery-overlay h3 {
    font-size: 1.3rem;
    margin-bottom: 8px;
}

.gallery-overlay p {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 15px;
}

.btn-view {
    background: var(--gradient-primary);
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-view:hover {
    transform: scale(1.1);
}

.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.lightbox-close {
    position: absolute;
    top: 30px;
    right: 40px;
    font-size: 3rem;
    color: #fff;
    cursor: pointer;
    transition: color 0.3s ease;
}

.lightbox-close:hover {
    color: var(--primary-color);
}

.lightbox-content {
    text-align: center;
    color: #fff;
    max-width: 90vw;
}

.lightbox-content img {
    max-width: 100%;
    max-height: 70vh;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
}

.lightbox-content h3 {
    font-size: 2rem;
    margin-bottom: 15px;
}

.lightbox-content p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.empty-gallery {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
}

.empty-gallery i {
    font-size: 5rem;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-gallery h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.empty-gallery p {
    color: #666;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .lightbox-content img {
        max-height: 50vh;
    }
    
    .lightbox-content h3 {
        font-size: 1.5rem;
    }
}
</style>