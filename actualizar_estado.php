<?php
$host = "localhost";  
$user = "root";       
$password = "";       
$database = "freshbox";

// Conexi�n a la base de datos
$conn = new mysqli($host, $user, $password, $database);

// Verificar si la conexi�n fue exitosa
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexi�n a la base de datos."]));
}

$conn->set_charset("utf8mb4");

// Establecer cabeceras HTTP para respuesta en formato JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Obtener los datos enviados en el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"), true);

// Verificar si los datos necesarios est�n presentes
if (!isset($data["vt_id"]) || !isset($data["estado"])) {
    echo json_encode(["success" => false, "message" => "Datos incompletos."]);
    exit;
}

$vt_id = intval($data["vt_id"]);  // Obtener el ID de la venta
$estado = in_array($data["estado"], ["pendiente", "aceptado", "entregado"]) ? $data["estado"] : "pendiente";  // Validar estado

// Query para actualizar el estado de la venta en la tabla freshboxventas
$query = "UPDATE freshboxventas SET vt_status = ? WHERE vt_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $estado, $vt_id);  // Vincular par�metros (estado: string, vt_id: int)

// Ejecutar la consulta y verificar si se actualiz� correctamente
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Estado actualizado."]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar estado."]);
}

// Cerrar la declaraci�n y la conexi�n
$stmt->close();
$conn->close();
?>
