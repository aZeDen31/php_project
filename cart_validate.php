<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$success = false;

// 1. Récupérer les articles du panier pour calculer le total
$cart_infos = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id");
$cart_infos->execute([':user_id' => $user_id]);
$cart_infos = $cart_infos->fetchAll();

if (empty($cart_infos)) {
    header('Location: ../cart'); // Redirige si le panier est vide
    exit;
}

$cart_items = [];
$cart_total = 0;

foreach ($cart_infos as $item) {
    $article_id = $item['article_id'];
    $article_info = $pdo->prepare("SELECT * FROM article WHERE article_id = :article_id");
    $article_info->execute([':article_id' => $article_id]);
    $article_info = $article_info->fetch();
    
    if ($article_info) {
        $item_qty = isset($item['article_number']) ? (int)$item['article_number'] : 1;
        
        $stmtStock = $pdo->prepare("SELECT actual_stock FROM stock WHERE article_id = :id");
        $stmtStock->execute([':id' => $article_id]);
        $stockRow = $stmtStock->fetch();
        $actual_stock = $stockRow ? (int)$stockRow['actual_stock'] : 0;
        
        $cart_items[] = array_merge($item, $article_info, ['purchased_qty' => $item_qty, 'actual_stock' => $actual_stock]);
        $cart_total += ($article_info['price'] * $item_qty);
    }
}

$stmt_user = $pdo->prepare("SELECT solde FROM user WHERE user_id = :id");
$stmt_user->execute([':id' => $user_id]);
$user = $stmt_user->fetch();

