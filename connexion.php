<?php
// Démarrer la session (si pas déjà fait)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db = 'voitures';
$user = 'root';
$pass = ''; // ou 'root' selon ta config XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// ===== FONCTIONS D'AUTHENTIFICATION À AJOUTER =====
function estConnecte() {
    return isset($_SESSION['user_id']);
}

function estAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirectIfNotConnected() {
    if (!estConnecte()) {
        header('Location: login.php');
        exit;
    }
}

function redirectIfNotAdmin() {
    if (!estAdmin()) {
        header('Location: index.php');
        exit;
    }
}
?>