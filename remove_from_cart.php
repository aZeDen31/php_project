<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $article_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND article_id = :article_id");
    $stmt->execute([
        ':user_id' => $user_id, 
        ':article_id' => $article_id
    ]);
    header('Location: cart.php');
    exit;
} else {
    header('Location: index.php');
    exit;
}
?>