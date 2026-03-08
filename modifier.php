<?php
require 'connexion.php';
redirectIfNotAdmin(); // SEULS LES ADMINS PEUVENT MODIFIER

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['vehicle_id'] ?? 0;
    
    // Récupérer les données
    $marque = $_POST['marque'] ?? '';
    $modele = $_POST['modele'] ?? '';
    $annee = $_POST['annee'] ?? '';
    $couleur = $_POST['couleur'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $moteur = $_POST['moteur'] ?? '';
    $puissance = $_POST['puissance'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $acceleration = $_POST['acceleration'] ?? '';
    $vitesse_max = $_POST['vitesse_max'] ?? '';

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
                $image_url = $target_file;
            }
        }
    }

    // Construire la requête UPDATE
    $sql = "UPDATE vehicules SET 
            marque = ?, modele = ?, annee = ?, couleur = ?, prix = ?,
            moteur = ?, puissance = ?, transmission = ?, acceleration = ?, vitesse_max = ?";
    
    $params = [$marque, $modele, $annee, $couleur, $prix, $moteur, $puissance, $transmission, $acceleration, $vitesse_max];
    
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