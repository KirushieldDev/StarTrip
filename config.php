<?php
// Paramètres de connexion
$host = '127.0.0.1';  // Adresse du serveur de base de données
$db   = 'travia';  // Nom de la base de données
$user = 'root';  // Nom d'utilisateur de la base de données
$pass = '';  // Mot de passe de la base de données
$charset = 'utf8mb4';  // Jeu de caractères

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Mode de rapport d'erreurs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Mode de récupération par défaut
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Utiliser les préparations réelles
];

try {
    // Création de la connexion PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
   // echo "Connexion réussie!";

} catch (\PDOException $e) {
    // Gestion des erreurs de connexion
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
