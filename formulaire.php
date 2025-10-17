
<?php
header("Content-Type: application/json");
// Affichage des erreurs désactivé en production pour des raisons de sécurité.
// Si vous déboguez, vous pouvez le réactiver temporairement.
error_reporting(E_ALL);
ini_set('display_errors', 0); // Important : Mettre à 0 en production

// --------------------------------------------------------
// Récupère les variables d'environnement EXACTES de Railway
// --------------------------------------------------------
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
// Le port est souvent implicite, mais si vous en avez besoin, utilisez getenv('MYSQLPORT')

// Connexion MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    // Éviter de renvoyer l'erreur détaillée en production (pour la sécurité)
    $error_message = "DB connection failed: Cannot connect to database."; 
    // Si vous voulez le logguer pour vous-même, utilisez error_log($conn->connect_error);

    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $error_message]);
    exit;
}

// Vérifier la méthode HTTP pour s'assurer que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(["status" => "error", "message" => "Method not allowed. Only POST accepted."]);
    exit;
}


// Lire les données JSON reçues
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON payload."]);
    exit;
}

// --------------------------------------------------------
// Validation et Préparation des données
// --------------------------------------------------------

// Validation simple des champs requis pour éviter des erreurs SQL non explicites
$required_fields = ["anrede", "vorname", "nachname", "email", "postleitzahl"];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing required field: " . $field]);
        exit;
    }
}

// Conversion pour bind_param
// POSTLEITZAHL est traité comme une chaîne (s) pour supporter les codes alphanumériques
$anrede = $data["anrede"] ?? null;
$vorname = $data["vorname"] ?? null;
$nachname = $data["nachname"] ?? null;
$firma = $data["firma"] ?? null;
$strasseUndHausnummer = $data["strasseUndHausnummer"] ?? null;
$postleitzahl = $data["postleitzahl"] ?? null;
$stadt = $data["stadt"] ?? null;
$land = $data["land"] ?? null;
$email = $data["email"] ?? null;
$telefon = $data["telefon"] ?? null;
$message = $data["message"] ?? null;


// Insertion dans la table principale
// Le format de bind_param est maintenant 'sssssssssss' (11 chaînes)
$stmt = $conn->prepare("
    INSERT INTO Messformular1 
    (anrede, vorname, nachname, firma, strasseUndHausnummer, postleitzahl, stadt, land, email, telefon, message)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssssssss",
    $anrede,
    $vorname,
    $nachname,
    $firma,
    $strasseUndHausnummer,
    $postleitzahl, // Traité comme une chaîne (s)
    $stadt,
    $land,
    $email,
    $telefon,
    $message
);

if (!$stmt->execute()) {
    http_response_code(500);
    // Afficher l'erreur SQL uniquement en mode débogage, sinon une erreur générique
    error_log("SQL Error: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Could not save main form data."]);
    exit;
}

$form_id = $stmt->insert_id;
$stmt->close();

// Enregistrer les éléments cochés (si présents)
if (isset($data["elements"]) && is_array($data["elements"])) {
    $stmt2 = $conn->prepare("INSERT INTO elements (form_id, nom, estCoche) VALUES (?, ?, ?)");
    foreach ($data["elements"] as $el) {
        $nom = $el["nom"] ?? null;
        // Assurez-vous que estCoche est un booléen avant la conversion
        $estCoche = (isset($el["estCoche"]) && $el["estCoche"]) ? 1 : 0; 
        
        $stmt2->bind_param("isi", $form_id, $nom, $estCoche);
        
        if (!$stmt2->execute()) {
             // Logguer l'erreur sans la renvoyer au client
             error_log("SQL Error (elements table): " . $stmt2->error);
        }
    }
    $stmt2->close();
}

$conn->close();

http_response_code(200);
echo json_encode(["status" => "success", "message" => "Form saved successfully", "form_id" => $form_id]);
?>
