<?php
$host = "localhost";  
$user = "root";       
$password = "";       
$database = "freshbox";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]));
}

$conn->set_charset("utf8mb4");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email)) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit();
}

$email = trim($data->email);

// Consulta para buscar el us_id del transportista
$sql = "SELECT us_id FROM freshboxusuarios WHERE us_email = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conn->error]));
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Actualizar el campo us_sesion a 0 en freshboxusuarios para cerrar sesión
    $updateSql = "UPDATE freshboxusuarios SET us_sesion = 0 WHERE us_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    if ($updateStmt === false) {
        die(json_encode(["success" => false, "message" => "Error al preparar la actualización: " . $conn->error]));
    }
    $updateStmt->bind_param("i", $user["us_id"]);
    $updateStmt->execute();
    $updateStmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Cierre de sesión exitoso"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
}

$stmt->close();
$conn->close();
?>
