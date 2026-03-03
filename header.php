<?php
if (session_status() === PHP_SESSION_NONE) {
    // Définir une très longue durée de vie pour la session et le cookie (10 ans)
    // Cela permet de rester connecté tant que la session n'est pas détruite sur le serveur
    ini_set('session.gc_maxlifetime', 315360000);
    ini_set('session.cookie_lifetime', 315360000);
    session_start();
}
require_once 'db_config.php';

// 1. Initialisation par défaut pour TOUS les cas (non connecté ou erreur)
$isLoggedIn = false;
$userPhoto = 'default.jpg';
$username = "test";

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    try {
        $stmt = $pdo->prepare("SELECT profile_picture, username FROM user WHERE user_id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        $username = $user['username'];
        
        
        // Si l'utilisateur a une photo en BDD, on l'utilise, sinon on garde default.jpg
        if ($user && !empty($user['profile_picture'])) {
            $userPhoto = $user['profile_picture'];
        }
    } catch (PDOException $e) {
        // En cas d'erreur, $userPhoto reste 'default.jpg'
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopExpress</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><a href="/php-exam/php_project" style="text-decoration:none; color:inherit;"><i class="fas fa-shopping-bag"></i> ShopExpress</a></h1>
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

                    <?php if ($isLoggedIn): ?>
                        <a href="compte.php" class="icon-link profile-active header-avatar">
                            <img src="img/<?php echo htmlspecialchars($userPhoto); ?>" alt="Profil" class="header-avatar">
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="icon-link header-avatar">
                            <img src="img/<?php echo htmlspecialchars($userPhoto); ?>" alt="Connexion" class="header-avatar">
                        </a>
                    <?php endif; ?>

                    <a href="panier" class="icon-link cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>