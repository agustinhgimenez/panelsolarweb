<?php
// api/datos.php
// Endpoint para recibir datos del ESP32

$username_esperado = "panel_solar";
$password_esperado = "panelsolar123";

header('Content-Type: application/json');

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    http_response_code(401);
    echo json_encode(["error" => "Autenticación requerida"]);
    exit;
}

if ($_SERVER['PHP_AUTH_USER'] !== $username_esperado || $_SERVER['PHP_AUTH_PW'] !== $password_esperado) {
    http_response_code(401);
    echo json_encode(["error" => "Usuario o contraseña incorrectos"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido"]);
    exit;
}

$campos_requeridos = ['current', 'voltage', 'power', 'irradiance'];
foreach ($campos_requeridos as $campo) {
    if (!isset($data[$campo])) {
        http_response_code(400);
        echo json_encode(["error" => "Campo faltante: $campo"]);
        exit;
    }
}

$archivo_csv = __DIR__ . '/datos_panel.csv';
$timestamp = date('Y-m-d H:i:s');
$linea = "$timestamp," . $data['current'] . "," . $data['voltage'] . "," . $data['power'] . "," . $data['irradiance'] . "\n";

if (!file_exists($archivo_csv)) {
    $header = "Timestamp,Corriente (A),Voltaje (V),Potencia (W),Irradiancia (W/m2)\n";
    file_put_contents($archivo_csv, $header);
}

file_put_contents($archivo_csv, $linea, FILE_APPEND);

http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Datos guardados correctamente",
    "timestamp" => $timestamp,
    "data" => $data
]);
?>