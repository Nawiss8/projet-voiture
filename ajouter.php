<?php
require 'connexion.php';
redirectIfNotAdmin(); // SEULS LES ADMINS PEUVENT AJOUTER

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Gestion de la marque (existante ou nouvelle)
    if ($_POST['marque'] === 'nouvelle') {
        $marque = $_POST['nouvelle_marque'] ?? '';
        
        // Upload du logo si fourni pour la nouvelle marque
        if (isset($_FILES['logo_marque']) && $_FILES['logo_marque']['error'] === UPLOAD_ERR_OK) {
            $file_type = $_FILES['logo_marque']['type'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'img-vid/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $extension = strtolower(pathinfo($_FILES['logo_marque']['name'], PATHINFO_EXTENSION));
                $logo_name = strtolower(str_replace(' ', '-', $marque)) . '-logo.' . $extension;
                $target_file = $upload_dir . $logo_name;
                
                move_uploaded_file($_FILES['logo_marque']['tmp_name'], $target_file);
            }
        }
    } else {
        $marque = $_POST['marque'];
    }
    
    // Récupérer TOUS les champs du formulaire
    $modele = $_POST['modele'] ?? '';
    $annee = $_POST['annee'] ?? '';
    $couleur = $_POST['couleur'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $moteur = $_POST['moteur'] ?? '';
    $puissance = $_POST['puissance'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $acceleration = $_POST['acceleration'] ?? '';
    $vitesse_max = $_POST['vitesse_max'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;

    // Gestion de l'image de la voiture
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_type = $_FILES['image']['type'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        
        if (!in_array($file_type, $allowed_types)) {
            die("Erreur : Seuls les fichiers JPG et PNG sont autorisés pour l'image de la voiture !");
        }
        
        $upload_dir = 'img-vid/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = $target_file;
        } else {
            die("Erreur lors de l'upload de l'image.");
        }
    } else {
        die("Erreur : Image de la voiture obligatoire !");
    }

    // Validation des champs obligatoires
    if ($marque && $modele && $annee && $couleur && $prix) {
        // Requête COMPLÈTE avec TOUTES les colonnes
        $sql = "INSERT INTO vehicules (
                    marque, modele, annee, couleur, prix, image_url, 
                    moteur, puissance, transmission, acceleration, vitesse_max, user_id
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?
                )";
        
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $marque, $modele, $annee, $couleur, $prix, $image_url,
                $moteur, $puissance, $transmission, $acceleration, $vitesse_max, $user_id
            ]);
            
            header('Location: index.php?success=1');
            exit;
            
        } catch (PDOException $e) {
            die("Erreur SQL : " . $e->getMessage());
        }
    } else {
        die("Veuillez remplir tous les champs obligatoires (marque, modèle, année, couleur, prix).");
    }
} else {
    header('Location: index.php');
    exit;
}
?>