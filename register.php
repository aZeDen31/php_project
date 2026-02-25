<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ShopExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* =========================================
           Variables et Reset (Repris de ton style)
           ========================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --white: #ffffff;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            background-color: var(--bg-light);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* =========================================
           Header simplifié
           ========================================= */
        .header {
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-content { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; }
        .logo h1 { color: var(--primary-color); font-size: 1.8rem; display: flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        
        .nav ul { display: flex; list-style: none; gap: 2rem; }
        .nav a { text-decoration: none; color: var(--text-dark); font-weight: 500; transition: color 0.3s; }
        .nav a:hover { color: var(--primary-color); }

        /* =========================================
           Boutons
           ========================================= */
        .btn {
            display: inline-block; padding: 0.8rem 2rem; border-radius: 25px;
            text-decoration: none; font-weight: 600; transition: all 0.3s; border: none; cursor: pointer; text-align: center;
        }
        .btn-primary { background: var(--accent-color); color: var(--white); }
        .btn-primary:hover { background: #d97706; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3); }
        .w-100 { width: 100%; }

        /* =========================================
           Styles spécifiques au Formulaire d'Inscription
           ========================================= */
        .auth-section {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 0;
        }

        .auth-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 450px;
        }

        .auth-card h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Input type file stylisé */
        .form-group input[type="file"] {
            padding: 0.5rem;
            background-color: var(--bg-light);
            cursor: pointer;
        }

        .auth-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .auth-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .auth-link a:hover {
            color: var(--secondary-color);
        }

        /* =========================================
           Footer simplifié
           ========================================= */
        .footer { background: var(--text-dark); color: var(--white); padding: 2rem 0; text-align: center; margin-top: auto; }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <a href="index.php" class="logo">
                <h1><i class="fas fa-shopping-bag"></i> ShopExpress</h1>
            </a>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="boutique.php">Boutique</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="auth-section">
        <div class="container" style="display: flex; justify-content: center;">
            <div class="auth-card">
                <h2>Créer un compte</h2>
                <form action="register_process.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" id="username" name="username" placeholder="ex: jeandupont" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse Email <span style="color: var(--danger-color);">*</span></label>
                        <input type="email" id="email" name="email" placeholder="jean.dupont@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe <span style="color: var(--danger-color);">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Min. 8 caractères" required>
                    </div>

                    <div class="form-group">
                        <label for="profile_picture">Photo de profil (Optionnel)</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg, image/webp">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                </form>

                <p class="auth-link">
                    Déjà un compte ? <a href="login.html">Connectez-vous</a>
                </p>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 ShopExpress. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>