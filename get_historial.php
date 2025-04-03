<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");

$host = "localhost";  
$user = "root";       
$password = "";       
$database = "freshbox";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexi�n fallida: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");

$ts_id = isset($_GET['ts_id']) ? intval($_GET['ts_id']) : 0;

if ($ts_id === 0) {
    die(json_encode(["error" => "ID de transportista no v�lido"]));
}

$sql = "
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
    freshboxusuarios u ON v.us_id = u.us_id 
JOIN 
    freshboxclientes cli ON u.us_id = cli.us_id 
WHERE 
    c.ts_id = ? AND v.vt_status = 'entregado'
ORDER BY v.vt_fecha DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ts_id);
$stmt->execute();
$result = $stmt->get_result();

$entregas = [];
while ($row = $result->fetch_assoc()) {
    $entregas[] = $row;
}

echo json_encode($entregas ?: []);

$stmt->close();
$conn->close();
?>
