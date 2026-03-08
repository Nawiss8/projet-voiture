<?php
require 'connexion.php';

// Vérifier s'il y a déjà des admins
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$hasAdmin = $stmt->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user'; // Par défaut user
    
    $errors = [];
    
    // Validation
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
    if (strlen($password) < 6) $errors[] = "Mot de passe trop court (6 caractères minimum)";
    if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas";
    
    // Vérifier si email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $errors[] = "Cet email est déjà utilisé";
    
    // Si c'est une demande admin mais qu'il y a déjà un admin, on force user
    if ($role === 'admin' && $hasAdmin) {
        $role = 'user';
    }
    
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $hash, $role]);
        
        $_SESSION['success'] = "Compte créé ! Vous pouvez vous connecter.";
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0a0a0a;
        }
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.6) 100%);
            z-index: -1;
        }
        .auth-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 30px;
            padding: 40px;
            width: 90%;
            max-width: 400px;
            color: white;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #e74c3c;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: rgba(255,255,255,0.8);
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            color: white;
            font-size: 1rem;
        }
        select {
            cursor: pointer;
            option {
                background: #1a1a1a;
            }
        }
        input:focus, select:focus {
            outline: none;
            border-color: #e74c3c;
        }
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(231,76,60,0.5);
        }
        .error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            color: #e74c3c;
        }
        .error ul {
            margin-left: 20px;
        }
        .link {
            text-align: center;
            margin-top: 20px;
        }
        .link a {
            color: #e74c3c;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
        .info-note {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.5);
        }
        .admin-note {
            background: rgba(231, 76, 60, 0.1);
            border-left: 3px solid #e74c3c;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
        }
    </style>
</head>
<body>
   <video class="video-background" autoplay muted loop playsinline>
    <source src="img-vid/Cars 2 - Rod Redlines Death - HD Clip.mp4" type="video/mp4">
    Votre navigateur ne supporte pas la vidéo.
</video>
<div class="video-overlay"></div>

    <div class="auth-container">
        <h1><i class="fas fa-user-plus"></i> Inscription</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!$hasAdmin): ?>
            <div class="admin-note">
                <i class="fas fa-crown" style="color: #e74c3c;"></i>
                Premier compte : vous serez automatiquement ADMIN
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="nom" value="<?= $_POST['nom'] ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label>Mot de passe (min 6 caractères)</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" required>
            </div>

            <?php if ($hasAdmin): ?>
                <!-- Champ caché - toujours user pour les nouveaux comptes -->
                <input type="hidden" name="role" value="user">
                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    Les nouveaux comptes sont créés en tant qu'utilisateurs standards
                </div>
            <?php else: ?>
                <!-- Premier compte = admin automatique -->
                <input type="hidden" name="role" value="admin">
            <?php endif; ?>
            
            <button type="submit">S'inscrire</button>
        </form>
        
        <div class="link">
            Déjà un compte ? <a href="login.php">Connectez-vous</a>
        </div>
    </div>
</body>
</html>