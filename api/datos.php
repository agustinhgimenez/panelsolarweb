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

$campos_requeridos = ['current', 'voltage', 'power', 'irradiance', 'ldr_raw', 'ldr_voltage'];
foreach ($campos_requeridos as $campo) {
    if (!isset($data[$campo])) {
        http_response_code(400);
        echo json_encode(["error" => "Campo faltante: $campo"]);
        exit;
    }
}

if (!is_array($data['ldr_raw']) || count($data['ldr_raw']) !== 4) {
    http_response_code(400);
    echo json_encode(["error" => "ldr_raw debe ser un array de 4 valores"]);
    exit;
}

if (!is_array($data['ldr_voltage']) || count($data['ldr_voltage']) !== 4) {
    http_response_code(400);
    echo json_encode(["error" => "ldr_voltage debe ser un array de 4 valores"]);
    exit;
}

$servo_angle   = isset($data['servo_angle']) ? $data['servo_angle'] : 90;
$servo_angle_v = isset($data['servo_angle_v']) ? $data['servo_angle_v'] : 90;
$lux               = isset($data['lux']) ? $data['lux'] : 0;
$irradiance_lux    = isset($data['irradiance_lux']) ? $data['irradiance_lux'] : 0;
$irradiance_ldr    = isset($data['irradiance_ldr']) ? $data['irradiance_ldr'] : 0;

$archivo_csv = __DIR__ . '/datos_panel.csv';
$archivo_ultimo = __DIR__ . '/ultimo.json';
$timestamp = date('Y-m-d H:i:s');

$linea = $timestamp . "," .
    $data['current'] . "," .
    $data['voltage'] . "," .
    $data['power'] . "," .
    $data['irradiance'] . "," .
    implode(',', $data['ldr_raw']) . "," .
    implode(',', $data['ldr_voltage']) . "," .
    $servo_angle . "," .
    $servo_angle_v . "," .
    $lux . "," .
    $irradiance_lux . "," .
    $irradiance_ldr . "\n";

if (!file_exists($archivo_csv)) {
    $header = "Timestamp,Corriente (A),Voltaje (V),Potencia (W),Irradiancia panel (W/m2)," .
              "LDR1 raw,LDR2 raw,LDR3 raw,LDR4 raw," .
              "LDR1 V,LDR2 V,LDR3 V,LDR4 V," .
              "Servo H (deg),Servo V (deg),Lux (lx),Irradiancia lux (W/m2),Irradiancia LDR (W/m2)\n";
    file_put_contents($archivo_csv, $header);
}

file_put_contents($archivo_csv, $linea, FILE_APPEND);

$ultimo = [
    "timestamp"     => $timestamp,
    "current"       => $data['current'],
    "voltage"       => $data['voltage'],
    "power"         => $data['power'],
    "irradiance"    => $data['irradiance'],
    "ldr_raw"       => $data['ldr_raw'],
    "ldr_voltage"   => $data['ldr_voltage'],
    "servo_angle"   => $servo_angle,
    "servo_angle_v" => $servo_angle_v,
    "lux"           => $lux,
    "irradiance_lux" => $irradiance_lux,
    "irradiance_ldr" => $irradiance_ldr,
];
file_put_contents($archivo_ultimo, json_encode($ultimo, JSON_PRETTY_PRINT));

http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Datos guardados correctamente",
    "timestamp" => $timestamp,
    "data" => $ultimo
]);
?>
