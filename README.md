# StarTrip 🚀

## 🎯 Aperçu du Projet

StarTrip est une plateforme de réservation de voyages interplanétaires inspirée de Star Wars. Cette application web permet aux utilisateurs de planifier et de réserver des voyages entre différentes planètes de la galaxie, avec des fonctionnalités conçues pour rendre la planification des voyages spatiaux intuitive et efficace.

## 🌐 Projet final
Vous pouvez retrouver le projet final à l'adresse ci-dessous : <br>
🔗 [https://startrip.julien-synaeve.fr](https://startrip.julien-synaeve.fr)

## ✨ Fonctionnalités

### Fonctionnalités Principales
- 🔍 Recherche de voyages entre planètes
- 🛫 Multiples options d'itinéraires avec différents vaisseaux
- 🛒 Système de panier
- 🎫 Système de gestion des réservations
- 🗺️ Visualisation interactive de la carte galactique

### Expérience Utilisateur
- 📱 Design responsive pour tous les appareils
- 📋 Vue détaillée des voyages
- ✂️ Système d'annulation des réservations

## 🛠️ Technologies Utilisées

### ⚙️ Backend
- PHP
- MySQL
- Java
- C

### 🎨 Frontend
- HTML
- CSS
- JS

### 📚 Librairies
- Bootstrap
- Leaflet

## 🚀 Pour Commencer

### 1️⃣ Prérequis
- Serveur PHP
- Serveur MySQL
- Navigateur Web

### 2️⃣ Installation

1. Clonez le dépôt :
```bash
git clone https://github.com/KirushieldDev/StarTrip.git
```

### 3️⃣ Configuraton
1. Dupliquez le fichier ```configs/config.php.bkp``` et renommez le ```configs/config.php```
2. Modifiez le fichier ```configs/config.php``` en remplacant les valeurs par celles de votre base de données MySQL
```php
<?php
$host = "";             // L'hôte pour la connexion à la base de données
$db_name = "";          // Le nom de la base de données
$user = "";             // Le nom d'utilisateur utilisé pour s'authentifier à la base de données
$pass = "";             // Le mot de passe utilisé pour s'authentifier à la base de données

try {
    $cnx = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
} catch (PDOException $e) {
    echo $e;
}
?>
```
- Ouvrir le fichier ```scripts/create_tables.php``` pour créer les tables néccessaires automatiquement
- Insertion des données nécessaires :
  - Ouvrir le fichier ```scripts/import_ships.php``` pour insérer les vaisseaux
  - Ouvir le fichier ```scripts/import_planets.php``` pour insérer les planètes et les voyages
3. Dupliquez le fichier ```java/src/main/java/fr/uge/DatabaseConnection.backup``` et renommez le ```java/src/main/java/fr/uge/DatabaseConnection.java```
4. Modifiez le fichier ```java/src/main/java/fr/uge/DatabaseConnection.java``` en remplacant les valeurs par celles de votre base de données MySQL
```java
package fr.uge;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class fr.uge.DatabaseConnection {
    private static final String URL = "";       // L'url de la base de données
    private static final String USERNAME = "";  // Le nom d'utilisateur utilisé pour s'authentifier à la base de données
    private static final String PASSWORD = "";  // Le mot de passe utilisé pour s'authentifier à la base de données

    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USERNAME, PASSWORD);
    }
}
```
5. Ajoutez la librairie (*Libraries*) ```java/mysql-connector-j-9.1.0.jar``` dans ce projet
6. Compilez une fois le programme C
```bash
gcc -o a-etoile.exe C/a-etoile.c
```
7. Dans le fichier ```java/src/main/java/fr/uge/QueryData.java``` il faut remplacer le ```startrip_path``` par le chemin absolu du projet
8. Placez-vous dans le dossier ```java``` puis lancez la commande :
```bash
mvn clean package
```
Pour créer le graphe sans filtre :
```bash
java -jar java/target/java-1.0-SNAPSHOT.jar
```
Pour filtrer le graphe il faut utiliser la commande en passant les paramètres nécessaires par exemple:
```bash
java -jar java/target/java-1.0-SNAPSHOT.jar Empire 1
```
Vous pouvez trouver le graphe dans le fichier ```graph.txt```

### 4️⃣ Démarrage
- Ouvrir le fichier ```index.html``` dans votre navigateur
- 🎉 Vous êtes maintenant prêt à explorer StarTrip !
