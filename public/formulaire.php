<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Récupère les variables d'environnement (Railway les définit automatiquement)
$host = getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER');
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD');
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE');

// Connexion MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB connection failed: " . $conn->connect_error]);
    exit;
}

// Lire les données JSON reçues
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

// Insertion dans la table principale
$stmt = $conn->prepare("
    INSERT INTO Messformular1 
    (anrede, vorname, nachname, firma, strasseUndHausnummer, postleitzahl, stadt, land, email, telefon, message)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssissssss",
    $data["anrede"],
    $data["vorname"],
    $data["nachname"],
    $data["firma"],
    $data["strasseUndHausnummer"],
    $data["postleitzahl"],
    $data["stadt"],
    $data["land"],
    $data["email"],
    $data["telefon"],
    $data["message"]
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $stmt->error]);
    exit;
}

$form_id = $stmt->insert_id;
$stmt->close();

// Enregistrer les éléments cochés (si présents)
if (isset($data["elements"]) && is_array($data["elements"])) {
    $stmt2 = $conn->prepare("INSERT INTO elements (form_id, nom, estCoche) VALUES (?, ?, ?)");
    foreach ($data["elements"] as $el) {
        $nom = $el["nom"];
        $estCoche = $el["estCoche"] ? 1 : 0;
        $stmt2->bind_param("isi", $form_id, $nom, $estCoche);
        $stmt2->execute();
    }
    $stmt2->close();
}

$conn->close();

echo json_encode(["status" => "success", "message" => "Form saved successfully"]);
?>
