<?php
$host = 'db';
$user = 'user';
$pass = 'password';
$db   = 'messformular';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    echo "Erreur de connexion : " . $mysqli->connect_error . PHP_EOL;
    exit(1);
} else {
    echo "Connexion réussie ✅" . PHP_EOL;
}

// Exemple : créer une table test
$mysqli->query("CREATE TABLE IF NOT EXISTS test_table (id INT PRIMARY KEY AUTO_INCREMENT, message VARCHAR(255))");
$mysqli->query("INSERT INTO test_table (message) VALUES ('Hello depuis Docker')");
$result = $mysqli->query("SELECT * FROM test_table");
while ($row = $result->fetch_assoc()) {
    echo "Row: " . $row['id'] . " - " . $row['message'] . PHP_EOL;
}
