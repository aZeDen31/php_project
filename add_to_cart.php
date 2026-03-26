<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['qty'])) {
    $article_id = (int)$_GET['id'];
    $qty = (int)$_GET['qty'];
    $user_id = $_SESSION['user_id'];

    if ($qty <= 0) {
        header('Location: detail.php?id=' . $article_id);
        exit;
    }

    $stmtStock = $pdo->prepare("SELECT actual_stock FROM stock WHERE article_id = :id");
    $stmtStock->execute([':id' => $article_id]);
    $stockRow = $stmtStock->fetch();
    $actual_stock = $stockRow ? (int)$stockRow['actual_stock'] : 0;

    $stmtCart = $pdo->prepare("SELECT cart_id, article_number FROM cart WHERE user_id = :user_id AND article_id = :article_id");
    $stmtCart->execute([':user_id' => $user_id, ':article_id' => $article_id]);
    $cartRow = $stmtCart->fetch();

    if ($cartRow) {
        $new_qty = $cartRow['article_number'] + $qty;
        $new_qty = min($new_qty, $actual_stock);
        if ($new_qty > 0) {
            $stmtUpdate = $pdo->prepare("UPDATE cart SET article_number = :qty WHERE cart_id = :cart_id");
            $stmtUpdate->execute([':qty' => $new_qty, ':cart_id' => $cartRow['cart_id']]);
        }
    } else {
        $qty = min($qty, $actual_stock);
        if ($qty > 0) {
            $stmtInsert = $pdo->prepare("INSERT INTO cart (user_id, article_id, article_number) VALUES (:user_id, :article_id, :article_number)");
            $stmtInsert->execute([
                ':user_id' => $user_id, 
                ':article_id' => $article_id, 
                ':article_number' => $qty
            ]);
        }
    }

    if (isset($_GET['buy']) && $_GET['buy'] == 1) {
        header('Location: cart.php'); 
    } else {
        header('Location: detail.php?id=' . $article_id); 
    }
    exit;
} else {
    header('Location: index.php');
    exit;
}
?>