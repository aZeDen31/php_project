<?php
require 'db_config.php';

$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
$logFile = __DIR__ . '/error.log';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_inscription'])) {
    
    $username = htmlspecialchars($_POST['username']);
    $email    = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $img_name = "default.png";

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload_dir = 'img/';
        $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('user_') . '.' . $extension;
        $destination = $upload_dir . $new_name;
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($extension), $allowed_extensions)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {
                $img_name = $new_name;
            } else {
                $message = "Erreur lors du téléchargement de l'image.";
            }
        } else {
            $message = "Format d'image non valide (JPG, PNG, GIF, WEBP uniquement).";
        }
    }

    if ($email && empty($message)) {
        try {
            $sql = "INSERT INTO user (username, password, email, solde, profile_picture, role) 
                    VALUES (:u, :p, :e, :s, :img, :r)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':u'   => $username,
                ':p'   => $password,
                ':e'   => $email,
                ':s'   => 0.0,
                ':img' => $img_name,
                ':r'   => 'user'
            ]);
            $message = "Inscription réussie !";
        } catch (PDOException $e) {
            // Log détaillé dans error.log
            $logEntry = sprintf("[%s] PDOException (%s): %s in %s on line %d\n", date('Y-m-d H:i:s'), $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

            if ($debug) {
                $message = "Erreur SQL : " . htmlspecialchars($e->getMessage());
            } else {
                $message = ($e->getCode() == 23000) ? "Pseudo ou Email déjà pris." : "Erreur lors de l'inscription. (Détails dans le log)";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Inscription</title>
</head>
<body>

    <section class="auth-section">
        <div class="auth-container">
            <h2>Créer un compte</h2>

            <?php if (!empty($message)): ?>
                <div class="auth-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Ex: JeanDupont" required>
                </div>

                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="exemple@mail.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label for="profile_picture">Photo de profil</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/*">
                </div>

                <button type="submit" name="btn_inscription" class="btn btn-primary btn-auth">
                    S'inscrire gratuitement
                </button>
            </form>

            <div class="auth-footer">
                Déjà un compte ? <a href="login">Se connecter</a>
            </div>
        </div>
    </section>

</body>
</html>