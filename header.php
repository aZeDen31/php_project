<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-shopping-bag"></i> ShopExpress</h1>
                </div>
                
                <nav class="nav">
                    <ul>
                        <li><a href="/php-exam/php_project">Accueil</a></li>
                        <li><a href="boutique.php">Boutique</a></li>
                        <li><a href="categories.php">Catégories</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="panier">Panier</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Rechercher un produit...">
                        <button><i class="fas fa-search"></i></button>
                    </div>
                    <a href="compte.php" class="icon-link">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="panier" class="icon-link cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
</body>
</html>