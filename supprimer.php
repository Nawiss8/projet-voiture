<?php
require 'connexion.php';
redirectIfNotAdmin(); // SEULS LES ADMINS PEUVENT SUPPRIMER

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer l'URL de l'image avant de supprimer
    $stmt = $pdo->prepare("SELECT image_url FROM vehicules WHERE id = ?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch();
    
    // Supprimer l'image si elle existe
    if ($vehicle && !empty($vehicle['image_url']) && file_exists($vehicle['image_url'])) {
        unlink($vehicle['image_url']);
    }
    
    // Supprimer le véhicule
    $stmt = $pdo->prepare("DELETE FROM vehicules WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: index.php?success=3');
    exit;
}
?>