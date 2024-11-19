<?php
// Exemple de code pour la recherche des planètes

$host = '127.0.0.1';
$db = 'travia';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
$query = $_GET['query'] ?? '';

$stmt = $pdo->prepare("SELECT Id, Name FROM Planets WHERE Name LIKE :query LIMIT 10");
$stmt->execute(['query' => "%$query%"]);
$planets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($planets);
?>
