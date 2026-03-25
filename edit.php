<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$success = false;
$mode    = isset($_REQUEST['article_id']) ? 'article' : 'profile';

if ($mode === 'article') {
    $article_id = (int)$_REQUEST['article_id'];
    $stmt = $pdo->prepare("SELECT * FROM article WHERE article_id = :id");
    $stmt->execute([':id' => $article_id]);
    $article = $stmt->fetch();

    if (!$article || ($article['autor_id'] != $user_id && $_SESSION['role'] !== 'admin')) {
        header('Location: account.php');
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_edit_article'])) {
        $name        = htmlspecialchars(trim($_POST['article_name']));
        $description = htmlspecialchars(trim($_POST['description']));
        $price       = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $img_name    = $article['article_image'];

        if (isset($_FILES['article_image']) && $_FILES['article_image']['error'] == 0) {
            $ext     = strtolower(pathinfo($_FILES['article_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $new_name = uniqid('article_') . '.' . $ext;
                if (move_uploaded_file($_FILES['article_image']['tmp_name'], 'img/' . $new_name)) {
                    $img_name = $new_name;
                }
            }
        }

        if (!empty($name) && $price !== false && $price > 0) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE article SET article_name=:n, description=:d, price=:p, article_image=:img
                    WHERE article_id=:id
                ");
                $stmt->execute([':n'=>$name, ':d'=>$description, ':p'=>$price, ':img'=>$img_name, ':id'=>$article_id]);
                $success = true;
                $message = "Article mis à jour avec succès !";
                
                $stmt2 = $pdo->prepare("SELECT * FROM article WHERE article_id = :id");
                $stmt2->execute([':id' => $article_id]);
                $article = $stmt2->fetch();
            } catch (PDOException $e) {
                $message = "Erreur : " . htmlspecialchars($e->getMessage());
            }
        } else {
            $message = "Veuillez remplir tous les champs correctement.";
        }
    }

    if (isset($_REQUEST['delete']) && $_REQUEST['delete'] == 1) {
        $stmt = $pdo->prepare("DELETE FROM article WHERE article_id = :id AND autor_id = :uid");
        $stmt->execute([':id' => $article_id, ':uid' => $user_id]);
        header('Location: account.php');
        exit;
    }
}

if ($mode === 'profile') {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_edit_profile'])) {
        $username = htmlspecialchars(trim($_POST['username']));
        $email    = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $img_name = $user['profile_picture'];

        $new_password = null;
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            } else {
                $message = "Les mots de passe ne correspondent pas.";
            }
        }

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0 && empty($message)) {
            $ext     = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $new_name = uniqid('user_') . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], 'img/' . $new_name)) {
                    $img_name = $new_name;
                }
            }
        }

        if (!empty($username) && $email && empty($message)) {
            try {
                if ($new_password) {
                    $stmt = $pdo->prepare("UPDATE user SET username=:u, email=:e, profile_picture=:img, password=:pw WHERE user_id=:id");
                    $stmt->execute([':u'=>$username, ':e'=>$email, ':img'=>$img_name, ':pw'=>$new_password, ':id'=>$user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE user SET username=:u, email=:e, profile_picture=:img WHERE user_id=:id");
                    $stmt->execute([':u'=>$username, ':e'=>$email, ':img'=>$img_name, ':id'=>$user_id]);
                }
                $_SESSION['username'] = $username;
                $success = true;
                $message = "Profil mis à jour avec succès !";
                $stmt2 = $pdo->prepare("SELECT * FROM user WHERE user_id = :id");
                $stmt2->execute([':id' => $user_id]);
                $user = $stmt2->fetch();
            } catch (PDOException $e) {
                $message = ($e->getCode() == 23000) ? "Ce nom d'utilisateur ou email est déjà pris." : "Erreur : " . htmlspecialchars($e->getMessage());
            }
        } elseif (empty($message)) {
            $message = "Veuillez remplir tous les champs.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier – ShopExpress</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .edit-section { padding: 3rem 0; background: var(--bg-light); min-height: 80vh; display:flex; align-items:flex-start; justify-content:center; }
        .edit-container { background: var(--white); border-radius: 20px; box-shadow: 0 4px 30px rgba(37,99,235,.1); padding: 2.5rem; width: 100%; max-width: 640px; }
        .edit-container h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; text-align: center; }
        .success-msg { color:#065f46;background:#d1fae5;border:1px solid #a7f3d0;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.5rem;text-align:center; }
        .error-msg { color:#991b1b;background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.5rem;text-align:center; }
        .delete-zone { margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); text-align:center; }
        .btn-danger { background: var(--danger-color); color: white; border: none; padding: .7rem 1.5rem; border-radius: 10px; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="edit-section">
    <div class="edit-container">

        <?php if ($mode === 'article'): ?>
            <h2><i class="fas fa-edit" style="color:var(--primary-color)"></i> Modifier l'article</h2>

            <?php if (!empty($message)): ?>
                <div class="<?php echo $success ? 'success-msg' : 'error-msg'; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Image actuelle</label>
                    <img src="img/articles/<?php echo htmlspecialchars($article['article_image']); ?>"
                         style="width:100%;height:200px;object-fit:contain;border-radius:10px;margin-bottom:.5rem;"
                         onerror="this.src='https://placehold.co/600x200?text=Image'">
                    <input type="file" name="article_image" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Nom de l'article *</label>
                    <input type="text" name="article_name" class="form-control" required
                           value="<?php echo htmlspecialchars($article['article_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" class="form-control" rows="5" required style="resize:vertical;"><?php echo htmlspecialchars($article['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Prix (€) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0.01" required
                           value="<?php echo htmlspecialchars($article['price']); ?>">
                </div>
                <button type="submit" name="btn_edit_article" class="btn btn-primary btn-auth">
                    <i class="fas fa-save"></i> Sauvegarder
                </button>
            </form>

            <div class="delete-zone">
                <p style="color:var(--text-light);margin-bottom:1rem;">Zone dangereuse</p>
                <form action="edit.php" method="POST" onsubmit="return confirm('Supprimer cet article définitivement ?');" style="display:inline;">
                    <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
                    <input type="hidden" name="delete" value="1">
                    <button type="submit" class="btn-danger" style="border:none;"><i class="fas fa-trash"></i> Supprimer l'article</button>
                </form>
            </div>

        <?php else: /* mode profil */ ?>
            <h2><i class="fas fa-user-edit" style="color:var(--primary-color)"></i> Modifier mon profil</h2>

            <?php if (!empty($message)): ?>
                <div class="<?php echo $success ? 'success-msg' : 'error-msg'; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group" style="text-align:center;margin-bottom:1.5rem;">
                    <img src="img/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                         id="avatar-preview"
                         style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-color);cursor:pointer;"
                         onclick="document.getElementById('profile_picture').click()"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=2563eb&color=fff'">
                    <br><small style="color:var(--text-light);">Cliquer pour changer la photo</small>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display:none;" onchange="previewAvatar(event)">
                </div>
                <div class="form-group">
                    <label>Nom d'utilisateur *</label>
                    <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Nouveau mot de passe <small style="color:var(--text-light);">(laisser vide pour ne pas changer)</small></label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••">
                </div>
                <button type="submit" name="btn_edit_profile" class="btn btn-primary btn-auth">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="account.php"><i class="fas fa-arrow-left"></i> Retour à mon compte</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
function previewAvatar(event) {
    const img = document.getElementById('avatar-preview');
    img.src = URL.createObjectURL(event.target.files[0]);
}
</script>
</body>
</html>