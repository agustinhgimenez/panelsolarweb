<?php
header('Content-Type: application/json');

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

echo json_encode([
    "timestamp"   => $ultima[0],
    "current"     => $ultima[1],
    "voltage"     => $ultima[2],
    "power"       => $ultima[3],
    "irradiance"  => $ultima[4]
]);
?>