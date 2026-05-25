# Guía MicroPython para ESP32 — Envío de datos al servidor

Esta guía está pensada para el compañero que monta la parte física y programa el ESP32 usando MicroPython.

Contenido:
- Requisitos y flasheo MicroPython
- Conexión WiFi
- Lectura de sensores (ADS1115, ACS712, divisores)
- Envío de JSON al servidor (`/save_data.php`)
- Código de ejemplo (ver `esp32_micropython_main.py`)

## 1) Requisitos
- ESP32 con MicroPython (firmware reciente). Descargar desde https://micropython.org
- Herramientas: `esptool.py`, `ampy` o `rshell` para subir archivos.

Flashear MicroPython (ejemplo):

```sh
pip install esptool
esptool.py --chip esp32 erase_flash
esptool.py --chip esp32 --port /dev/ttyUSB0 write_flash -z 0x1000 esp32-*.bin
```

## 2) Copiar archivos al ESP32
Usar `ampy` o `mpremote`:

```sh
pip install adafruit-ampy
ampy --port /dev/ttyUSB0 put esp32_micropython_main.py main.py
```

## 3) Configurar WiFi
Editar `SSID` y `PASS` en `esp32_micropython_main.py`.

## 4) Sensores y pines
- LDRs: recomendado usar `ADS1115` por I2C (4 canales). En MicroPython necesitarás un driver ADS1115 y leer sus canales.
- ACS712: conectar salida a un ADC del ESP32 (ej. GPIO32) y medir Vout. Calibrar `Voffset` y `sensitivity`.
- Divisores de tensión: medir con multímetro y ajustar fórmulas de conversión en el código.

## 5) Envío de JSON
El script crea un JSON con las claves necesarias y lo POSTea a `https://optoelectronica.agustingimenez.ar/save_data.php`.
Si hay problemas con TLS en MicroPython, probar en entorno de desarrollo con `post_json` intentando sin SSL (comentado en ejemplo), pero para producción es obligatorio usar TLS o una red local segura.

## 6) Ejemplo rápido de payload

```json
{
  "timestamp":"2026-05-20T12:00:00Z",
  "project_date":"Mayo 2026",
  "project_status":"Versión de trabajo (prototipo)",
  "project_version":"v1",
  "LDR_NW":123,
  "LDR_NE":120,
  "LDR_SW":110,
  "LDR_SE":115,
  "H":5,
  "servo_angle":90,
  "Vpanel":4.8,
  "Vbat":3.9,
  "I":0.18,
  "P":0.864,
  "irradiance":150.0,
  "soc":78,
  "charging":false
}
```

---
Archivo incluido: `esp32_micropython_main.py` (subir como `main.py` en el ESP32).
