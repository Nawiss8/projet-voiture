# Projet Gallery Voitures - BTS SIO

## 📋 Description
Application web de gestion de collection de voitures permettant d'ajouter, modifier, supprimer et visualiser des véhicules avec leurs caractéristiques techniques.

## 🚀 Fonctionnalités

### Fonctionnalités principales
- ✅ **CRUD complet** : Ajout, modification, suppression de véhicules
- ✅ **Galerie interactive** : Affichage sous forme de cartes avec effets hover
- ✅ **Filtrage par marque** : Navigation dynamique par marque
- ✅ **Upload d'images** : Support JPG/PNG avec validation
- ✅ **Gestion des nouvelles marques** : Ajout de marques avec logo optionnel

### Fonctionnalités avancées
- ✅ **Page de détail** : Vue détaillée de chaque véhicule avec compteur de vues
- ✅ **Export CSV** : Export de la collection complète
- ✅ **Confirmation suppression** : Boîte de dialogue avant suppression
- ✅ **Compteur de vues** : Statistiques de consultation

## 🛠️ Technologies utilisées
- **Frontend** : HTML5, CSS3, JavaScript, Font Awesome
- **Backend** : PHP 8+ (PDO)
- **Base de données** : MySQL
- **Serveur** : XAMPP / WAMP

## 📊 Structure de la base de données

```sql
CREATE TABLE vehicules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  marque VARCHAR(50) NOT NULL,
  modele VARCHAR(50) NOT NULL,
  annee YEAR NOT NULL,
  couleur VARCHAR(30) NOT NULL,
  prix DECIMAL(10,2) NOT NULL,
  image_url VARCHAR(255),
  moteur VARCHAR(100),
  puissance INT,
  transmission VARCHAR(50),
  acceleration DECIMAL(3,1),
  vitesse_max INT,
  vues INT DEFAULT 0
);# projet-voiture
