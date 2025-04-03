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

if (!isset($data->email) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit();
}

$email = trim($data->email);
$password = trim($data->password);

// Consulta para buscar al transportista en la tabla freshboxtransportistas
$sql = "SELECT ts_id as id, ts_nombre as nombre, ts_email as email, ts_password as password, us_id FROM freshboxtransportistas WHERE ts_email = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die(json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conn->error]));
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Si se encuentra el transportista, verificar la contraseña
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verificar si el transportista ya tiene una sesión activa
    $sessionCheckSql = "SELECT us_sesion FROM freshboxusuarios WHERE us_id = ?";
    $sessionStmt = $conn->prepare($sessionCheckSql);
    $sessionStmt->bind_param("i", $user["us_id"]);
    $sessionStmt->execute();
    $sessionResult = $sessionStmt->get_result();

    if ($sessionResult->num_rows > 0) {
        $session = $sessionResult->fetch_assoc();
        if ($session['us_sesion'] == 1) {
            echo json_encode(["success" => false, "message" => "Ya tienes una sesión activa en otro dispositivo"]);
            $sessionStmt->close();
            $stmt->close();
            $conn->close();
            exit();
        }
    }

    // Verificar la contraseña
    if ($password === $user["password"]) {
        // Si el login es exitoso, actualizar el campo us_sesion a 1 en freshboxusuarios
        $updateSql = "UPDATE freshboxusuarios SET us_sesion = 1 WHERE us_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt === false) {
            die(json_encode(["success" => false, "message" => "Error al preparar la actualización: " . $conn->error]));
        }
        $updateStmt->bind_param("i", $user["us_id"]);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode([
            "success" => true,
            "message" => "Inicio de sesión exitoso",
            "user" => [
                "id" => $user["id"],
                "nombre" => $user["nombre"],
                "email" => $user["email"],
                "tipo" => "transportista"
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    }
    $sessionStmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Transportista no encontrado"]);
}

$stmt->close();
$conn->close();
?>
