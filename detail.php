<?php
require 'db_config.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$article_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT a.*, u.username AS author_name 
    FROM article a 
    JOIN user u ON a.autor_id = u.user_id 
    WHERE a.article_id = :id
");
$stmt->execute([':id' => $article_id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: index.php');
    exit;
}

// Articles similaires (derniers publiés hors l'article courant)
$stmtSim = $pdo->prepare("SELECT * FROM article WHERE article_id != :id ORDER BY publication_date DESC LIMIT 4");
$stmtSim->execute([':id' => $article_id]);
$similar = $stmtSim->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['article_name']); ?> – ShopExpress</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-section { padding: 3rem 0; background: var(--bg-light); min-height: 80vh; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; background: var(--white); border-radius: 16px; padding: 2.5rem; box-shadow: 0 4px 15px rgba(0,0,0,.07); }
        .detail-image img { width: 100%; border-radius: 12px; object-fit: contain; max-height: 420px; }
        .detail-info h1 { font-size: 1.8rem; margin-bottom: 1rem; }
        .detail-price { font-size: 2rem; font-weight: 700; color: var(--primary-color); margin: 1rem 0; }
        .detail-meta { display: flex; gap: 1.5rem; color: var(--text-light); font-size: .9rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .detail-desc { color: var(--text-dark); line-height: 1.8; margin-bottom: 2rem; }
        .qty-selector { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .qty-selector button { width: 36px; height: 36px; border: 2px solid var(--border-color); background: white; border-radius: 8px; font-size: 1.2rem; cursor: pointer; transition: all .2s; }
        .qty-selector button:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .qty-selector span { font-size: 1.2rem; font-weight: 600; min-width: 30px; text-align: center; }
        .detail-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .breadcrumb { margin-bottom: 1.5rem; font-size: .9rem; color: var(--text-light); }
        .breadcrumb a { color: var(--primary-color); text-decoration: none; }
        .similar-section { margin-top: 3rem; }
        .similar-section h2 { font-size: 1.5rem; margin-bottom: 1.5rem; }
        @media(max-width:768px){ .detail-grid{ grid-template-columns:1fr; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="detail-section">
    <div class="container">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Accueil</a> &rsaquo; 
            <a href="index.php">Boutique</a> &rsaquo; 
            <?php echo htmlspecialchars($article['article_name']); ?>
        </div>

        <!-- Article principal -->
        <div class="detail-grid">
            <div class="detail-image">
                <img src="img/articles/<?php echo htmlspecialchars($article['article_image']); ?>"
                     alt="<?php echo htmlspecialchars($article['article_name']); ?>"
                     onerror="this.src='https://placehold.co/600x420?text=Image+indisponible'">
            </div>

            <div class="detail-info">
                <h1><?php echo htmlspecialchars($article['article_name']); ?></h1>

                <div class="detail-meta">
                    <span><i class="fas fa-user"></i> <a href="account.php?id=<?php echo $article['autor_id']; ?>" style="color: inherit; text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars($article['author_name']); ?></a></span>
                    <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($article['publication_date']); ?></span>
                    <div class="rating" style="display:inline-flex;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="far fa-star"></i>
                        <span style="margin-left:.3rem;">(4.0)</span>
                    </div>
                </div>

                <div class="detail-price"><?php echo number_format($article['price'], 2); ?> €</div>

                <p class="detail-desc"><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>

                <div class="qty-selector">
                    <span>Quantité :</span>
                    <button onclick="changeQty(-1)">−</button>
                    <span id="qty">1</span>
                    <button onclick="changeQty(1)">+</button>
                </div>

                <div class="detail-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-primary" onclick="addToCart(<?php echo $article['article_id']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                        </button>
                        <button class="btn btn-secondary" onclick="buyNow(<?php echo $article['article_id']; ?>)">
                            <i class="fas fa-bolt"></i> Acheter maintenant
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Se connecter pour acheter
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article['autor_id']): ?>
                    <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border-color);">
                        <a href="edit.php?article_id=<?php echo $article['article_id']; ?>" class="btn btn-secondary" style="font-size:.9rem;">
                            <i class="fas fa-edit"></i> Modifier cet article
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Articles similaires -->
        <?php if (!empty($similar)): ?>
        <div class="similar-section">
            <h2 class="section-title">Vous aimerez aussi</h2>
            <div class="products-grid">
                <?php foreach ($similar as $s): ?>
                    <div class="product-card">
                        <img src="img/articles/<?php echo htmlspecialchars($s['article_image']); ?>"
                             alt="<?php echo htmlspecialchars($s['article_name']); ?>"
                             onerror="this.src='https://placehold.co/280x250?text=Image'">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($s['article_name']); ?></h3>
                            <p class="description"><?php echo htmlspecialchars(substr($s['description'], 0, 60)); ?>...</p>
                            <div class="price-cart">
                                <span class="new-price"><?php echo number_format($s['price'], 2); ?> €</span>
                                <a href="detail.php?id=<?php echo $s['article_id']; ?>" class="btn-add-cart">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php include 'footer.php'; ?>

<script>
let qty = 1;

function changeQty(delta) {
    qty = Math.max(1, qty + delta);
    document.getElementById('qty').textContent = qty;
}

function addToCart(id) {
    window.location.href = 'add_to_cart.php?id=' + id + '&qty=' + qty;
}

function buyNow(id) {
    window.location.href = 'add_to_cart.php?id=' + id + '&qty=' + qty + '&buy=1';
}
</script>
</body>
</html>