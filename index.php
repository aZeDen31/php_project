<?php
// Connexion à la base de données php_exam_db
$mysqli = new mysqli("localhost", "root", "", "php_exam_db");

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

// Récupérer les derniers articles (basé sur la date de publication)
$query = "SELECT * FROM article ORDER BY publication_date DESC LIMIT 8";
$articles = $mysqli->query($query);
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
                <h2>Nouvelle Collection 2024</h2>
                <p>Découvrez nos derniers produits avec des réductions jusqu'à 50%</p>
                <a href="boutique.php" class="btn btn-primary">Découvrir maintenant</a>
            </div>
        </div>
    </section>

    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Catégories populaires</h2>
            <div class="categories-grid">
                <div class="category-card">
                    <img src="images/electronique.jpg" alt="Électronique">
                    <div class="category-info">
                        <h3>Électronique</h3>
                        <a href="#" class="btn-link">Voir plus <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="category-card">
                    <img src="images/mode.jpg" alt="Mode">
                    <div class="category-info">
                        <h3>Mode</h3>
                        <a href="#" class="btn-link">Voir plus <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="category-card">
                    <img src="images/maison.jpg" alt="Maison">
                    <div class="category-info">
                        <h3>Maison & Jardin</h3>
                        <a href="#" class="btn-link">Voir plus <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="category-card">
                    <img src="images/sport.jpg" alt="Sport">
                    <div class="category-info">
                        <h3>Sport</h3>
                        <a href="#" class="btn-link">Voir plus <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Produits en vedette</h2>
            <div class="products-grid">
                <?php if ($articles && $articles->num_rows > 0): ?>
                    <?php while ($article = $articles->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($article['article_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['article_name']); ?>">
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($article['article_name']); ?></h3>
                                <p class="description"><?php echo htmlspecialchars(substr($article['description'], 0, 60)) . '...'; ?></p>
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
                                        <span class="new-price"><?php echo number_format($article['price'], 2); ?>€</span>
                                    </div>
                                    <button class="btn-add-cart" onclick="ajouterPanier(<?php echo $article['article_id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
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
            
            <div class="view-more">
                <a href="boutique.php" class="btn btn-secondary">Voir tous les produits</a>
            </div>
        </div>
    </section>

    <section class="promo-banner">
        <div class="container">
            <div class="promo-content">
                <h2>Offre spéciale du jour</h2>
                <p>Profitez de -50% sur une sélection de produits</p>
                <a href="promotions.php" class="btn btn-white">J'en profite</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>À propos</h3>
                    <p>ShopExpress est votre destination en ligne pour tous vos besoins. Qualité garantie et livraison rapide.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Liens rapides</h3>
                    <ul>
                        <li><a href="#">Mon compte</a></li>
                        <li><a href="#">Mes commandes</a></li>
                        <li><a href="#">Liste de souhaits</a></li>
                        <li><a href="#">Programme de fidélité</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Service client</h3>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Livraison</a></li>
                        <li><a href="#">Retours</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Newsletter</h3>
                    <p>Inscrivez-vous pour recevoir nos offres exclusives</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Votre email">
                        <button type="submit" class="btn btn-primary">S'inscrire</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 ShopExpress. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        function ajouterPanier(produitId) {
            // Logique pour ajouter au panier
            alert('Produit ID ' + produitId + ' ajouté au panier !');
            
            // Mettre à jour le compteur du panier s'il existe dans le DOM
            let cartCount = document.querySelector('.cart-count');
            if(cartCount) {
                let count = parseInt(cartCount.textContent) || 0;
                cartCount.textContent = count + 1;
            }
        }
    </script>
</body>
</html>

<?php
$mysqli->close();
?>