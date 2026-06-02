<?php
header('Content-Type: application/json');

$candidatos = [
    __DIR__ . '/ultimo.json',
    dirname(__DIR__) . '/ultimo.json',
];

foreach ($candidatos as $archivo) {
    if (!file_exists($archivo)) {
        continue;
    }
    $data = json_decode(file_get_contents($archivo), true);
    if ($data !== null && is_array($data)) {
        $data['servo_angle'] = $data['servo_angle'] ?? 90;
        $data['servo_angle_v'] = $data['servo_angle_v'] ?? 90;
        $data['irradiance_ldr'] = $data['irradiance_ldr'] ?? 0;
        $data['panel_adc'] = $data['panel_adc'] ?? 0;
        $data['v_adc'] = $data['v_adc'] ?? 0;
        $data['acs_v'] = $data['acs_v'] ?? 0;
        echo json_encode($data);
        exit;
    }
}

$csv = __DIR__ . '/datos_panel.csv';
if (!file_exists($csv)) {
    $csv = dirname(__DIR__) . '/datos_panel.csv';
}

if (file_exists($csv)) {
    $lineas = file($csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lineas) > 1) {
        $ultima = str_getcsv(trim(end($lineas)));
        if (count($ultima) >= 13) {
            echo json_encode([
                'timestamp' => $ultima[0],
                'current' => $ultima[1],
                'voltage' => $ultima[2],
                'power' => $ultima[3],
                'irradiance' => $ultima[4],
                'ldr_raw' => array_slice($ultima, 5, 4),
                'ldr_voltage' => array_slice($ultima, 9, 4),
                'servo_angle' => $ultima[13] ?? 90,
                'servo_angle_v' => $ultima[14] ?? 90,
                'irradiance_ldr' => $ultima[15] ?? 0,
                'panel_adc' => $ultima[16] ?? 0,
                'v_adc' => $ultima[17] ?? 0,
                'acs_v' => $ultima[18] ?? 0,
            ]);
            exit;
        }
    }
}

echo json_encode(['error' => 'Sin datos', 'hint' => 'ultimo.json no existe en api/. Hacer git pull y revisar permisos de escritura.']);
?>
