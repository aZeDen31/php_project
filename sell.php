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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_sell'])) {
    $name        = htmlspecialchars(trim($_POST['article_name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $price       = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $img_name    = "default_article.png";

    if (isset($_FILES['article_image']) && $_FILES['article_image']['error'] == 0) {
        $extension = strtolower(pathinfo($_FILES['article_image']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($extension, $allowed)) {
            $new_name = uniqid('article_') . '.' . $extension;
            $dest     = 'img/articles/' . $new_name;
            if (move_uploaded_file($_FILES['article_image']['tmp_name'], $dest)) {
                $img_name = $new_name;
            } else {
                $message = "Erreur lors du téléchargement de l'image.";
            }
        } else {
            $message = "Format d'image invalide (JPG, PNG, GIF, WEBP).";
        }
    }

    if (!empty($name) && $price !== false && $price > 0 && empty($message)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO article (article_name, description, price, publication_date, autor_id, article_image)
                VALUES (:n, :d, :p, CURDATE(), :a, :img)
            ");
            $stmt->execute([
                ':n'   => $name,
                ':d'   => $description,
                ':p'   => $price,
                ':a'   => $user_id,
                ':img' => $img_name,
            ]);
            $success = true;
            $message = "Votre article a été mis en vente avec succès !";
        } catch (PDOException $e) {
            $message = "Erreur lors de la publication : " . htmlspecialchars($e->getMessage());
        }
    } elseif (empty($message)) {
        $message = "Veuillez remplir tous les champs correctement.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendre un article – ShopExpress</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sell-section { padding: 3rem 0; background: var(--bg-light); min-height: 80vh; display:flex; align-items:flex-start; justify-content:center; }
        .sell-container { background: var(--white); border-radius: 20px; box-shadow: 0 4px 30px rgba(37,99,235,.1); padding: 2.5rem; width: 100%; max-width: 640px; }
        .sell-container h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; text-align: center; }
        .preview-box { border: 2px dashed var(--border-color); border-radius: 12px; height: 200px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom: 1rem; cursor:pointer; }
        .preview-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .price-input { position: relative; }
        .price-input input { padding-right: 2.5rem; }
        .price-input span { position:absolute; right:1rem; top:50%; transform:translateY(-50%); color:var(--text-light); font-weight:600; }
        .success-msg { color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1.5rem; text-align: center; }
        .error-msg { color: #991b1b; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1.5rem; text-align: center; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="sell-section">
    <div class="sell-container">
        <h2><i class="fas fa-store" style="color:var(--primary-color);"></i> Vendre un article</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $success ? 'success-msg' : 'error-msg'; ?>">
                <?php echo $message; ?>
                <?php if ($success): ?>
                    <br><a href="account.php#articles">Voir mes articles</a> · <a href="sell.php">Vendre un autre</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" enctype="multipart/form-data">

            <!-- Preview image -->
            <div class="form-group">
                <label>Photo de l'article</label>
                <div class="preview-box" id="preview-box" onclick="document.getElementById('article_image').click()">
                    <img id="preview-img" src="" alt="" style="display:none;">
                    <span id="preview-placeholder" style="color:var(--text-light);">
                        <i class="fas fa-camera" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                        Cliquez pour ajouter une photo
                    </span>
                </div>
                <input type="file" id="article_image" name="article_image" accept="image/*" style="display:none;" onchange="previewImage(event)">
            </div>

            <div class="form-group">
                <label for="article_name">Nom de l'article *</label>
                <input type="text" id="article_name" name="article_name" class="form-control"
                       placeholder="Ex: iPhone 14 Pro, Vélo de route..." required
                       value="<?php echo isset($_POST['article_name']) ? htmlspecialchars($_POST['article_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" class="form-control"
                          rows="5" placeholder="Décrivez votre article : état, caractéristiques, raison de la vente..." required
                          style="resize:vertical;"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Prix *</label>
                <div class="price-input">
                    <input type="number" id="price" name="price" class="form-control"
                           placeholder="0.00" min="0.01" step="0.01" required
                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                    <span>€</span>
                </div>
            </div>

            <button type="submit" name="btn_sell" class="btn btn-primary btn-auth">
                <i class="fas fa-paper-plane"></i> Publier l'annonce
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
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const img   = document.getElementById('preview-img');
        const ph    = document.getElementById('preview-placeholder');
        img.src     = e.target.result;
        img.style.display   = 'block';
        ph.style.display    = 'none';
    };
    reader.readAsDataURL(file);
}
</script>
</body>
</html>