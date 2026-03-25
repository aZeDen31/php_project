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

    $stmt = $pdo->prepare("SELECT article_number FROM cart WHERE user_id = :user_id AND article_id = :article_id");
    $stmt->execute([':user_id' => $user_id, ':article_id' => $article_id]);
    $row = $stmt->fetch();

    if ($row) {
        $new_qty = (int)$row['article_number'] + $delta;
        
        if ($new_qty > 0) {
            $update = $pdo->prepare("UPDATE cart SET article_number = :qty WHERE user_id = :user_id AND article_id = :article_id");
            $update->execute([':qty' => $new_qty, ':user_id' => $user_id, ':article_id' => $article_id]);
        }
    }
}

header('Location: cart.php');
exit;
?>
