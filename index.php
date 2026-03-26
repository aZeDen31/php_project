<?php
require 'db_config.php';

$stmt = $pdo->query("SELECT * FROM article ORDER BY publication_date DESC");
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopExpress - Votre boutique en ligne</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Nouvelle Collection 2026</h2>
                <p>Découvrez nos derniers produits modernes et de qualité</p>
            </div>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Produits en vedette</h2>
            <div class="products-grid">
                <?php if ($articles && count($articles) > 0): ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="product-card">
                            <img src="img/articles/<?php echo htmlspecialchars($article['article_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['article_name']); ?>"
                                 style="object-fit: contain;">
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($article['article_name']); ?></h3>
                                <p class="description"><?php echo htmlspecialchars(substr($article['description'], 0, 60)) . '...'; ?></p>
                                <div class="price-cart">
                                    <div class="price">
                                        <span class="new-price"><?php echo number_format($article['price'], 2); ?>€</span>
                                    </div>
                                    <button class="btn-add-cart" onclick="ajouterPanier(<?php echo $article['article_id']; ?>)">
                                    <a href="detail?id=<?php echo $article['article_id']; ?>" class="btn-link"><i class="fas fa-arrow-right"></i></a>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="product-card">
                        <div class="badge">-30%</div>
                        <img src="images/produit1.jpg" alt="Produit 1">
                        <div class="product-info">
                            <h3>Smartphone XYZ Pro</h3>
                            <p class="description">Dernier modèle avec écran AMOLED et 5G...</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span>(4.5)</span>
                            </div>
                            <div class="price-cart">
                                <div class="price">
                                    <span class="old-price">699.99€</span>
                                    <span class="new-price">489.99€</span>
                                </div>
                                <button class="btn-add-cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-card">
                        <img src="images/produit2.jpg" alt="Produit 2">
                        <div class="product-info">
                            <h3>Casque Audio Premium</h3>
                            <p class="description">Réduction de bruit active et son haute qualité...</p>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                <span>(4.0)</span>
                            </div>
                            <div class="price-cart">
                                <div class="price">
                                    <span class="new-price">149.99€</span>
                                </div>
                                <button class="btn-add-cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>
