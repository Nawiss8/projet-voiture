<?php
require 'connexion.php';
redirectIfNotConnected();

// Ajouter aux favoris
if (isset($_GET['add'])) {
    $vehicule_id = $_GET['add'];
    
    // Vérifier si déjà en favori
    $check = $pdo->prepare("SELECT * FROM favoris WHERE user_id = ? AND vehicule_id = ?");
    $check->execute([$_SESSION['user_id'], $vehicule_id]);
    
    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO favoris (user_id, vehicule_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $vehicule_id]);
    }
    
    header('Location: favoris.php');
    exit;
}

// Retirer des favoris
if (isset($_GET['remove'])) {
    $vehicule_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND vehicule_id = ?");
    $stmt->execute([$_SESSION['user_id'], $vehicule_id]);
    header('Location: favoris.php');
    exit;
}

// Récupérer les favoris
$stmt = $pdo->prepare("
    SELECT v.*, f.created_at as favori_date 
    FROM favoris f 
    JOIN vehicules v ON f.vehicule_id = v.id 
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$favoris = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris</title>
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
            padding: 140px 20px 20px;
            position: relative;
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
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        h1 {
            color: white;
            font-size: 2.5rem;
        }
        h1 i {
            color: #e74c3c;
            margin-right: 15px;
        }
        .back-btn {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50px;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .back-btn:hover {
            background: rgba(231,76,60,0.2);
            border-color: #e74c3c;
            transform: translateX(-5px);
        }
        .cars-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 30px;
        }
        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 25px 35px rgba(0,0,0,0.5);
            position: relative;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            border-color: rgba(231,76,60,0.3);
        }
        .card-image {
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            border-radius: 15px;
            margin-bottom: 15px;
        }
        .card-image img {
            max-width: 90%;
            max-height: 180px;
            object-fit: contain;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.7));
        }
        .card-title {
            text-align: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .card-specs {
            display: flex;
            justify-content: center;
            gap: 20px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 15px;
        }
        .card-specs span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .card-specs i {
            color: #e74c3c;
        }
        .remove-fav {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            background: rgba(231,76,60,0.2);
            border: 1px solid #e74c3c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e74c3c;
            text-decoration: none;
            transition: all 0.3s;
        }
        .remove-fav:hover {
            background: #e74c3c;
            color: white;
            transform: scale(1.1);
        }
        .detail-link {
            text-align: center;
            margin-top: 15px;
        }
        .detail-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .detail-link a:hover {
            letter-spacing: 1px;
        }
        .empty {
            grid-column: 1/-1;
            text-align: center;
            padding: 80px;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(5px);
            border-radius: 30px;
            border: 2px dashed rgba(231,76,60,0.3);
            color: white;
        }
        .empty i {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .empty a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border-radius: 50px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .empty a:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(231,76,60,0.5);
        }
        @media (max-width: 768px) {
            body { padding-top: 120px; }
            .header { flex-direction: column; gap: 20px; }
            h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <video class="video-background" autoplay muted loop>
        <source src="img-vid/BMW M3 Competition  lost soul Edit  4k.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Mes Favoris</h1>
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Retour à la galerie
            </a>
        </div>
        
        <?php if (empty($favoris)): ?>
            <div class="empty">
                <i class="fas fa-heart-broken"></i>
                <h2>Aucun favori</h2>
                <p>Vous n'avez pas encore ajouté de voitures à vos favoris</p>
                <a href="index.php">Découvrir des voitures</a>
            </div>
        <?php else: ?>
            <div class="cars-container">
                <?php foreach ($favoris as $car): ?>
                    <div class="card">
                        <a href="?remove=<?= $car['id'] ?>" class="remove-fav" title="Retirer des favoris">
                            <i class="fas fa-times"></i>
                        </a>
                        
                        <div class="card-image">
                            <?php if (!empty($car['image_url']) && file_exists($car['image_url'])): ?>
                                <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['modele']) ?>">
                            <?php else: ?>
                                <img src="img-vid/default-car.png" alt="Image non disponible">
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="card-title"><?= htmlspecialchars($car['marque'] . ' ' . $car['modele']) ?></h3>
                        
                        <div class="card-specs">
                            <span><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($car['annee']) ?></span>
                            <span><i class="fas fa-palette"></i> <?= htmlspecialchars($car['couleur']) ?></span>
                        </div>
                        
                        <div class="detail-link">
                            <a href="detail.php?id=<?= $car['id'] ?>">
                                Voir détails <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>