<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 315360000);
    ini_set('session.cookie_lifetime', 315360000);
    session_start();
}
require_once 'db_config.php';

$isLoggedIn = false;
$userPhoto = 'default.jpg';
$username = "test";

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    try {
        $stmt = $pdo->prepare("SELECT profile_picture, username FROM user WHERE user_id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $userHeader = $stmt->fetch();
        $username = $userHeader['username'];
        if ($userHeader && !empty($userHeader['profile_picture'])) {
            $userPhoto = $userHeader['profile_picture'];
        }
    } catch (PDOException $e) {
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
                        <li><a href="cart">Panier</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">

                    <?php if ($isLoggedIn): ?>
                        <a href="account" class="icon-link profile-active header-avatar">
                            <img src="img/<?php echo htmlspecialchars($userPhoto); ?>" alt="Profil" class="header-avatar">
                        </a>
                    <?php else: ?>
                        <a href="login" class="icon-link header-avatar">
                            <img src="img/<?php echo htmlspecialchars($userPhoto); ?>" alt="Connexion" class="header-avatar">
                        </a>
                    <?php endif; ?>

                    <a href="cart" class="icon-link cart">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>