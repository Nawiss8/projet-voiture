<?php
require 'connexion.php';
redirectIfNotAdmin();

function getOrCreateMarque($pdo, $nom_marque, $logo_url = null) {
    $stmt = $pdo->prepare("SELECT id FROM voltures_marques WHERE nom = ?");
    $stmt->execute([$nom_marque]);
    $marque = $stmt->fetch();
    
    if ($marque) {
        return $marque['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO voltures_marques (nom, logo_url) VALUES (?, ?)");
        $stmt->execute([$nom_marque, $logo_url]);
        return $pdo->lastInsertId();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $logo_path = null;
    $logo_inserted = false;
    
    if ($_POST['marque'] === 'nouvelle') {
        $marque_nom = $_POST['nouvelle_marque'] ?? '';
        
        if (isset($_FILES['logo_marque']) && $_FILES['logo_marque']['error'] === UPLOAD_ERR_OK) {
            $file_type = $_FILES['logo_marque']['type'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'img-vid/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $extension = strtolower(pathinfo($_FILES['logo_marque']['name'], PATHINFO_EXTENSION));
                $logo_name = strtolower(str_replace(' ', '-', $marque_nom)) . '-logo.' . $extension;
                $target_file = $upload_dir . $logo_name;
                
                if (move_uploaded_file($_FILES['logo_marque']['tmp_name'], $target_file)) {
                    $logo_path = $target_file;
                    $logo_inserted = true;
                }
            }
        }
    } else {
        $marque_nom = $_POST['marque'];
    }
    
    $modele = $_POST['modele'] ?? '';
    $annee = $_POST['annee'] ?? '';
    $couleur = $_POST['couleur'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $moteur = $_POST['moteur'] ?? '';
    $puissance = $_POST['puissance'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $acceleration = $_POST['acceleration'] ?? '';
    $vitesse_max = $_POST['vitesse_max'] ?? '';

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

    if ($marque_nom && $modele && $annee && $couleur && $prix) {
        
        $marque_id = getOrCreateMarque($pdo, $marque_nom, $logo_path);
        
        $sql = "INSERT INTO voltures_vehicles (
                    marque_id, modelle, annee, couleur, prix, image_url, 
                    moteur, puissance, transmission, acceleration, vitesse_max
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?
                )";
        
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                $marque_id, $modele, $annee, $couleur, $prix, $image_url,
                $moteur, $puissance, $transmission, $acceleration, $vitesse_max
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