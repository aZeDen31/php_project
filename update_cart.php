<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['delta'])) {
    $article_id = (int)$_GET['id'];
    $delta = (int)$_GET['delta'];
    $user_id = $_SESSION['user_id'];

    $stmtStock = $pdo->prepare("SELECT actual_stock FROM stock WHERE article_id = :id");
    $stmtStock->execute([':id' => $article_id]);
    $stockRow = $stmtStock->fetch();
    $actual_stock = $stockRow ? (int)$stockRow['actual_stock'] : 0;

    $stmt = $pdo->prepare("SELECT cart_id, article_number FROM cart WHERE user_id = :user_id AND article_id = :article_id");
    $stmt->execute([':user_id' => $user_id, ':article_id' => $article_id]);
    $row = $stmt->fetch();

    if ($row) {
        $new_qty = (int)$row['article_number'] + $delta;
        $new_qty = min($new_qty, $actual_stock);
        
        if ($new_qty > 0) {
            $update = $pdo->prepare("UPDATE cart SET article_number = :qty WHERE cart_id = :cart_id");
            $update->execute([':qty' => $new_qty, ':cart_id' => $row['cart_id']]);
        } elseif ($new_qty <= 0) {
            $delete = $pdo->prepare("DELETE FROM cart WHERE cart_id = :cart_id");
            $delete->execute([':cart_id' => $row['cart_id']]);
        }
    }
}

header('Location: cart.php');
exit;
?>
