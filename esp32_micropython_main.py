"""
MicroPython example for ESP32 — enviar lecturas al servidor HTTPS

Este script es un ejemplo mínimo. Ajustar pines, divisores y calibraciones.
"""
import network
import usocket as socket
import ussl as ssl
import ujson as json
import utime as time
from machine import ADC, Pin, I2C

# --- CONFIG ---
SSID = 'TU_SSID'
PASS = 'TU_PASS'
HOST = 'optoelectronica.agustingimenez.ar'
PORT = 443
PATH = '/save_data.php'

# Pines (ejemplo)
adc_vpanel = ADC(Pin(34))
adc_vpanel.atten(ADC.ATTN_11DB)
adc_vpanel.width(ADC.WIDTH_12BIT)

adc_vbat = ADC(Pin(35))
adc_vbat.atten(ADC.ATTN_11DB)
adc_vbat.width(ADC.WIDTH_12BIT)

adc_acs = ADC(Pin(32))
adc_acs.atten(ADC.ATTN_11DB)
adc_acs.width(ADC.WIDTH_12BIT)

# Si usas ADS1115 por I2C, implementar lectura aquí (no incluido).

def connect_wifi(ssid, pwd, timeout=15):
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    if not wlan.isconnected():
        wlan.connect(ssid, pwd)
        t0 = time.time()
        while not wlan.isconnected():
            if time.time() - t0 > timeout:
                return False
            time.sleep(0.5)
    return True

def read_voltage(adc):
    # devuelve voltaje en V (aprox) para referencia de 3.3V
    raw = adc.read()
    return (raw / 4095.0) * 3.3

def read_sensors():
    # Lecturas ejemplo (devuelven valores crudos/convertidos)
    nw = 0; ne = 0; sw = 0; se = 0
    # Si tenés ADS1115, lee los 4 canales y asigna a nw/ne/sw/se

    vpanel_adc = read_voltage(adc_vpanel)
    vbat_adc = read_voltage(adc_vbat)
    vacs = read_voltage(adc_acs)

    # Aplicar divisores reales
    Vpanel = vpanel_adc * (127.0/27.0)  # ejemplo
    Vbat = vbat_adc * 2.0

    Voffset = 2.5  # medir sin carga
    sensitivity = 0.066  # V/A (ajustar según ACS712)
    I = (vacs - Voffset) / sensitivity

    H = (ne + se) - (nw + sw)
    P = Vpanel * I

    return {
        'timestamp': time.localtime(),
        'LDR_NW': nw,
        'LDR_NE': ne,
        'LDR_SW': sw,
        'LDR_SE': se,
        'H': H,
        'servo_angle': 90,
        'Vpanel': round(Vpanel,3),
        'Vbat': round(Vbat,3),
        'I': round(I,3),
        'P': round(P,3),
        'irradiance': 0.0,
        'soc': 50,
        'charging': False,
        'project_date': 'Mayo 2026',
        'project_status': 'Versión de trabajo (prototipo)',
        'project_version': 'v1'
    }

def iso_timestamp(tm):
    # tm = time.localtime()
    return '{}-{:02d}-{:02d}T{:02d}:{:02d}:{:02d}Z'.format(tm[0],tm[1],tm[2],tm[3],tm[4],tm[5])

def post_json(host, port, path, data):
    body = json.dumps(data)
    addr = socket.getaddrinfo(host, port)[0][-1]
    s = socket.socket()
    s.connect(addr)
    try:
        s = ssl.wrap_socket(s)
    except Exception as e:
        # si no hay SSL, se intenta enviar sin cifrado (no recomendado)
        print('SSL wrap failed:', e)
    req = 'POST {} HTTP/1.1\r\nHost: {}\r\nContent-Type: application/json\r\nContent-Length: {}\r\nConnection: close\r\n\r\n{}'.format(path, host, len(body), body)
    s.write(req)
    # leer respuesta (opcional)
    resp = b''
    try:
        while True:
            part = s.read(512)
            if not part:
                break
            resp += part
    except Exception:
        pass
    s.close()
    return resp

def main_loop():
    ok = connect_wifi(SSID, PASS)
    if not ok:
        print('WiFi failed')
        return
    print('WiFi OK', network.WLAN(network.STA_IF).ifconfig())

    while True:
        sample = read_sensors()
        sample['timestamp'] = iso_timestamp(time.localtime())
        print('Enviar:', sample)
        resp = post_json(HOST, PORT, PATH, sample)
        print('Resp len', len(resp))
        time.sleep(2)

if __name__ == '__main__':
    main_loop()
