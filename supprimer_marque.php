<?php
require 'connexion.php';
redirectIfNotAdmin();

$id_marque = $_GET['id'] ?? 0;

if ($id_marque) {
    // Récupérer le nom de la marque
    $stmt = $pdo->prepare("SELECT nom FROM voltures_marques WHERE id = ?");
    $stmt->execute([$id_marque]);
    $marque = $stmt->fetch();
    
    if ($marque) {
        // Récupérer toutes les images des véhicules de cette marque
        $stmt = $pdo->prepare("SELECT image_url FROM voltures_vehicles WHERE marque_id = ?");
        $stmt->execute([$id_marque]);
        $vehicules = $stmt->fetchAll();
        
        // Supprimer les images des véhicules
        foreach ($vehicules as $v) {
            if (!empty($v['image_url']) && file_exists($v['image_url'])) {
                unlink($v['image_url']);
            }
        }
        
        // Supprimer les favoris liés (grâce à ON DELETE CASCADE, normalement automatique)
        $stmt = $pdo->prepare("DELETE FROM voltures_favourites WHERE vehicle_id IN (SELECT id FROM voltures_vehicles WHERE marque_id = ?)");
        $stmt->execute([$id_marque]);
        
        // Supprimer les véhicules
        $stmt = $pdo->prepare("DELETE FROM voltures_vehicles WHERE marque_id = ?");
        $stmt->execute([$id_marque]);
        
        // Supprimer le logo de la marque
        $marque_nom_lower = strtolower($marque['nom']);
        $logo_extensions = ['png', 'jpg', 'jpeg'];
        foreach ($logo_extensions as $ext) {
            $logo_path = 'img-vid/' . $marque_nom_lower . '-logo.' . $ext;
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
        }
        
        // Supprimer la marque
        $stmt = $pdo->prepare("DELETE FROM voltures_marques WHERE id = ?");
        $stmt->execute([$id_marque]);
        
        header('Location: index.php?success=4');
        exit;
    }
}

header('Location: index.php');
exit;
?>