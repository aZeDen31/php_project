<?php
$host    = '127.0.0.1';
$db      = 'php_exam_db';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    // Active les exceptions pour les erreurs SQL (crucial pour le débug)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Récupère les données sous forme de tableau associatif par défaut
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Désactive l'émulation des requêtes préparées (meilleure sécurité)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>