<?php
// Simple receiver: guardar JSON POST en data.json
// Permitir CORS para pruebas desde dispositivos locales
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$raw = file_get_contents('php://input');
if(!$raw){
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'empty body']);
  exit;
}
$data = json_decode($raw, true);
if($data === null){
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'invalid json']);
  exit;
}
$fn = __DIR__ . '/data.json';
if(file_put_contents($fn, json_encode($data, JSON_PRETTY_PRINT)) === false){
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'write failed']);
  exit;
}
echo json_encode(['ok'=>true]);
