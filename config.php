<?php
$host = "134.199.211.202";  
$user = "ana";       
$password = "ana123";       
$database = "freshBox"; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]));
}

$conn->set_charset("utf8mb4"); // Evita problemas con acentos y caracteres especiales
?>
