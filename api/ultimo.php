<?php
header('Content-Type: application/json');
$archivo_ultimo = __DIR__ . '/ultimo.json';

if (file_exists($archivo_ultimo)) {
    $data = json_decode(file_get_contents($archivo_ultimo), true);
    if ($data !== null) {
        $data += [
            'servo_angle' => $data['servo_angle'] ?? 90,
            'servo_angle_v' => $data['servo_angle_v'] ?? 90,
            'irradiance_ldr' => $data['irradiance_ldr'] ?? 0,
            'panel_adc' => $data['panel_adc'] ?? 0,
            'v_adc' => $data['v_adc'] ?? 0,
            'acs_v' => $data['acs_v'] ?? 0,
        ];
        echo json_encode($data);
        exit;
    }
}

echo json_encode(["error" => "Sin datos"]);
?>
