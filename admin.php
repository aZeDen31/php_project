<?php
require 'db_config.php';
session_start();

// Accès réservé aux admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = "";

// Suppression d'un article
if (isset($_GET['delete_article'])) {
    $id = (int)$_GET['delete_article'];
    $pdo->prepare("DELETE FROM article WHERE article_id = :id")->execute([':id' => $id]);
    $message = "Article supprimé.";
}

// Suppression d'un utilisateur
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    if ($id != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM user WHERE user_id = :id")->execute([':id' => $id]);
        $message = "Utilisateur supprimé.";
    }
}

// Changer le rôle d'un utilisateur
if (isset($_GET['toggle_role'])) {
    $id   = (int)$_GET['toggle_role'];
    $stmt = $pdo->prepare("SELECT role FROM user WHERE user_id = :id");
    $stmt->execute([':id' => $id]);
    $u    = $stmt->fetch();
    if ($u) {
        $new_role = ($u['role'] === 'admin') ? 'user' : 'admin';
        $pdo->prepare("UPDATE user SET role = :r WHERE user_id = :id")->execute([':r' => $new_role, ':id' => $id]);
        $message = "Rôle mis à jour.";
    }
}

// Stats
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM user")->fetchColumn();
$totalArticles = $pdo->query("SELECT COUNT(*) FROM article")->fetchColumn();
$totalInvoices = $pdo->query("SELECT COUNT(*) FROM invoice")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM invoice")->fetchColumn();

// Listes
$users    = $pdo->query("SELECT * FROM user ORDER BY user_id DESC")->fetchAll();
$articles = $pdo->query("
    SELECT a.*, u.username AS author_name 
    FROM article a JOIN user u ON a.autor_id = u.user_id 
    ORDER BY a.publication_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration – ShopExpress</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-section { padding: 2rem 0; background: var(--bg-light); min-height: 100vh; }
        .admin-header { background: var(--white); border-radius: 16px; padding: 1.5rem 2rem; margin-bottom: 2rem; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .admin-header h1 { font-size: 1.5rem; color: var(--primary-color); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--white); border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .stat-card .icon { font-size: 2rem; margin-bottom: .5rem; }
        .stat-card .value { font-size: 2rem; font-weight: 700; color: var(--primary-color); }
        .stat-card .label { color: var(--text-light); font-size: .85rem; }
        .admin-card { background: var(--white); border-radius: 16px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,.06); margin-bottom: 2rem; }
        .admin-card h2 { font-size: 1.2rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border-color); padding-bottom: .8rem; }
        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        th { background: var(--bg-light); padding: .75rem 1rem; text-align: left; color: var(--text-light); font-weight: 600; }
        td { padding: .75rem 1rem; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .badge-role { padding: .25rem .7rem; border-radius: 20px; font-size: .8rem; font-weight: 600; }
        .badge-admin { background: #fef3c7; color: #92400e; }
        .badge-user { background: #eff6ff; color: var(--primary-color); }
        .action-btns a { margin-right: .3rem; padding: .3rem .7rem; border-radius: 6px; font-size: .8rem; text-decoration: none; }
        .btn-edit-sm { background: var(--primary-color); color: white; }
        .btn-del { background: var(--danger-color); color: white; }
        .btn-role { background: var(--accent-color); color: white; }
        .msg-box { background: #d1fae5; color: #065f46; padding: .75rem 1rem; border-radius: 10px; margin-bottom: 1.5rem; }
        @media(max-width:768px){ th,td{ padding: .5rem .6rem; font-size:.8rem; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="admin-section">
    <div class="container">

        <div class="admin-header">
            <h1><i class="fas fa-cog"></i> Tableau de bord Admin</h1>
            <div style="display:flex;gap:1rem;">
                <a href="account.php" class="btn btn-secondary" style="font-size:.9rem;">Mon compte</a>
                <a href="account.php?logout=1" class="btn" style="background:var(--danger-color);color:white;font-size:.9rem;">Déconnexion</a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="msg-box"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon" style="color:var(--primary-color)"><i class="fas fa-users"></i></div>
                <div class="value"><?php echo $totalUsers; ?></div>
                <div class="label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color:var(--accent-color)"><i class="fas fa-box"></i></div>
                <div class="value"><?php echo $totalArticles; ?></div>
                <div class="label">Articles</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color:var(--success-color)"><i class="fas fa-file-invoice"></i></div>
                <div class="value"><?php echo $totalInvoices; ?></div>
                <div class="label">Commandes</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="color:#8b5cf6"><i class="fas fa-euro-sign"></i></div>
                <div class="value"><?php echo number_format($totalRevenue, 0); ?> €</div>
                <div class="label">Chiffre d'affaires</div>
            </div>
        </div>

        <!-- Gestion des utilisateurs -->
        <div class="admin-card">
            <h2><i class="fas fa-users"></i> Gestion des utilisateurs</h2>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Solde</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['user_id']; ?></td>
                                <td>
                                    <img src="img/<?php echo htmlspecialchars($u['profile_picture']); ?>"
                                         style="width:30px;height:30px;border-radius:50%;object-fit:cover;margin-right:.5rem;vertical-align:middle;"
                                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($u['username']); ?>&size=30&background=2563eb&color=fff'">
                                    <?php echo htmlspecialchars($u['username']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="badge-role <?php echo $u['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo htmlspecialchars($u['role']); ?>
                                </span></td>
                                <td><?php echo number_format($u['solde'], 2); ?> €</td>
                                <td class="action-btns">
                                    <a href="admin.php?toggle_role=<?php echo $u['user_id']; ?>"
                                       class="btn-role"
                                       onclick="return confirm('Changer le rôle de <?php echo htmlspecialchars($u['username']); ?> ?')">
                                        <i class="fas fa-exchange-alt"></i> Rôle
                                    </a>
                                    <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                                        <a href="admin.php?delete_user=<?php echo $u['user_id']; ?>"
                                           class="btn-del"
                                           onclick="return confirm('Supprimer <?php echo htmlspecialchars($u['username']); ?> ?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gestion des articles -->
        <div class="admin-card">
            <h2><i class="fas fa-box"></i> Gestion des articles</h2>
            <div style="margin-bottom:1rem;">
                <a href="sell.php" class="btn btn-primary" style="font-size:.9rem;">
                    <i class="fas fa-plus"></i> Ajouter un article
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Auteur</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $art): ?>
                            <tr>
                                <td><?php echo $art['article_id']; ?></td>
                                <td>
                                    <img src="img/articles/<?php echo htmlspecialchars($art['article_image']); ?>"
                                         style="width:50px;height:40px;object-fit:cover;border-radius:6px;"
                                         onerror="this.src='https://placehold.co/50x40?text=img'">
                                </td>
                                <td><?php echo htmlspecialchars($art['article_name']); ?></td>
                                <td><strong><?php echo number_format($art['price'], 2); ?> €</strong></td>
                                <td><?php echo htmlspecialchars($art['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($art['publication_date']); ?></td>
                                <td class="action-btns">
                                    <a href="detail.php?id=<?php echo $art['article_id']; ?>" class="btn-edit-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?article_id=<?php echo $art['article_id']; ?>" class="btn-edit-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="admin.php?delete_article=<?php echo $art['article_id']; ?>"
                                       class="btn-del"
                                       onclick="return confirm('Supprimer cet article ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($articles)): ?>
                            <tr><td colspan="7" style="text-align:center;color:var(--text-light);padding:2rem;">Aucun article pour le moment.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>