
<?php
// Configuration et Entêtes
header("Content-Type: application/json");
// Désactiver l'affichage des erreurs en production pour des raisons de sécurité
ini_set('display_errors', 0);
error_reporting(E_ALL);

/**
 * Fonction utilitaire pour envoyer une réponse JSON et terminer le script.
 * @param array $data Les données à encoder en JSON.
 * @param int $statusCode Le code de statut HTTP à envoyer.
 */
function sendResponse(array $data, int $statusCode) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// 1. VÉRIFICATION DE LA MÉTHODE HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(["status" => "error", "message" => "Method not allowed. Only POST requests are accepted."], 405);
}

// 2. RÉCUPÉRATION DES IDENTIFIANTS RAILWAY
// Utilisez les noms exacts des variables d'environnement de Railway.
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
// Le port est facultatif mais bonne pratique
$port = getenv('MYSQLPORT') ?: 3306; 

// 3. TENTATIVE DE CONNEXION À LA BASE DE DONNÉES
$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    // Enregistre l'erreur dans les logs de Railway (pour le débogage)
    error_log("DB connection failed: " . $conn->connect_error);
    sendResponse(["status" => "error", "message" => "DB connection failed: Cannot connect to database."], 500);
}

// 4. LECTURE ET VÉRIFICATION DES DONNÉES JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    sendResponse(["status" => "error", "message" => "Invalid JSON payload received."], 400);
}

// Validation de la présence des champs principaux
$required_fields = ["anrede", "vorname", "nachname", "email"];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        sendResponse(["status" => "error", "message" => "Missing required field: " . $field], 400);
    }
}

// 5. INSERTION DANS LA TABLE PRINCIPALE (Messformular1)
// Remarque: 's' pour postleitzahl pour supporter les codes alphanumériques.
$stmt = $conn->prepare("
    INSERT INTO Messformular1 
    (anrede, vorname, nachname, firma, strasseUndHausnummer, postleitzahl, stadt, land, email, telefon, message)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// 11 paramètres de type chaîne de caractères ('s')
$stmt->bind_param(
    "sssssssssss", 
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
    error_log("SQL Error (Messformular1): " . $stmt->error);
    sendResponse(["status" => "error", "message" => "Failed to insert into Messformular1.", "details" => $stmt->error], 500);
}

$form_id = $stmt->insert_id;
$stmt->close();

// 6. INSERTION DANS LA TABLE SECONDAIRE (elements)
if (isset($data["elements"]) && is_array($data["elements"])) {
    $stmt2 = $conn->prepare("INSERT INTO elements (form_id, nom, estCoche) VALUES (?, ?, ?)");
    
    // Parcourt chaque élément et insère
    foreach ($data["elements"] as $el) {
        // Assurez-vous que les champs requis pour 'elements' existent
        if (isset($el["nom"])) {
            $nom = $el["nom"];
            // Convertit booléen PHP en 1 ou 0 pour MySQL (INT)
            $estCoche = (isset($el["estCoche"]) && $el["estCoche"]) ? 1 : 0; 
            
            // i (entier), s (chaîne), i (entier)
            $stmt2->bind_param("isi", $form_id, $nom, $estCoche); 
            
            if (!$stmt2->execute()) {
                // Optionnel : enregistrer l'erreur d'insertion des éléments
                error_log("SQL Error (elements): " . $stmt2->error);
            }
        }
    }
    $stmt2->close();
}

// 7. SUCCÈS
$conn->close();
sendResponse(["status" => "success", "message" => "Form saved successfully", "id" => $form_id], 200);
?>
