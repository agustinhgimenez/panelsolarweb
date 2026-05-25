# Optoelectronica — Monitor TP

Archivos creados en esta carpeta:
- `index.html` — página principal (muestra `data.json`).
- `style.css`, `script.js` — estilos y cliente JS.
- `save_data.php` — endpoint para recibir JSON desde el ESP32 y guardar en `data.json`.
- `data.json` — fichero con las lecturas actuales (escrito por `save_data.php`).
- `nginx-example.conf` — ejemplo de configuración de nginx para `optoelectronica.agustingimenez.ar`.
- `esp32_example.ino` — ejemplo de cómo enviar lecturas desde ESP32.

Pasos para activar el subdominio:
1. Crear un registro DNS `A` para `optoelectronica.agustingimenez.ar` apuntando a la IP del servidor.
2. Copiar `nginx-example.conf` a `/etc/nginx/sites-available/optoelectronica` y crear un enlace en `sites-enabled`:

```sh
sudo cp nginx-example.conf /etc/nginx/sites-available/optoelectronica
sudo ln -s /etc/nginx/sites-available/optoelectronica /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

3. Asegurarse de tener PHP-FPM instalado y que `fastcgi_pass` apunte al socket correcto (o usar `127.0.0.1:9000`).
4. Ajustar permisos de `/var/www/optoelectronica` para que el proceso web pueda escribir `data.json`:

```sh
sudo chown -R www-data:www-data /var/www/optoelectronica
sudo chmod 664 /var/www/optoelectronica/data.json
```

Cómo enviar datos desde el ESP32 (ejemplo):

El ESP32 puede enviar las lecturas mediante HTTP POST con `Content-Type: application/json` a `http://optoelectronica.agustingimenez.ar/save_data.php`.

Ejemplo muy simple (Arduino core para ESP32): usar la biblioteca `HTTPClient` y enviar un JSON con las claves esperadas (ej: `LDR_NW`, `Vpanel`, `I`, `servo_angle`, etc.). Vea `esp32_example.ino`.
