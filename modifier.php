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
    $id = $_POST['vehicle_id'] ?? 0;
    
    // Gestion de la marque
    $logo_path = null;
    
    if ($_POST['marque'] === 'nouvelle') {
        $marque_nom = $_POST['nouvelle_marque'] ?? '';
        
        // Upload du logo si fourni
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

    // Récupérer ou créer l'ID de la marque
    $marque_id = getOrCreateMarque($pdo, $marque_nom, $logo_path);

    // Gestion de l'image
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_type = $_FILES['image']['type'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'img-vid/';
            $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Supprimer l'ancienne image
                $stmt = $pdo->prepare("SELECT image_url FROM voltures_vehicles WHERE id = ?");
                $stmt->execute([$id]);
                $old = $stmt->fetch();
                if ($old && !empty($old['image_url']) && file_exists($old['image_url'])) {
                    unlink($old['image_url']);
                }
                $image_url = $target_file;
            }
        }
    }

    // Construire la requête UPDATE
    $sql = "UPDATE voltures_vehicles SET 
            marque_id = ?, modelle = ?, annee = ?, couleur = ?, prix = ?,
            moteur = ?, puissance = ?, transmission = ?, acceleration = ?, vitesse_max = ?";
    
    $params = [$marque_id, $modele, $annee, $couleur, $prix, $moteur, $puissance, $transmission, $acceleration, $vitesse_max];
    
    if ($image_url) {
        $sql .= ", image_url = ?";
        $params[] = $image_url;
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    header('Location: index.php?success=2');
    exit;
}
?>