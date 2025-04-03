<?php
$host = "134.199.211.202";  
$user = "ana";       
$password = "ana123";       
$database = "freshBox"; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexi�n a la base de datos."]));
}

$conn->set_charset("utf8mb4");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (!isset($_GET["ts_id"])) {
    echo json_encode(["success" => false, "message" => "ID del transportista requerido."]);
    exit;
}

$ts_id = intval($_GET["ts_id"]);

$query = "
SELECT 
    c.ct_id, 
    c.ct_cantidad, 
    c.ct_peso, 
    c.ct_status,  
    v.vt_id, 
    v.vt_fecha, 
    v.vt_total, 
    v.vt_status,
    v.vt_pesoTotal,
    cli.cl_direccion
FROM 
    freshboxcontenedores c
JOIN 
    freshboxventas v ON c.vt_id = v.vt_id
JOIN 
    freshboxusuarios u ON v.us_id = u.us_id -- Relaci�n con usuarios
JOIN 
    freshboxclientes cli ON u.us_id = cli.us_id -- Relaci�n con clientes
WHERE 
    c.ts_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ts_id);
$stmt->execute();
$result = $stmt->get_result();

$cargas = [];
while ($row = $result->fetch_assoc()) {
    $cargas[] = $row;
}

echo json_encode($cargas);
$stmt->close();
$conn->close();
?>
