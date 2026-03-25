<?php

require 'db_config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_infos = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id");
$cart_infos->execute([':user_id' => $user_id]);
$cart_infos = $cart_infos->fetchAll();

$cart_items = [];
$cart_total = 0;

foreach ($cart_infos as $item) {
    $article_id = $item['article_id'];
    $article_info = $pdo->prepare("SELECT * FROM article WHERE article_id = :article_id");
    $article_info->execute([':article_id' => $article_id]);
    $article_info = $article_info->fetch();
    if ($article_info) {
        $item_qty = isset($item['article_number']) ? (int)$item['article_number'] : 1;
        $cart_items[] = array_merge($item, $article_info);
        $cart_total += ($article_info['price'] * $item_qty);
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier – ShopExpress</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-section { padding: 4rem 0; min-height: 80vh; background-color: var(--bg-light); }
        .cart-header { margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; color: var(--text-dark); }
        .cart-header h2 { font-size: 2.2rem; }
        .cart-container { display: grid; grid-template-columns: 1fr 380px; gap: 2.5rem; align-items: start; }
        
        .cart-items { background: var(--white); border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,.04); }
        .cart-item { display: flex; align-items: center; padding: 1.5rem 0; border-bottom: 1px solid var(--border-color); gap: 1.5rem; }
        .cart-item:first-child { padding-top: 0; }
        .cart-item:last-child { border-bottom: none; padding-bottom: 0; }
        .cart-item img { width: 120px; height: 120px; object-fit: contain; border-radius: 12px; }
        .cart-item-info { flex: 1; }
        .cart-item-info h3 { font-size: 1.25rem; color: var(--text-dark); margin-bottom: 0.5rem; }
        .cart-item-info p { color: var(--text-light); font-size: 0.95rem; margin-bottom: 0.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; }
        .cart-item-price { font-size: 1.3rem; font-weight: 700; color: var(--primary-color); margin-top: 0.5rem; display: inline-block; }
        
        .cart-item-controls { display: flex; align-items: center; gap: 1.5rem; }
        .cart-item-qty { display: flex; align-items: center; background: var(--bg-light); border-radius: 10px; border: 1px solid var(--border-color); overflow: hidden; }
        .btn-qty { border: none; background: transparent; padding: 0.5rem 0.8rem; cursor: pointer; color: var(--text-dark); transition: background 0.2s; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; }
        .btn-qty:hover { background: #e5e7eb; }
        .cart-item-qty span { font-weight: 600; min-width: 35px; text-align: center; font-size: 1.1rem; color: var(--text-dark); }
        
        .btn-remove { background: #fee2e2; color: var(--danger-color); border: none; width: 45px; height: 45px; border-radius: 12px; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .btn-remove:hover { background: var(--danger-color); color: var(--white); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(239, 68, 68, 0.2); }

        .cart-summary { background: var(--white); border-radius: 16px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,.04); position: sticky; top: 100px; }
        .cart-summary h3 { font-size: 1.3rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); color: var(--text-dark); }
        .cart-summary-item { display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 1.1rem; color: var(--text-light); }
        .cart-summary-total { display: flex; justify-content: space-between; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px dashed var(--border-color); font-size: 1.4rem; font-weight: 700; color: var(--text-dark); }
        .cart-summary-total span:last-child { color: var(--primary-color); }
        
        .btn-checkout { width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 12px; margin-top: 2rem; display: flex; justify-content: center; align-items: center; gap: 0.8rem; }
        
        .empty-cart { text-align: center; padding: 5rem 2rem; background: var(--white); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.04); }
        .empty-cart i { font-size: 5rem; color: #e5e7eb; margin-bottom: 1.5rem; }
        .empty-cart h3 { font-size: 1.8rem; color: var(--text-dark); margin-bottom: 1rem; }
        .empty-cart p { font-size: 1.1rem; color: var(--text-light); margin-bottom: 2rem; }

        @media(max-width: 992px) {
            .cart-container { grid-template-columns: 1fr; }
            .cart-summary { position: static; }
        }
        @media(max-width: 576px) {
            .cart-item { flex-direction: column; text-align: center; }
            .cart-item img { width: 100%; height: 200px; }
            .cart-item-info { margin: 1rem 0; }
        }
    </style>
</head>
<body>
    
    <?php include 'header.php'; ?>

    <section class="cart-section">
        <div class="container">
            <div class="cart-header">
                <h2><i class="fas fa-shopping-cart"></i> Mon panier</h2>
            </div>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>Votre panier est vide</h3>
                    <p>Découvrez nos produits et trouvez votre bonheur !</p>
                    <a href="/php-exam/php_project/" class="btn btn-primary">
                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Retourner à la boutique
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <img src="img/articles/<?php echo htmlspecialchars($item['article_image']); ?>"
                                     alt="<?php echo htmlspecialchars($item['article_name']); ?>"
                                     onerror="this.src='https://placehold.co/200x200?text=Image'">
                                <div class="cart-item-info">
                                    <h3><?php echo htmlspecialchars($item['article_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                                    <div class="cart-item-price"><?php echo number_format($item['price'], 2); ?> €</div>
                                </div>
                                
                                <div class="cart-item-controls">
                                    <div class="cart-item-qty">
                                        <button class="btn-qty" onclick="updateQty(<?php echo $item['article_id']; ?>, -1)" title="Diminuer">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <span><?php echo isset($item['article_number']) ? htmlspecialchars($item['article_number']) : 1; ?></span>
                                        <button class="btn-qty" onclick="updateQty(<?php echo $item['article_id']; ?>, 1)" title="Augmenter">
                                            <i class="fas fa-chevron-up"></i>
                                        </button>
                                    </div>
                                    <button class="btn-remove" onclick="removeFromCart(<?php echo $item['article_id']; ?>)" title="Retirer du panier">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="cart-summary">
                        <h3>Résumé de la commande</h3>
                        <div class="cart-summary-item">
                            <span>Sous-total</span>
                            <span><?php echo number_format($cart_total, 2); ?> €</span>
                        </div>
                        <div class="cart-summary-item">
                            <span>Livraison</span>
                            <span>Gratuite</span>
                        </div>
                        <div class="cart-summary-total">
                            <span>Total</span>
                            <span><?php echo number_format($cart_total, 2); ?> €</span>
                        </div>
                        <a href="cart/validate" class="btn btn-primary btn-checkout" style="text-decoration: none;">
                            <i class="fas fa-credit-card"></i> Passer la commande
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>


    <script>
        function updateQty(article_id, delta) {
            window.location.href = 'update_cart.php?id=' + article_id + '&delta=' + delta;
        }

        function removeFromCart(article_id) {
            window.location.href = 'remove_from_cart.php?id=' + article_id;
        }
    </script>
</body>
</html>