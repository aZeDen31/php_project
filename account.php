<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$success_msg = "";

$is_own_profile = true;
$view_user_id = $user_id;

if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] != $user_id) {
    $is_own_profile = false;
    $view_user_id = (int)$_GET['id'];
}

if ($is_own_profile && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_balance'])) {
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    if ($amount !== false && $amount > 0) {
        $stmtUpdate = $pdo->prepare("UPDATE user SET solde = solde + :amt WHERE user_id = :id");
        $stmtUpdate->execute([':amt' => $amount, ':id' => $user_id]);
        $success_msg = "Solde mis à jour avec succès (+ " . number_format($amount, 2) . " €).";
    } else {
        $message = "Montant invalide.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = :id");
$stmt->execute([':id' => $view_user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

$invoices = [];
if ($is_own_profile) {
    $stmtInv = $pdo->prepare("SELECT * FROM invoice WHERE user_id = :id ORDER BY transaction_date DESC");
    $stmtInv->execute([':id' => $user_id]);
    $invoices = $stmtInv->fetchAll();
}

$stmtArt = $pdo->prepare("SELECT * FROM article WHERE autor_id = :id ORDER BY publication_date DESC");
$stmtArt->execute([':id' => $view_user_id]);
$myArticles = $stmtArt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_own_profile ? "Mon Compte" : "Profil de " . htmlspecialchars($user['username']); ?> – ShopExpress</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .account-section { padding: 3rem 0; background: var(--bg-light); min-height: 80vh; }
        .account-grid { display: grid; grid-template-columns: 280px 1fr; gap: 2rem; align-items: start; }
        .account-sidebar { background: var(--white); border-radius: 16px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,.07); text-align: center; }
        .account-sidebar img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color); margin-bottom: 1rem; }
        .account-sidebar h3 { font-size: 1.2rem; margin-bottom: .3rem; }
        .account-sidebar .solde { background: #eff6ff; color: var(--primary-color); font-weight: 700; border-radius: 8px; padding: .5rem 1rem; margin: 1rem 0; display: inline-block; }
        .account-sidebar nav a { display: flex; align-items: center; gap: .7rem; padding: .7rem 1rem; border-radius: 8px; text-decoration: none; color: var(--text-dark); font-weight: 500; margin-bottom: .3rem; transition: background .2s; }
        .account-sidebar nav a:hover, .account-sidebar nav a.active { background: #eff6ff; color: var(--primary-color); }
        .account-sidebar nav a.logout { color: var(--danger-color); }
        .account-sidebar nav a.logout:hover { background: #fee2e2; }
        .account-main > div { background: var(--white); border-radius: 16px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,.07); margin-bottom: 2rem; }
        .account-main h2 { font-size: 1.4rem; margin-bottom: 1.5rem; color: var(--text-dark); border-bottom: 2px solid var(--border-color); padding-bottom: .8rem; }
        .invoice-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .invoice-table th { background: var(--bg-light); padding: .8rem 1rem; text-align: left; color: var(--text-light); font-weight: 600; }
        .invoice-table td { padding: .8rem 1rem; border-bottom: 1px solid var(--border-color); }
        .article-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
        .article-mini { border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; }
        .article-mini img { width: 100%; height: 120px; object-fit: contain; }
        .article-mini-info { padding: .8rem; }
        .article-mini-info h4 { font-size: .9rem; margin-bottom: .3rem; }
        .article-mini-info .price { color: var(--primary-color); font-weight: 700; }
        .article-mini-info .actions { display: flex; gap: .5rem; margin-top: .5rem; }
        .btn-sm { padding: .3rem .7rem; font-size: .8rem; border-radius: 6px; border: none; cursor: pointer; }
        .btn-edit { background: var(--primary-color); color: white; }
        .btn-delete { background: var(--danger-color); color: white; }
        @media(max-width:768px){ .account-grid{ grid-template-columns:1fr; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="account-section">
    <div class="container">
        <div class="account-grid">

            <!-- Sidebar -->
            <aside class="account-sidebar">
                <img src="img/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                     alt="Photo de profil"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=2563eb&color=fff'">
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                
                <?php if ($is_own_profile): ?>
                    <p style="color:var(--text-light);font-size:.85rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="solde"><i class="fas fa-wallet"></i> <?php echo number_format($user['solde'], 2); ?> €</div>
                <?php else: ?>
                    <span style="display:inline-block; margin-top:0.5rem; padding: 0.3rem 0.8rem; background:var(--bg-light); border-radius:20px; font-size:0.85rem; color:var(--text-light);">
                        Membre depuis <?php echo isset($user['created_at']) ? date('Y', strtotime($user['created_at'])) : "récemment"; ?>
                    </span>
                <?php endif; ?>

                <nav style="margin-top: 1.5rem;">
                    <a href="#profil" class="active"><i class="fas fa-user"></i> <?php echo $is_own_profile ? "Mon profil" : "Profil utilisateur"; ?></a>
                    <a href="#articles"><i class="fas fa-store"></i> <?php echo $is_own_profile ? "Mes articles" : "Ses articles"; ?></a>
                    
                    <?php if ($is_own_profile): ?>
                        <a href="#solde-section"><i class="fas fa-wallet"></i> Ajouter du solde</a>
                        <a href="#commandes"><i class="fas fa-file-invoice"></i> Mes commandes</a>
                        <a href="sell"><i class="fas fa-plus-circle"></i> Vendre un article</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin"><i class="fas fa-cog"></i> Administration</a>
                        <?php endif; ?>
                        <a href="account?logout=1" class="logout"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a>
                    <?php endif; ?>
                </nav>
            </aside>

            <!-- Main content -->
            <main class="account-main">

                <!-- Profil -->
                <div id="profil">
                    <h2><i class="fas fa-user"></i> Informations personnelles</h2>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label style="font-size:.85rem;color:var(--text-light);">Nom d'utilisateur</label>
                            <p style="font-weight:600;"><?php echo htmlspecialchars($user['username']); ?></p>
                        </div>
                        <?php if ($is_own_profile): ?>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);">Email</label>
                                <p style="font-weight:600;"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);">Rôle</label>
                                <p style="font-weight:600;"><?php echo htmlspecialchars($user['role']); ?></p>
                            </div>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);">Solde</label>
                                <p style="font-weight:600;color:var(--primary-color);"><?php echo number_format($user['solde'], 2); ?> €</p>
                            </div>
                        <?php else: ?>
                            <div>
                                <label style="font-size:.85rem;color:var(--text-light);">Articles publiés</label>
                                <p style="font-weight:600;"><?php echo count($myArticles); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($is_own_profile): ?>
                        <a href="edit" class="btn btn-primary" style="margin-top:1.5rem;display:inline-block;">
                            <i class="fas fa-edit"></i> Modifier mon profil
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($is_own_profile): ?>
                <!-- Solde -->
                <div id="solde-section">
                    <h2><i class="fas fa-wallet"></i> Ajouter du solde</h2>
                    
                    <?php if (!empty($success_msg)): ?>
                        <div style="color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1.5rem; text-align: center;">
                            <?php echo $success_msg; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($message)): ?>
                        <div style="color: #991b1b; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1.5rem; text-align: center;">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="account" style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                        <input type="number" name="amount" class="form-control" placeholder="Montant en € (ex: 50.00)" min="0.01" step="0.01" required style="max-width:300px;">
                        <button type="submit" name="add_balance" class="btn btn-primary" style="padding: .85rem 1.5rem; border-radius: 12px; border:none; cursor:pointer;">
                            <i class="fas fa-plus"></i> Créditer
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Articles publiés -->
                <div id="articles">
                    <h2><i class="fas fa-store"></i> <?php echo $is_own_profile ? "Mes articles en vente" : "Ses articles en vente"; ?></h2>
                    <?php if (empty($myArticles)): ?>
                        <p style="color:var(--text-light);text-align:center;padding:2rem 0;">
                            <i class="fas fa-tags" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                            <?php echo $is_own_profile ? "Vous n'avez pas encore mis d'articles en vente." : "Cet utilisateur n'a pas encore mis d'articles en vente."; ?>
                            <?php if ($is_own_profile): ?>
                                <a href="sell" style="display:block;margin-top:.5rem;">Vendre mon premier article</a>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <div class="article-list">
                            <?php foreach ($myArticles as $art): ?>
                                <div class="article-mini">
                                    <img src="img/articles/<?php echo htmlspecialchars($art['article_image']); ?>"
                                         alt="<?php echo htmlspecialchars($art['article_name']); ?>"
                                         onerror="this.src='https://placehold.co/200x120?text=Image'">
                                    <div class="article-mini-info">
                                        <h4><?php echo htmlspecialchars($art['article_name']); ?></h4>
                                        <span class="price"><?php echo number_format($art['price'], 2); ?> €</span>
                                        <div class="actions">
                                            <?php if ($is_own_profile || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')): ?>
                                                <form action="edit" method="POST" style="display:inline; margin:0; padding:0;">
                                                    <input type="hidden" name="article_id" value="<?php echo $art['article_id']; ?>">
                                                    <button type="submit" class="btn-sm btn-edit" style="border:none; cursor:pointer;" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="detail?id=<?php echo $art['article_id']; ?>" class="btn-sm" style="background:var(--bg-light); color:var(--text-dark); text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($is_own_profile): ?>
                <div id="commandes">
                    <h2><i class="fas fa-file-invoice"></i> Mes commandes</h2>
                    <?php if (empty($invoices)): ?>
                        <p style="color:var(--text-light);text-align:center;padding:2rem 0;">
                            <i class="fas fa-box-open" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                            Vous n'avez pas encore passé de commande.
                        </p>
                    <?php else: ?>
                        <table class="invoice-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Adresse</th>
                                    <th>Ville</th>
                                    <th>CP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td><?php echo $inv['invoice_id']; ?></td>
                                        <td><?php echo htmlspecialchars($inv['transaction_date']); ?></td>
                                        <td><strong><?php echo number_format($inv['amount'], 2); ?> €</strong></td>
                                        <td><?php echo htmlspecialchars($inv['invoice_address']); ?></td>
                                        <td><?php echo htmlspecialchars($inv['invoice_city']); ?></td>
                                        <td><?php echo htmlspecialchars($inv['postal_code']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </main>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>