function setText(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.innerText = value;
    }
}

function formatVoltage(value) {
    if (value === undefined || value === null || value === "") {
        return "—";
    }
    return parseFloat(value).toFixed(3) + " V";
}

async function actualizarDatos() {
    try {
        const response = await fetch('/api/ultimo.php?t=' + Date.now());
        const data = await response.json();

        console.log("DATOS:", data);

        if (data.error) {
            console.error(data.error);
            return;
        }

        setText("current", parseFloat(data.current).toFixed(3) + " A");
        setText("vpanel", parseFloat(data.voltage).toFixed(3) + " V");
        setText("power", parseFloat(data.power).toFixed(4) + " W");
        setText("irradiance", parseFloat(data.irradiance).toFixed(1) + " W/m²");

        setText("servo",
            data.servo_angle != null && data.servo_angle !== ""
                ? parseFloat(data.servo_angle).toFixed(0) + " °"
                : "—");

        setText("servo_v",
            data.servo_angle_v != null && data.servo_angle_v !== ""
                ? parseFloat(data.servo_angle_v).toFixed(0) + " °"
                : "—");

        setText("error_h",
            data.error_h != null && data.error_h !== ""
                ? parseFloat(data.error_h).toFixed(3) + " V"
                : "—");

        setText("error_v",
            data.error_v != null && data.error_v !== ""
                ? parseFloat(data.error_v).toFixed(3) + " V"
                : "—");

        if (Array.isArray(data.ldr_voltage) && data.ldr_voltage.length >= 4) {
            setText("ldr_nw", formatVoltage(data.ldr_voltage[0]));
            setText("ldr_ne", formatVoltage(data.ldr_voltage[1]));
            setText("ldr_sw", formatVoltage(data.ldr_voltage[2]));
            setText("ldr_se", formatVoltage(data.ldr_voltage[3]));
        } else if (Array.isArray(data.ldr_raw) && data.ldr_raw.length >= 4) {
            const vref = 3.3;
            const adcMax = 4095;
            setText("ldr_nw", formatVoltage((data.ldr_raw[0] / adcMax) * vref));
            setText("ldr_ne", formatVoltage((data.ldr_raw[1] / adcMax) * vref));
            setText("ldr_sw", formatVoltage((data.ldr_raw[2] / adcMax) * vref));
            setText("ldr_se", formatVoltage((data.ldr_raw[3] / adcMax) * vref));
        }

    } catch (error) {
        console.error("ERROR:", error);
    }
}

actualizarDatos();
setInterval(actualizarDatos, 2000);
