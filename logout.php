<?php
require 'connexion.php';

// Détruire toutes les données de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header('Location: login.php');
exit;
?>