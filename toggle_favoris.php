<?php
require 'connexion.php';
redirectIfNotConnected();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    $user_id = $_SESSION['user_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $return_url = $_POST['return_url'] ?? 'index.php';
    
    // Vérifier si déjà en favori
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM voltures_favourites WHERE user_id = ? AND vehicle_id = ?");
    $stmt->execute([$user_id, $vehicle_id]);
    $exists = $stmt->fetchColumn();
    
    if ($exists) {
        // Supprimer des favoris
        $stmt = $pdo->prepare("DELETE FROM voltures_favourites WHERE user_id = ? AND vehicle_id = ?");
        $stmt->execute([$user_id, $vehicle_id]);
    } else {
        // Ajouter aux favoris
        $stmt = $pdo->prepare("INSERT INTO voltures_favourites (user_id, vehicle_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $vehicle_id]);
    }
    
    header("Location: $return_url");
    exit;
}

header('Location: index.php');
exit;
?>