if (!$user) {
    header('Location: ../login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_order'])) {
    $address = htmlspecialchars($_POST['address']);
    $city = htmlspecialchars($_POST['city']);
    $postal_code = (int)$_POST['postal_code'];

    if (empty($address) || empty($city) || empty($postal_code)) {
        $message = "Veuillez remplir tous les champs de livraison.";
    } elseif ($user['solde'] < $cart_total) {
        $message = "Solde insuffisant. <a href='account.php#solde-section'>Rechargez votre compte</a>.";
    } else {
        $stock_error = false;
        foreach ($cart_items as $item) {
            if ($item['purchased_qty'] > $item['actual_stock']) {
                $stock_error = true;
                $message = "L'article <b>" . htmlspecialchars($item['article_name']) . "</b> n'a pas assez de stock (" . $item['actual_stock'] . " disponible(s)). Veuillez modifier votre panier.";
                break;
            }
        }
        
        if (!$stock_error) {
            try {
                $pdo->beginTransaction();

                $new_solde = $user['solde'] - $cart_total;
                $update_buyer = $pdo->prepare("UPDATE user SET solde = :solde WHERE user_id = :user_id");
                $update_buyer->execute([':solde' => $new_solde, ':user_id' => $user_id]);

                foreach ($cart_items as $item) {
                    $seller_id = $item['autor_id'];
                    $item_total = $item['price'] * $item['purchased_qty'];
                    
                    $update_seller = $pdo->prepare("UPDATE user SET solde = solde + :amt WHERE user_id = :seller_id");
                    $update_seller->execute([':amt' => $item_total, ':seller_id' => $seller_id]);

                    $update_stock = $pdo->prepare("UPDATE stock SET actual_stock = actual_stock - :qty WHERE article_id = :article_id");
                    $update_stock->execute([':qty' => $item['purchased_qty'], ':article_id' => $item['article_id']]);
                }

                $insert_invoice = $pdo->prepare("INSERT INTO invoice (user_id, transaction_date, amount, invoice_address, invoice_city, postal_code) VALUES (:user_id, :t_date, :amount, :address, :city, :pc)");
                $insert_invoice->execute([
                    ':user_id' => $user_id,
                    ':t_date' => date('Y-m-d'),
                    ':amount' => $cart_total,
                    ':address' => $address,
                    ':city' => $city,
                    ':pc' => $postal_code
                ]);

                // D) Vider le panier
                $clear_cart = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
                $clear_cart->execute([':user_id' => $user_id]);

                $pdo->commit();
                $success = true;

            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Une erreur technique s'est produite lors du paiement.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation et Paiement - ShopExpress</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Base url fix pour le virtual directory /cart/ -->
    <base href="../">
    <style>
        .checkout-section { padding: 4rem 0; min-height: 80vh; background-color: var(--bg-light); }
        .checkout-container { display: grid; grid-template-columns: 1fr 400px; gap: 2rem; align-items: start; }
        .checkout-box { background: var(--white); padding: 2.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.04); }
        .checkout-box h2 { font-size: 1.6rem; color: var(--text-dark); margin-bottom: 2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 1rem; }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark); }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem; }
        .form-control:focus { border-color: var(--primary-color); outline: none; }
        
        .order-recap { background: var(--white); padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,.04); position: sticky; top: 100px; }
        .order-recap h3 { font-size: 1.3rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); }
        .recap-item { display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.95rem; color: var(--text-light); }
        .recap-total { display: flex; justify-content: space-between; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px dashed var(--border-color); font-size: 1.4rem; font-weight: 700; color: var(--text-dark); }
        .recap-total span:last-child { color: var(--primary-color); }
        
        .solde-info { display: flex; align-items: center; justify-content: space-between; background: #eff6ff; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; }
        .solde-info p { color: var(--secondary-color); font-weight: 600; font-size: 1.1rem; margin: 0; }
        
        .checkout-msg { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
        .checkout-msg.error { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        
        .success-page { text-align: center; padding: 4rem 2rem; }
        .success-page i { font-size: 5rem; color: var(--success-color); margin-bottom: 1.5rem; }
        .success-page h2 { font-size: 2rem; color: var(--text-dark); margin-bottom: 1rem; }
        .success-page p { font-size: 1.1rem; color: var(--text-light); margin-bottom: 2rem; }

        @media(max-width: 992px) {
            .checkout-container { grid-template-columns: 1fr; }
            .order-recap { position: static; }
        }
    </style>
</head>
<body>
    
    <?php include 'header.php'; ?>

    <section class="checkout-section">
        <div class="container">
            <?php if ($success): ?>
                <div class="checkout-box success-page">
                    <i class="fas fa-check-circle"></i>
                    <h2>Commande validée avec succès !</h2>
                    <p>Votre livraison est en cours de préparation. Le solde du vendeur a été crédité.</p>
                    <a href="account.php#commandes" class="btn btn-primary">Voir mes commandes</a>
                </div>
            <?php else: ?>
                
                <div class="checkout-container">
                    <!-- Formulaire -->
                    <div class="checkout-box">
                        <h2><i class="fas fa-truck"></i> Adresse de livraison</h2>
                        
                        <?php if (!empty($message)): ?>
                            <div class="checkout-msg error"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <div class="solde-info">
                            <p><i class="fas fa-wallet"></i> Votre Solde: <?php echo number_format($user['solde'], 2); ?> €</p>
                            <?php if ($user['solde'] < $cart_total): ?>
                                <span style="color: #b91c1c; font-weight: bold; font-size: 0.9rem;">(Fonds insuffisants)</span>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="cart/validate">
                            <div class="form-group">
                                <label for="address">Adresse complète</label>
                                <input type="text" id="address" name="address" class="form-control" placeholder="123 rue de la Paix" required>
                            </div>
                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="city">Ville</label>
                                    <input type="text" id="city" name="city" class="form-control" placeholder="Paris" required>
                                </div>
                                <div class="form-group">
                                    <label for="postal_code">Code Postal</label>
                                    <input type="number" id="postal_code" name="postal_code" class="form-control" placeholder="75000" required>
                                </div>
                            </div>
                            <button type="submit" name="pay_order" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;" <?php echo ($user['solde'] < $cart_total) ? 'disabled style="opacity:0.6; cursor:not-allowed;"' : ''; ?>>
                                <i class="fas fa-lock"></i> Payer et Valider
                            </button>
                        </form>
                    </div>

                    <!-- Récapitulatif -->
                    <div class="order-recap">
                        <h3>Récapitulatif</h3>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="recap-item">
                                <span><?php echo htmlspecialchars($item['article_name']); ?> (x<?php echo $item['purchased_qty']; ?>)</span>
                                <span><?php echo number_format($item['price'] * $item['purchased_qty'], 2); ?> €</span>
                            </div>
                        <?php endforeach; ?>
                        <div class="recap-total">
                            <span>Total à payer</span>
                            <span><?php echo number_format($cart_total, 2); ?> €</span>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>
