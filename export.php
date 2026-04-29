<?php
require 'connexion.php';
redirectIfNotAdmin(); // SEULS LES ADMINS PEUVENT EXPORTER

// Récupérer toutes les voitures avec leur marque
$stmt = $pdo->query("
    SELECT v.*, m.nom as marque_nom 
    FROM voltures_vehicles v 
    JOIN voltures_marques m ON v.marque_id = m.id 
    ORDER BY m.nom, v.modelle
");
$vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nom du fichier
$filename = 'voitures_' . date('Y-m-d_H-i-s') . '.xls';

// En-têtes HTTP pour le téléchargement (fichier Excel HTML)
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Style CSS pour un meilleur rendu
echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Voitures</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th {
            background: #e74c3c;
            color: white;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #c0392b;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .marque {
            font-weight: bold;
            color: #e74c3c;
        }
        .prix {
            font-weight: bold;
            color: #27ae60;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-style: italic;
            color: #999;
        }
    </style>
</head>
<body>
    <h1>📊 Liste des véhicules</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Marque</th>
                <th>Modèle</th>
                <th>Année</th>
                <th>Couleur</th>
                <th>Prix (€)</th>
                <th>Moteur</th>
                <th>Puissance (ch)</th>
                <th>Transmission</th>
                <th>0-100 km/h (s)</th>
                <th>Vitesse max (km/h)</th>
                <th>Vues</th>
            </tr>
        </thead>
        <tbody>
';

// Données
foreach ($vehicules as $car) {
    echo '<tr>';
    echo '<td>' . $car['id'] . '</td>';
    echo '<td class="marque">' . htmlspecialchars($car['marque_nom']) . '</td>';
    echo '<td>' . htmlspecialchars($car['modelle']) . '</td>';
    echo '<td>' . $car['annee'] . '</td>';
    echo '<td>' . htmlspecialchars($car['couleur']) . '</td>';
    echo '<td class="prix">' . number_format($car['prix'], 0, ',', ' ') . ' €</td>';
    echo '<td>' . (!empty($car['moteur']) ? htmlspecialchars($car['moteur']) : '-') . '</td>';
    echo '<td>' . (!empty($car['puissance']) ? htmlspecialchars($car['puissance']) : '-') . '</td>';
    echo '<td>' . (!empty($car['transmission']) ? htmlspecialchars($car['transmission']) : '-') . '</td>';
    echo '<td>' . (!empty($car['acceleration']) ? htmlspecialchars($car['acceleration']) : '-') . '</td>';
    echo '<td>' . (!empty($car['vitesse_max']) ? htmlspecialchars($car['vitesse_max']) : '-') . '</td>';
    echo '<td>' . ($car['vues'] ?? 0) . '</td>';
    echo '</tr>';
}

echo '
        </tbody>
    </table>
    <div class="footer">
        Export généré le ' . date('d/m/Y à H:i:s') . ' | Total : ' . count($vehicules) . ' véhicule(s)
    </div>
</body>
</html>
';
exit;
?>