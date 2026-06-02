<?php
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

$data = json_decode(file_get_contents('php://input'), true);
if ($data === null) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido"]);
    exit;
}

foreach (['current', 'voltage', 'power', 'irradiance', 'ldr_raw', 'ldr_voltage'] as $campo) {
    if (!isset($data[$campo])) {
        http_response_code(400);
        echo json_encode(["error" => "Campo faltante: $campo"]);
        exit;
    }
}

$servo_angle    = $data['servo_angle'] ?? 90;
$servo_angle_v  = $data['servo_angle_v'] ?? 90;
$irradiance_ldr = $data['irradiance_ldr'] ?? 0;
$panel_adc      = $data['panel_adc'] ?? 0;
$v_adc          = $data['v_adc'] ?? 0;
$acs_adc        = $data['acs_adc'] ?? 0;
$acs_v          = $data['acs_v'] ?? 0;

$timestamp = date('Y-m-d H:i:s');
$archivo_csv = __DIR__ . '/datos_panel.csv';
$archivo_ultimo = __DIR__ . '/ultimo.json';

$linea = "$timestamp,{$data['current']},{$data['voltage']},{$data['power']},{$data['irradiance']}," .
    implode(',', $data['ldr_raw']) . "," . implode(',', $data['ldr_voltage']) . "," .
    "$servo_angle,$servo_angle_v,$irradiance_ldr,$panel_adc,$v_adc,$acs_v\n";

if (!file_exists($archivo_csv)) {
    file_put_contents($archivo_csv,
        "Timestamp,I,V,P,Irr panel,LDR1r,LDR2r,LDR3r,LDR4r,LDR1v,LDR2v,LDR3v,LDR4v,ServoH,ServoV,IrrLDR,ADC36,Vadc36,ACSv\n");
}
file_put_contents($archivo_csv, $linea, FILE_APPEND);

$ultimo = [
    "timestamp" => $timestamp,
    "current" => $data['current'],
    "voltage" => $data['voltage'],
    "power" => $data['power'],
    "irradiance" => $data['irradiance'],
    "irradiance_ldr" => $irradiance_ldr,
    "panel_adc" => $panel_adc,
    "v_adc" => $v_adc,
    "acs_adc" => $acs_adc,
    "acs_v" => $acs_v,
    "ldr_raw" => $data['ldr_raw'],
    "ldr_voltage" => $data['ldr_voltage'],
    "servo_angle" => $servo_angle,
    "servo_angle_v" => $servo_angle_v,
];
file_put_contents($archivo_ultimo, json_encode($ultimo, JSON_PRETTY_PRINT));

echo json_encode(["success" => true, "timestamp" => $timestamp, "data" => $ultimo]);
?>
