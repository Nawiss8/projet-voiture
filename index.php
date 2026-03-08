<?php
require 'connexion.php';

// Récupérer toutes les voitures
$stmt = $pdo->query("SELECT * FROM vehicules ORDER BY marque, modele");
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les marques uniques
$marques = [];
foreach ($vehicules as $v) {
    if (!in_array($v['marque'], $marques)) {
        $marques[] = $v['marque'];
    }
}
sort($marques);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery de Voitures</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
            transition: 400ms;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #0a0a0a;
            padding-top: 140px;
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

        /* Navigation */
        .brand-nav {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 95%;
            max-width: 1300px;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 60px;
            z-index: 100;
            padding: 15px 25px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .nav-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .brand-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 40px;
            transition: all 0.3s ease;
            min-width: 80px;
        }

        .brand-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
        }

        .brand-item.active {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .brand-logo-box {
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.2);
            padding: 8px;
            margin-bottom: 5px;
        }

        .brand-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-item span {
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* GRILLE */
        .cars-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 30px;
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* CARTE */
        .card {
            width: 100%;
            max-width: 360px;
            height: 500px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 25px 35px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            position: relative;
            cursor: pointer;
        }

        .card-image {
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            border-radius: 15px;
            margin-bottom: 15px;
            pointer-events: none;
        }

        .card-image img {
            max-width: 90%;
            max-height: 180px;
            object-fit: contain;
            transition: transform 0.4s ease;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.7));
        }

        .card-title {
            text-align: center;
            color: #fff;
            font-weight: 600;
            font-size: 1.5rem;
            margin: 5px 0 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 36px;
            pointer-events: none;
        }

        .card-specs-mini {
            display: flex;
            justify-content: center;
            gap: 20px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-bottom: 10px;
            height: 24px;
            pointer-events: none;
        }

        .card-specs-mini span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-specs-mini i {
            color: #e74c3c;
        }

        /* BOUTONS D'ACTION - UNIQUEMENT POUR ADMIN */
        .card-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            z-index: 20;
            opacity: <?= estAdmin() ? '0' : '0' ?>;
            transition: all 0.3s ease;
        }

        .card:hover .card-actions {
            opacity: <?= estAdmin() ? '1' : '0' ?>;
        }

        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            color: white;
            position: relative;
            z-index: 30;
        }

        .edit-btn {
            background: rgba(52, 152, 219, 0.8);
        }

        .edit-btn:hover {
            background: #3498db;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(52, 152, 219, 0.5);
        }

        .delete-btn {
            background: rgba(231, 76, 60, 0.8);
        }

        .delete-btn:hover {
            background: #e74c3c;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(231, 76, 60, 0.5);
        }

        /* VUES SUR LA CARTE */
        .card-views {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            z-index: 15;
            pointer-events: none;
        }

        .card-views i {
            color: #e74c3c;
            margin-right: 5px;
        }

        /* CONTENU HOVER */
        .card-content {
            position: absolute;
            left: 20px;
            right: 20px;
            bottom: 20px;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 18px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s ease;
            z-index: 25;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            max-height: 280px;
            overflow: hidden;
            pointer-events: none;
        }

        .card-content-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            color: #e74c3c;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .info-value {
            color: #fff;
            font-weight: 500;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .non-specifie {
            color: rgba(255, 255, 255, 0.3);
            font-style: italic;
        }

        .prix-item {
            grid-column: span 2;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .prix-label {
            color: #e74c3c;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .prix-value {
            color: #e74c3c;
            font-weight: 700;
            font-size: 1.2rem;
        }

        /* EFFETS HOVER */
        .card:hover .card-image {
            transform: translateY(-60px);
        }

        .card:hover .card-title {
            transform: translateY(-60px);
        }

        .card:hover .card-specs-mini {
            transform: translateY(-60px);
        }

        .card:hover .card-image img {
            transform: scale(1.2) rotate(-5deg);
        }

        .card:hover .card-content {
            opacity: 1;
            transform: translateY(0);
        }

        /* Bouton ajout (droite) - UNIQUEMENT POUR ADMIN */
        .add-button-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: <?= estAdmin() ? 'block' : 'none' ?>;
        }

        .add-button {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            font-size: 32px;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .add-button:hover {
            transform: rotate(180deg) scale(1.1);
            box-shadow: 0 15px 35px rgba(231, 76, 60, 0.6);
        }

        /* Bouton export (gauche) - UNIQUEMENT POUR ADMIN */
        .export-button-container {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            display: <?= estAdmin() ? 'block' : 'none' ?>;
        }

        .export-button {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            font-size: 32px;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .export-button:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(52, 152, 219, 0.6);
        }

        /* Overlay formulaire */
        .form-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            max-width: 600px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            padding: 30px;
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 30px;
            color: #fff;
            position: relative;
        }

        .close-button {
            position: absolute;
            top: 20px;
            right: 25px;
            font-size: 28px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.5);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .close-button:hover {
            color: #e74c3c;
            transform: rotate(90deg);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: white;
            font-size: 2rem;
        }

        .form-container h2 i {
            color: #e74c3c;
            margin-right: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-group label span {
            color: #e74c3c;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #e74c3c;
        }

        .form-group input[type="file"] {
            padding: 10px;
            border: 2px dashed rgba(231, 76, 60, 0.3);
        }

        .form-group button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .form-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.5);
        }

        .nouvelle-marque-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin: 10px 0;
            border: 2px dashed rgba(231, 76, 60, 0.3);
            display: none;
        }

        .nouvelle-marque-section.visible {
            display: block;
        }

        .logo-preview {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 15px;
        }

        .logo-preview-box {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px solid rgba(255, 255, 255, 0.2);
            padding: 15px;
        }

        .logo-preview-box img {
            max-width: 100%;
            max-height: 100%;
            filter: brightness(0) invert(1);
        }

        /* Overlay de confirmation */
        .confirm-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 3000;
            justify-content: center;
            align-items: center;
        }

        .confirm-box {
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 30px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            color: white;
            animation: slideUp 0.3s ease;
        }

        .confirm-box i {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .confirm-box h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .confirm-box p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 25px;
        }

        .confirm-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .confirm-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .confirm-yes {
            background: #e74c3c;
            color: white;
        }

        .confirm-yes:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);
        }

        .confirm-no {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .confirm-no:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Message de succès */
        .success-message {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            z-index: 2000;
            animation: slideDown 0.5s ease, fadeOut 0.5s ease 2.5s forwards;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translate(-50%, -50px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }

        @keyframes fadeOut {
            to { opacity: 0; transform: translate(-50%, -20px); visibility: hidden; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            body { padding-top: 120px; }
            
            .card {
                max-width: 100%;
                height: auto;
                min-height: 500px;
            }
            
            .card-actions {
                opacity: 1 !important;
            }
            
            .card-content {
                position: relative;
                opacity: 1;
                transform: none;
                margin-top: 15px;
            }
            
            .card:hover .card-image,
            .card:hover .card-title,
            .card:hover .card-specs-mini {
                transform: none;
            }
            
            .card:hover .card-image img {
                transform: none;
            }

            .add-button-container, .export-button-container {
                bottom: 20px;
            }

            .add-button, .export-button {
                width: 55px;
                height: 55px;
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <video class="video-background" autoplay muted loop>
        <source src="img-vid/BMW M3 Competition  lost soul Edit  4k.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> 
            <?php 
            if ($_GET['success'] == 1) echo "Véhicule ajouté avec succès !";
            if ($_GET['success'] == 2) echo "Véhicule modifié avec succès !";
            if ($_GET['success'] == 3) echo "Véhicule supprimé avec succès !";
            ?>
        </div>
    <?php endif; ?>

    <!-- Menu utilisateur avec badge de rôle -->
    <div style="position: fixed; top: 90px; right: 30px; z-index: 1000;">
        <?php if (estConnecte()): ?>
            <div style="background: rgba(0, 0, 0, 0.7);
                        backdrop-filter: blur(10px);
                        -webkit-backdrop-filter: blur(10px);
                        border: 1px solid rgba(255, 255, 255, 0.1);
                        border-radius: 50px;
                        padding: 8px 20px;
                        display: flex;
                        align-items: center;
                        gap: 15px;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);">
                
                <!-- Icône utilisateur avec nom -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 35px; height: 35px; 
                                background: linear-gradient(135deg, #e74c3c, #c0392b);
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;">
                        <i class="fas fa-user" style="color: white; font-size: 16px;"></i>
                    </div>
                    <span style="color: white; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <?= htmlspecialchars($_SESSION['user_nom']) ?>
                        
                        <!-- BADGE DE RÔLE -->
                        <?php if (estAdmin()): ?>
                            <span style="background: #e74c3c; 
                                       color: white; 
                                       padding: 4px 10px; 
                                       border-radius: 20px; 
                                       font-size: 0.7rem; 
                                       font-weight: 600;
                                       display: inline-flex;
                                       align-items: center;
                                       gap: 4px;
                                       text-transform: uppercase;">
                                <i class="fas fa-crown" style="font-size: 0.6rem;"></i> Admin
                            </span>
                        <?php else: ?>
                            <span style="background: rgba(255,255,255,0.1); 
                                       border: 1px solid rgba(255,255,255,0.2);
                                       color: white; 
                                       padding: 4px 10px; 
                                       border-radius: 20px; 
                                       font-size: 0.7rem; 
                                       font-weight: 600;
                                       display: inline-flex;
                                       align-items: center;
                                       gap: 4px;">
                                <i class="fas fa-user" style="font-size: 0.6rem;"></i> User
                            </span>
                        <?php endif; ?>
                    </span>
                </div>

                <!-- Séparateur -->
                <div style="width: 1px; height: 25px; background: rgba(255,255,255,0.2);"></div>

                <!-- Lien favoris (pour tous les connectés) -->
                <a href="favoris.php" style="color: white; 
                                             text-decoration: none; 
                                             display: flex; 
                                             align-items: center;
                                             gap: 5px;
                                             padding: 5px 10px;
                                             border-radius: 30px;
                                             transition: all 0.3s;
                                             background: rgba(231, 76, 60, 0.1);"
                   onmouseover="this.style.background='rgba(231, 76, 60, 0.2)'"
                   onmouseout="this.style.background='rgba(231, 76, 60, 0.1)'">
                    <i class="fas fa-heart" style="color: #e74c3c; font-size: 16px;"></i>
                    <span style="font-size: 0.9rem;">Favoris</span>
                </a>

                <!-- Bouton déconnexion (pour tous) -->
                <a href="logout.php" 
                   style="color: white; 
                          text-decoration: none; 
                          display: flex; 
                          align-items: center;
                          gap: 8px;
                          padding: 8px 15px;
                          border-radius: 30px;
                          background: linear-gradient(135deg, #e74c3c, #c0392b);
                          transition: all 0.3s;
                          box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);"
                   onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 20px rgba(231, 76, 60, 0.5)'"
                   onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 5px 15px rgba(231, 76, 60, 0.3)'">
                    <i class="fas fa-sign-out-alt" style="font-size: 14px;"></i>
                    <span style="font-weight: 500;">Déconnexion</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Menu pour non connectés -->
            <div style="background: rgba(0, 0, 0, 0.7);
                        backdrop-filter: blur(10px);
                        -webkit-backdrop-filter: blur(10px);
                        border: 1px solid rgba(255, 255, 255, 0.1);
                        border-radius: 50px;
                        padding: 8px 15px;
                        display: flex;
                        gap: 10px;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);">
                <a href="login.php" 
                   style="color: white; 
                          text-decoration: none; 
                          padding: 8px 20px;
                          border-radius: 30px;
                          background: rgba(231, 76, 60, 0.1);
                          transition: all 0.3s;
                          display: flex;
                          align-items: center;
                          gap: 8px;"
                   onmouseover="this.style.background='rgba(231, 76, 60, 0.2)'"
                   onmouseout="this.style.background='rgba(231, 76, 60, 0.1)'">
                    <i class="fas fa-sign-in-alt" style="color: #e74c3c;"></i>
                    <span>Connexion</span>
                </a>
                <a href="register.php" 
                   style="color: white; 
                          text-decoration: none; 
                          padding: 8px 20px;
                          border-radius: 30px;
                          background: linear-gradient(135deg, #e74c3c, #c0392b);
                          transition: all 0.3s;
                          display: flex;
                          align-items: center;
                          gap: 8px;
                          box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);"
                   onmouseover="this.style.transform='scale(1.05)'"
                   onmouseout="this.style.transform='scale(1)'">
                    <i class="fas fa-user-plus"></i>
                    <span>Inscription</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Navigation -->
    <nav class="brand-nav">
        <div class="nav-container">
            <div class="brand-item active" data-brand="all">
                <div class="brand-logo-box">
                    <img src="img-vid/all-logo.png" alt="Toutes">
                </div>
                <span>TOUTES</span>
            </div>
            
            <?php foreach ($marques as $marque): 
                $marque_lower = strtolower($marque);
                $logo_file = 'img-vid/' . $marque_lower . '-logo.png';
            ?>
                <div class="brand-item" data-brand="<?= $marque_lower ?>">
                    <div class="brand-logo-box">
                        <?php if (file_exists($logo_file)): ?>
                            <img src="<?= $logo_file ?>" alt="<?= $marque ?>">
                        <?php else: ?>
                            <i class="fas fa-car" style="color: white; font-size: 24px;"></i>
                        <?php endif; ?>
                    </div>
                    <span><?= strtoupper($marque) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- Bouton export (gauche) - UNIQUEMENT POUR ADMIN -->
    <?php if (estAdmin()): ?>
    <div class="export-button-container">
        <a href="export.php" class="export-button">
            <i class="fas fa-download"></i>
        </a>
    </div>
    <?php endif; ?>

    <!-- Bouton ajout (droite) - UNIQUEMENT POUR ADMIN -->
    <?php if (estAdmin()): ?>
    <div class="add-button-container">
        <button class="add-button" onclick="openAddForm()">
            <i class="fas fa-plus"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Conteneur des cartes -->
    <main class="cars-container">
        <?php if (empty($vehicules)): ?>
            <div style="grid-column:1/-1; text-align:center; padding:80px; color:white;">
                <i class="fas fa-car" style="font-size:4rem; color:#e74c3c;"></i>
                <p>Aucune voiture dans la galerie. Ajoutez-en avec le bouton + !</p>
            </div>
        <?php else: ?>
            <?php foreach ($vehicules as $car): ?>
                <div class="card" onclick="window.location.href='detail.php?id=<?= $car['id'] ?>'" data-brand="<?= strtolower($car['marque']) ?>">
                    
                    <!-- BOUTONS D'ACTION - UNIQUEMENT POUR ADMIN -->
                    <?php if (estAdmin()): ?>
                    <div class="card-actions">
                        <div class="action-btn edit-btn" onclick="event.stopPropagation(); openEditForm(<?= $car['id'] ?>)">
                            <i class="fas fa-pen"></i>
                        </div>
                        <div class="action-btn delete-btn" onclick="event.stopPropagation(); confirmDelete(<?= $car['id'] ?>, '<?= htmlspecialchars($car['marque'] . ' ' . $car['modele']) ?>')">
                            <i class="fas fa-trash"></i>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- COMPTEUR DE VUES -->
                    <div class="card-views">
                        <i class="fas fa-eye"></i> <?= number_format($car['vues'] ?? 0, 0, ',', ' ') ?>
                    </div>
                    
                    <div class="card-image">
                        <?php if (!empty($car['image_url']) && file_exists($car['image_url'])): ?>
                            <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['modele']) ?>">
                        <?php else: ?>
                            <img src="img-vid/default-car.png" alt="Image non disponible">
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="card-title"><?= htmlspecialchars($car['marque'] . ' ' . $car['modele']) ?></h3>
                    
                    <div class="card-specs-mini">
                        <span><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($car['annee']) ?></span>
                        <span><i class="fas fa-palette"></i> <?= htmlspecialchars($car['couleur']) ?></span>
                    </div>
                    
                    <!-- CONTENU HOVER -->
                    <div class="card-content">
                        <div class="card-content-grid">
                            <div class="info-item">
                                <span class="info-label">MOTEUR</span>
                                <span class="info-value"><?= !empty($car['moteur']) ? htmlspecialchars($car['moteur']) : '<span class="non-specifie">Non spécifié</span>' ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">PUISSANCE</span>
                                <span class="info-value"><?= !empty($car['puissance']) ? htmlspecialchars($car['puissance']) . ' ch' : '<span class="non-specifie">Non spécifié</span>' ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">TRANSMISSION</span>
                                <span class="info-value"><?= !empty($car['transmission']) ? htmlspecialchars($car['transmission']) : '<span class="non-specifie">Non spécifié</span>' ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">0-100 KM/H</span>
                                <span class="info-value"><?= !empty($car['acceleration']) ? htmlspecialchars($car['acceleration']) . ' s' : '<span class="non-specifie">Non spécifié</span>' ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">VITESSE MAX</span>
                                <span class="info-value"><?= !empty($car['vitesse_max']) ? htmlspecialchars($car['vitesse_max']) . ' km/h' : '<span class="non-specifie">Non spécifié</span>' ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">ANNÉE</span>
                                <span class="info-value"><?= htmlspecialchars($car['annee']) ?></span>
                            </div>
                        </div>
                        
                        <div class="prix-item">
                            <span class="prix-label">PRIX</span>
                            <span class="prix-value"><?= number_format($car['prix'], 0, ',', ' ') ?> €</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- Overlay de confirmation de suppression -->
    <div id="confirmOverlay" class="confirm-overlay">
        <div class="confirm-box">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Confirmer la suppression</h3>
            <p id="confirmMessage">Êtes-vous sûr de vouloir supprimer ce véhicule ?</p>
            <div class="confirm-buttons">
                <button class="confirm-btn confirm-yes" onclick="executeDelete()">Oui, supprimer</button>
                <button class="confirm-btn confirm-no" onclick="closeConfirm()">Annuler</button>
            </div>
        </div>
    </div>

    <!-- Formulaire d'ajout/modification -->
    <div id="formOverlay" class="form-overlay">
        <div class="form-container">
            <span class="close-button" onclick="closeForm()">&times;</span>
            <h2 id="formTitle"><i class="fas fa-car"></i> Ajouter un véhicule</h2>
            
            <form id="vehicleForm" action="ajouter.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="vehicle_id" id="vehicle_id" value="">
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Marque <span>*</span></label>
                        <select name="marque" id="marque" required onchange="handleMarqueChange(this)">
                            <option value="">Sélectionnez une marque</option>
                            <?php foreach ($marques as $marque): ?>
                                <option value="<?= $marque ?>"><?= $marque ?></option>
                            <?php endforeach; ?>
                            <option value="nouvelle">➕ Nouvelle marque...</option>
                        </select>
                    </div>

                    <div id="nouvelleMarqueSection" class="nouvelle-marque-section">
                        <div class="form-group">
                            <label>Nom de la nouvelle marque <span>*</span></label>
                            <input type="text" name="nouvelle_marque" id="nouvelle_marque" placeholder="Ex: Toyota">
                        </div>
                        
                        <div class="logo-preview">
                            <div class="logo-preview-box">
                                <img src="img-vid/default-logo.png" alt="Aperçu" id="logoPreview">
                            </div>
                            <div>
                                <label>Logo (optionnel)</label>
                                <input type="file" name="logo_marque" accept=".jpg,.jpeg,.png" onchange="previewLogo(this)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Modèle <span>*</span></label>
                        <input type="text" name="modele" id="modele" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Année <span>*</span></label>
                        <input type="number" name="annee" id="annee" min="1900" max="2100" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Couleur <span>*</span></label>
                        <input type="text" name="couleur" id="couleur" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Prix (€) <span>*</span></label>
                        <input type="number" step="0.01" name="prix" id="prix" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Image voiture</label>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png" id="image">
                        <small style="color:rgba(255,255,255,0.5);">Laissez vide pour garder l'image actuelle</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Moteur</label>
                        <input type="text" name="moteur" id="moteur">
                    </div>
                    
                    <div class="form-group">
                        <label>Puissance (ch)</label>
                        <input type="number" name="puissance" id="puissance">
                    </div>
                    
                    <div class="form-group">
                        <label>Transmission</label>
                        <select name="transmission" id="transmission">
                            <option value="">-</option>
                            <option value="Manuelle">Manuelle</option>
                            <option value="Automatique">Automatique</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>0-100 km/h (s)</label>
                        <input type="number" step="0.1" name="acceleration" id="acceleration">
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Vitesse max (km/h)</label>
                        <input type="number" name="vitesse_max" id="vitesse_max">
                    </div>
                </div>
                
                <button type="submit" id="submitBtn">Ajouter</button>
            </form>
        </div>
    </div>

    <script>
        // Données des véhicules pour la modification
        const vehicles = <?= json_encode($vehicules) ?>;
        let deleteId = null;

        function openAddForm() {
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-car"></i> Ajouter un véhicule';
            document.getElementById('vehicle_id').value = '';
            document.getElementById('vehicleForm').action = 'ajouter.php';
            document.getElementById('submitBtn').textContent = 'Ajouter';
            
            // Reset form
            document.getElementById('marque').value = '';
            document.getElementById('modele').value = '';
            document.getElementById('annee').value = '';
            document.getElementById('couleur').value = '';
            document.getElementById('prix').value = '';
            document.getElementById('moteur').value = '';
            document.getElementById('puissance').value = '';
            document.getElementById('transmission').value = '';
            document.getElementById('acceleration').value = '';
            document.getElementById('vitesse_max').value = '';
            document.getElementById('image').required = true;
            
            document.getElementById('nouvelleMarqueSection').classList.remove('visible');
            
            document.getElementById('formOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function openEditForm(id) {
            const vehicle = vehicles.find(v => v.id == id);
            
            document.getElementById('formTitle').innerHTML = '<i class="fas fa-car"></i> Modifier ' + vehicle.marque + ' ' + vehicle.modele;
            document.getElementById('vehicle_id').value = vehicle.id;
            document.getElementById('vehicleForm').action = 'modifier.php';
            document.getElementById('submitBtn').textContent = 'Modifier';
            
            // Remplir le formulaire
            document.getElementById('marque').value = vehicle.marque;
            document.getElementById('modele').value = vehicle.modele;
            document.getElementById('annee').value = vehicle.annee;
            document.getElementById('couleur').value = vehicle.couleur;
            document.getElementById('prix').value = vehicle.prix;
            document.getElementById('moteur').value = vehicle.moteur || '';
            document.getElementById('puissance').value = vehicle.puissance || '';
            document.getElementById('transmission').value = vehicle.transmission || '';
            document.getElementById('acceleration').value = vehicle.acceleration || '';
            document.getElementById('vitesse_max').value = vehicle.vitesse_max || '';
            document.getElementById('image').required = false;
            
            document.getElementById('formOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeForm() {
            document.getElementById('formOverlay').style.display = 'none';
            document.body.style.overflow = 'auto';
            resetForm();
        }

        function confirmDelete(id, name) {
            deleteId = id;
            document.getElementById('confirmMessage').textContent = `Êtes-vous sûr de vouloir supprimer ${name} ?`;
            document.getElementById('confirmOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeConfirm() {
            document.getElementById('confirmOverlay').style.display = 'none';
            document.body.style.overflow = 'auto';
            deleteId = null;
        }

        function executeDelete() {
            if (deleteId) {
                window.location.href = 'supprimer.php?id=' + deleteId;
            }
        }

        window.onclick = function(event) {
            const overlay = document.getElementById('formOverlay');
            const confirmOverlay = document.getElementById('confirmOverlay');
            
            if (event.target == overlay) closeForm();
            if (event.target == confirmOverlay) closeConfirm();
        }

        function handleMarqueChange(select) {
            const section = document.getElementById('nouvelleMarqueSection');
            const input = document.getElementById('nouvelle_marque');
            
            if (select.value === 'nouvelle') {
                section.classList.add('visible');
                input.required = true;
            } else {
                section.classList.remove('visible');
                input.required = false;
            }
        }

        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function resetForm() {
            document.getElementById('marque').value = '';
            document.getElementById('nouvelleMarqueSection').classList.remove('visible');
            document.getElementById('nouvelle_marque').value = '';
            document.getElementById('logoPreview').src = 'img-vid/default-logo.png';
        }

        // Filtrage
        document.addEventListener("DOMContentLoaded", function() {
            const items = document.querySelectorAll(".brand-item");
            const cards = document.querySelectorAll(".card");

            items.forEach(item => {
                item.addEventListener("click", () => {
                    items.forEach(i => i.classList.remove("active"));
                    item.classList.add("active");
                    
                    const brand = item.getAttribute("data-brand");
                    
                    cards.forEach(card => {
                        card.style.display = brand === "all" || card.getAttribute("data-brand") === brand ? "flex" : "none";
                    });
                });
            });
        });
    </script>
</body>
</html>