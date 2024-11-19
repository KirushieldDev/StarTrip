<?php
class DatabaseHandler {
private $pdo;

public function __construct($host, $db, $user, $pass, $charset = 'utf8mb4') {
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false,
];

$this->pdo = new PDO($dsn, $user, $pass, $options);
}

public function getConnection() {
return $this->pdo;
}

public function clearTables() {
// Vider la table trips
$this->pdo->exec("DELETE FROM trips");
// Vider la table Planets
$this->pdo->exec("DELETE FROM Planets");
}
}
