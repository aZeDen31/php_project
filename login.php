<?php
// Définir une très longue durée de vie pour la session et le cookie (10 ans)
ini_set('session.gc_maxlifetime', 315360000);
ini_set('session.cookie_lifetime', 315360000);
session_start();
require 'db_config.php';

$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
$logFile = __DIR__ . '/error.log';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_login'])) {
    $email_or_username = trim($_POST['email_or_username']);
    $password = $_POST['password'];

    if (!empty($email_or_username) && !empty($password)) {
        try {
            // Correction : Utilisation de deux noms de paramètres distincts (:id1 et :id2)
            $sql = "SELECT * FROM user WHERE email = :id1 OR username = :id2";
            $stmt = $pdo->prepare($sql);
            
            // On lie la même valeur aux deux marqueurs
            $stmt->execute([
                ':id1' => $email_or_username,
                ':id2' => $email_or_username
            ]);
            
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Créer la session pour l'utilisateur
                // Vérifiez bien que les noms de colonnes 'user_id', 'username' et 'role' 
                // existent exactement ainsi dans votre table 'user'
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Rediriger vers la page d'accueil
                header('Location: /php-exam/php_project');
                exit;
            } else {
                $message = "Identifiants incorrects.";
            }
        } catch (PDOException $e) {
             // Log détaillé dans error.log
             $logEntry = sprintf("[%s] PDOException dans login (%s): %s in %s on line %d\n", date('Y-m-d H:i:s'), $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
             file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
 
             if ($debug) {
                 $message = "Erreur SQL : " . htmlspecialchars($e->getMessage());
             } else {
                 $message = "Une erreur est survenue lors de la connexion. (Détails dans le log)";
             }
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Connexion</title>
</head>

<?php include 'header.php'; ?>

<body>

    <section class="auth-section">
        <div class="auth-container">
            <h2>Connexion</h2>

            <?php if (!empty($message)): ?>
                <div class="auth-message" style="color: red; margin-bottom: 15px; text-align: center;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email_or_username">Email ou Nom d'utilisateur</label>
                    <input type="text" id="email_or_username" name="email_or_username" class="form-control" placeholder="exemple@mail.com ou JeanDupont" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" name="btn_login" class="btn btn-primary btn-auth">
                    Se connecter
                </button>
            </form>

            <div class="auth-footer">
                Pas encore de compte ? <a href="register.php">S'inscrire</a>
            </div>
        </div>
    </section>

</body>
</html>