<?php
header('Content-Type: application/json');

$archivo_ultimo = __DIR__ . '/ultimo.json';

if (file_exists($archivo_ultimo)) {
    $data = json_decode(file_get_contents($archivo_ultimo), true);
    if ($data !== null) {
        echo json_encode($data);
        exit;
    }
}

$archivo = __DIR__ . '/datos_panel.csv';

if (!file_exists($archivo)) {
    echo json_encode(["error" => "Archivo no encontrado"]);
    exit;
}

$lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (count($lineas) <= 1) {
    echo json_encode(["error" => "No hay datos"]);
    exit;
}

$ultima = explode(",", trim(end($lineas)));

$response = [
    "timestamp"  => $ultima[0],
    "current"    => $ultima[1],
    "voltage"    => $ultima[2],
    "power"      => $ultima[3],
    "irradiance" => $ultima[4],
];

if (count($ultima) >= 13) {
    $response["ldr_raw"] = [
        $ultima[5], $ultima[6], $ultima[7], $ultima[8]
    ];
    $response["ldr_voltage"] = [
        $ultima[9], $ultima[10], $ultima[11], $ultima[12]
    ];
}

echo json_encode($response);
?>
