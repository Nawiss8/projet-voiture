<?php
require 'connexion.php';

// Récupérer l'ID de la voiture
$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Incrémenter le compteur de vues
$stmt = $pdo->prepare("UPDATE voltures_vehicles SET vues = vues + 1 WHERE id = ?");
$stmt->execute([$id]);

// Récupérer les détails de la voiture avec sa marque
$stmt = $pdo->prepare("
    SELECT v.*, m.nom as marque_nom, m.id as marque_id, m.logo_url
    FROM voltures_vehicles v
    JOIN voltures_marques m ON v.marque_id = m.id
    WHERE v.id = ?
");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    header('Location: index.php');
    exit;
}

// Vérifier si la voiture est dans les favoris de l'utilisateur connecté
$is_favorite = false;
if (estConnecte()) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM voltures_favourites WHERE user_id = ? AND vehicle_id = ?");
    $stmt->execute([$user_id, $id]);
    $is_favorite = $stmt->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($vehicle['marque_nom'] . ' ' . $vehicle['modelle']) ?> - Détails</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            min-height: 100vh;
            color: white;
        }

        /* Navigation retour */
        .back-nav {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
        }

        .back-btn {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 12px 20px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-btn:hover {
            background: #e74c3c;
            transform: translateX(-5px);
        }

        /* Menu utilisateur */
        .user-menu {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        .user-menu > div {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-menu a {
            color: white;
            text-decoration: none;
        }

        /* Conteneur principal */
        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 100px 30px 50px;
        }

        /* Carte principale */
        .car-detail-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        /* Section image */
        .car-image-section {
            flex: 1;
            min-width: 300px;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .car-image-section img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.5));
        }

        /* Section infos */
        .car-info-section {
            flex: 1;
            min-width: 300px;
            padding: 40px;
        }

        .car-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .car-title span {
            color: #e74c3c;
        }

        .car-marque {
            display: inline-block;
            background: rgba(231, 76, 60, 0.2);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        /* Grille specs */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .spec-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .spec-label {
            color: #e74c3c;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .spec-value {
            font-size: 1.3rem;
            font-weight: 600;
        }

        /* Prix */
        .car-price {
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.2), rgba(192, 57, 43, 0.1));
            border-radius: 15px;
            text-align: center;
        }

        .price-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }

        .price-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #e74c3c;
        }

        .price-value small {
            font-size: 1rem;
        }

        /* Bouton favoris */
        .favorite-form {
            margin-top: 20px;
        }

        .favorite-btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .favorite-add {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .favorite-add:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.4);
        }

        .favorite-remove {
            background: rgba(255, 255, 255, 0.1);
            color: #e74c3c;
            border: 2px solid #e74c3c;
        }

        .favorite-remove:hover {
            background: rgba(231, 76, 60, 0.2);
        }

        /* Message si non connecté */
        .login-message {
            text-align: center;
            padding: 15px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50px;
            margin-top: 20px;
        }

        .login-message a {
            color: #e74c3c;
            text-decoration: none;
        }

        /* Compteur vues */
        .views-count {
            margin-top: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .detail-container {
                padding: 80px 15px 30px;
            }
            
            .car-title {
                font-size: 1.8rem;
            }
            
            .specs-grid {
                gap: 10px;
            }
            
            .spec-value {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Bouton retour -->
    <div class="back-nav">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Retour à la galerie
        </a>
    </div>

    <!-- Menu utilisateur -->
    <div class="user-menu">
        <?php if (estConnecte()): ?>
            <div>
                <i class="fas fa-user" style="color: #e74c3c;"></i>
                <span><?= htmlspecialchars($_SESSION['user_nom']) ?></span>
                <?php if (estAdmin()): ?>
                    <span style="background: #e74c3c; padding: 4px 8px; border-radius: 20px; font-size: 0.7rem;">Admin</span>
                <?php endif; ?>
                <a href="favoris.php">
                    <i class="fas fa-heart"></i>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        <?php else: ?>
            <div>
                <a href="login.php">Connexion</a>
                <a href="register.php" style="color: #e74c3c;">Inscription</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="detail-container">
        <div class="car-detail-card">
            <!-- Image -->
            <div class="car-image-section">
                <?php if (!empty($vehicle['image_url']) && file_exists($vehicle['image_url'])): ?>
                    <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" alt="<?= htmlspecialchars($vehicle['modelle']) ?>">
                <?php else: ?>
                    <img src="img-vid/default-car.png" alt="Image non disponible">
                <?php endif; ?>
            </div>

            <!-- Infos -->
            <div class="car-info-section">
                <div class="car-marque">
                    <i class="fas fa-flag-checkered"></i> <?= htmlspecialchars($vehicle['marque_nom']) ?>
                </div>
                
                <h1 class="car-title">
                    <?= htmlspecialchars($vehicle['modelle']) ?>
                </h1>

                <div class="specs-grid">
                    <div class="spec-item">
                        <div class="spec-label"><i class="fas fa-calendar"></i> Année</div>
                        <div class="spec-value"><?= htmlspecialchars($vehicle['annee']) ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label"><i class="fas fa-palette"></i> Couleur</div>
                        <div class="spec-value"><?= htmlspecialchars($vehicle['couleur']) ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label"><i class="fas fa-microchip"></i> Moteur</div>
                        <div class="spec-value"><?= !empty($vehicle['moteur']) ? htmlspecialchars($vehicle['moteur']) : '-' ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label"><i class="fas fa-tachometer-alt"></i> Puissance</div>
                        <div class="spec-value"><?= !empty($vehicle['puissance']) ? htmlspecialchars($vehicle['puissance']) . ' ch' : '-' ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label"><i class="fas fa-cogs"></i> Transmission</div>
                        <div class="spec-value"><?= !empty($vehicle['transmission']) ? htmlspecialchars($vehicle['transmission']) : '-' ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label"><i class="fas fa-stopwatch"></i> 0-100 km/h</div>
                        <div class="spec-value"><?= !empty($vehicle['acceleration']) ? htmlspecialchars($vehicle['acceleration']) . ' s' : '-' ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label"><i class="fas fa-gauge-high"></i> Vitesse max</div>
                        <div class="spec-value"><?= !empty($vehicle['vitesse_max']) ? htmlspecialchars($vehicle['vitesse_max']) . ' km/h' : '-' ?></div>
                    </div>
                </div>

                <div class="car-price">
                    <div class="price-label">Prix</div>
                    <div class="price-value">
                        <?= number_format($vehicle['prix'], 0, ',', ' ') ?> €
                    </div>
                </div>

                <!-- Bouton favoris -->
                <?php if (estConnecte()): ?>
                    <form method="POST" action="toggle_favoris.php" class="favorite-form">
                        <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                        <input type="hidden" name="return_url" value="detail.php?id=<?= $vehicle['id'] ?>">
                        <button type="submit" name="toggle_favorite" class="favorite-btn <?= $is_favorite ? 'favorite-remove' : 'favorite-add' ?>">
                            <?php if ($is_favorite): ?>
                                <i class="fas fa-heart-broken"></i> Retirer des favoris
                            <?php else: ?>
                                <i class="fas fa-heart"></i> Ajouter aux favoris
                            <?php endif; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-message">
                        <i class="fas fa-lock"></i> 
                        <a href="login.php">Connectez-vous</a> pour ajouter cette voiture à vos favoris
                    </div>
                <?php endif; ?>

                <div class="views-count">
                    <i class="fas fa-eye"></i> <?= number_format($vehicle['vues'], 0, ',', ' ') ?> vues
                </div>
            </div>
        </div>
    </div>
</body>
</html>