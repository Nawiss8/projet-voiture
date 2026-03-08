<?php
require 'connexion.php';

// Récupérer l'ID de la voiture
$id = $_GET['id'] ?? 0;

// Incrémenter le compteur de vues
$stmt = $pdo->prepare("UPDATE vehicules SET vues = vues + 1 WHERE id = ?");
$stmt->execute([$id]);

// Récupérer les infos de la voiture
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: index.php');
    exit;
}

// Vérifier si l'utilisateur est connecté et si la voiture est en favori
$isFav = false;
if (estConnecte()) {
    $check = $pdo->prepare("SELECT * FROM favoris WHERE user_id = ? AND vehicule_id = ?");
    $check->execute([$_SESSION['user_id'], $car['id']]);
    $isFav = $check->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['marque'] . ' ' . $car['modele']) ?> - Détail</title>
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
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
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

        .detail-container {
            max-width: 1000px;
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 40px;
            color: white;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 30px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .back-btn:hover {
            color: #e74c3c;
            transform: translateX(-5px);
        }

        .back-btn i {
            margin-right: 10px;
        }

        .detail-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .detail-image {
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            border-radius: 20px;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .detail-image img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.5));
        }

        .detail-info h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .detail-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            color: rgba(255,255,255,0.6);
            font-size: 1.1rem;
        }

        .detail-meta i {
            color: #e74c3c;
            margin-right: 5px;
        }

        .detail-stats {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
            padding: 20px;
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #e74c3c;
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
        }

        .detail-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .spec-item {
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .spec-label {
            color: #e74c3c;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .spec-value {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .detail-price {
            font-size: 2rem;
            font-weight: 700;
            color: #e74c3c;
            text-align: right;
            border-top: 2px solid rgba(255,255,255,0.1);
            padding-top: 30px;
        }

        .detail-views {
            margin-top: 20px;
            text-align: right;
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
        }

        .detail-views i {
            color: #e74c3c;
            margin-right: 5px;
        }

        /* Bouton favoris */
        .fav-btn-container {
            margin-top: 30px;
            text-align: center;
        }

        .fav-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .fav-btn-add {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 5px 15px rgba(231,76,60,0.3);
        }

        .fav-btn-add:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(231,76,60,0.5);
        }

        .fav-btn-remove {
            background: rgba(231,76,60,0.1);
            border: 1px solid #e74c3c;
            color: #e74c3c;
        }

        .fav-btn-remove:hover {
            background: #e74c3c;
            color: white;
        }

        .fav-btn i {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .detail-content {
                grid-template-columns: 1fr;
            }
            
            .detail-container {
                padding: 20px;
            }
            
            .detail-image img {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>
    <video class="video-background" autoplay muted loop>
        <source src="img-vid/BMW M3 Competition  lost soul Edit  4k.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="detail-container">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Retour à la galerie</a>
        
        <div class="detail-content">
            <div class="detail-image">
                <?php if (!empty($car['image_url']) && file_exists($car['image_url'])): ?>
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['modele']) ?>">
                <?php else: ?>
                    <img src="img-vid/default-car.png" alt="Image non disponible">
                <?php endif; ?>
            </div>
            
            <div class="detail-info">
                <h1><?= htmlspecialchars($car['marque'] . ' ' . $car['modele']) ?></h1>
                
                <div class="detail-meta">
                    <span><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($car['annee']) ?></span>
                    <span><i class="fas fa-palette"></i> <?= htmlspecialchars($car['couleur']) ?></span>
                </div>
                
                <div class="detail-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= number_format($car['vues'] ?? 0, 0, ',', ' ') ?></div>
                        <div class="stat-label">VUES</div>
                    </div>
                    <?php if (!empty($car['puissance'])): ?>
                    <div class="stat-item">
                        <div class="stat-value"><?= htmlspecialchars($car['puissance']) ?></div>
                        <div class="stat-label">CHEVAUX</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($car['vitesse_max'])): ?>
                    <div class="stat-item">
                        <div class="stat-value"><?= htmlspecialchars($car['vitesse_max']) ?></div>
                        <div class="stat-label">KM/H</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="detail-specs">
                    <?php if (!empty($car['moteur'])): ?>
                    <div class="spec-item">
                        <div class="spec-label">Moteur</div>
                        <div class="spec-value"><?= htmlspecialchars($car['moteur']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($car['transmission'])): ?>
                    <div class="spec-item">
                        <div class="spec-label">Transmission</div>
                        <div class="spec-value"><?= htmlspecialchars($car['transmission']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($car['acceleration'])): ?>
                    <div class="spec-item">
                        <div class="spec-label">0-100 km/h</div>
                        <div class="spec-value"><?= htmlspecialchars($car['acceleration']) ?> s</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="spec-item">
                        <div class="spec-label">Année</div>
                        <div class="spec-value"><?= htmlspecialchars($car['annee']) ?></div>
                    </div>
                </div>
                
                <div class="detail-price">
                    <?= number_format($car['prix'], 0, ',', ' ') ?> €
                </div>
                
                <div class="detail-views">
                    <i class="fas fa-eye"></i> <?= number_format($car['vues'] ?? 0, 0, ',', ' ') ?> personnes ont vu cette voiture
                </div>

                <!-- BOUTON FAVORIS -->
                <?php if (estConnecte()): ?>
                    <div class="fav-btn-container">
                        <?php if ($isFav): ?>
                            <a href="favoris.php?remove=<?= $car['id'] ?>" class="fav-btn fav-btn-remove">
                                <i class="fas fa-heart-broken"></i>
                                Retirer des favoris
                            </a>
                        <?php else: ?>
                            <a href="favoris.php?add=<?= $car['id'] ?>" class="fav-btn fav-btn-add">
                                <i class="fas fa-heart"></i>
                                Ajouter aux favoris
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="fav-btn-container">
                        <a href="login.php" class="fav-btn fav-btn-add" style="background: rgba(255,255,255,0.1);">
                            <i class="fas fa-sign-in-alt"></i>
                            Connectez-vous pour ajouter aux favoris
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>