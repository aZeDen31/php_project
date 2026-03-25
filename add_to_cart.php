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

    $stmt = $pdo->prepare("INSERT INTO cart (user_id, article_id, article_number) VALUES (:user_id, :article_id, :article_number)");
    $stmt->execute([
        ':user_id' => $user_id, 
        ':article_id' => $article_id, 
        ':article_number' => $qty
    ]);

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