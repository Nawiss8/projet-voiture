<?php
require 'connexion.php';
redirectIfNotAdmin(); // SEULS LES ADMINS PEUVENT EXPORTER

// Récupérer toutes les voitures
$stmt = $pdo->query("SELECT * FROM vehicules ORDER BY marque, modele");
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nom du fichier
$filename = 'voitures_' . date('Y-m-d') . '.csv';

// En-têtes HTTP pour le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// En-têtes des colonnes
fputcsv($output, ['ID', 'Marque', 'Modèle', 'Année', 'Couleur', 'Prix (€)', 'Moteur', 'Puissance (ch)', 'Transmission', '0-100 km/h (s)', 'Vitesse max (km/h)', 'Vues']);

// Données
foreach ($vehicules as $car) {
    fputcsv($output, [
        $car['id'],
        $car['marque'],
        $car['modele'],
        $car['annee'],
        $car['couleur'],
        $car['prix'],
        $car['moteur'] ?? '',
        $car['puissance'] ?? '',
        $car['transmission'] ?? '',
        $car['acceleration'] ?? '',
        $car['vitesse_max'] ?? '',
        $car['vues'] ?? 0
    ]);
}

fclose($output);
exit;
?>