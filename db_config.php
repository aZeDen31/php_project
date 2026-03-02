<?php
// Informations de connexion basées sur votre export MariaDB
$host    = '127.0.0.1';     // Ou 'localhost'
$db      = 'php_exam_db';   // Nom exact de votre base
$user    = 'root';          // Utilisateur par défaut XAMPP/WAMP
$pass    = '';              // Mot de passe par défaut (vide)
$charset = 'utf8mb4';       // En accord avec votre COLLATE utf8mb4_general_ci

// Construction du DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options pour rendre PDO plus robuste et sécurisé
$options = [
    // Active les exceptions pour les erreurs SQL (crucial pour le débug)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Récupère les données sous forme de tableau associatif par défaut
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Désactive l'émulation des requêtes préparées (meilleure sécurité)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Création de l'instance de connexion
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En cas d'erreur, on arrête le script et on affiche le message
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>