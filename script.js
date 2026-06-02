function setText(id, value) {

    const el = document.getElementById(id);

    if (el) el.innerText = value;

}



function formatVoltage(value) {

    if (value === undefined || value === null || value === "") return "—";

    return parseFloat(value).toFixed(3) + " V";

}



const IRR_REF_WM2 = 1000;



function irradianciaAPorcentaje(wm2) {

    const pct = (parseFloat(wm2) / IRR_REF_WM2) * 100;

    return Math.min(100, Math.max(0, pct));

}



async function actualizarDatos() {

    try {

        const response = await fetch('/api/ultimo.php?t=' + Date.now());

        const data = await response.json();



        if (data.error) {

            console.error(data.error);

            return;

        }



        // Columna LDR

        const luzPct = data.ldr_light_pct ?? data.irradiance_ldr;

        setText("ldr_light_pct",

            luzPct != null ? parseFloat(luzPct).toFixed(0) + " %" : "—");

        setText("servo",

            data.servo_angle != null ? parseFloat(data.servo_angle).toFixed(0) + " °" : "—");

        setText("servo_v",

            data.servo_angle_v != null ? parseFloat(data.servo_angle_v).toFixed(0) + " °" : "—");



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



        // Columna panel

        setText("vpanel", parseFloat(data.voltage || 0).toFixed(3) + " V");

        setText("current", parseFloat(data.current || 0).toFixed(3) + " A");

        setText("power", parseFloat(data.power || 0).toFixed(4) + " W");

        setText("irradiance",

            data.irradiance != null

                ? irradianciaAPorcentaje(data.irradiance).toFixed(0) + " %"

                : "—");

        setText("panel_adc", data.panel_adc != null ? String(data.panel_adc) : "—");

        setText("v_adc", data.v_adc != null ? parseFloat(data.v_adc).toFixed(3) + " V" : "—");

        setText("acs_v", data.acs_v != null ? parseFloat(data.acs_v).toFixed(3) + " V" : "—");



        const alerta = document.getElementById("panel_alerta");

        const adc = parseInt(data.panel_adc, 10) || 0;

        const v = parseFloat(data.voltage) || 0;

        if (alerta) {

            if (adc < 20 && v < 0.1) {

                alerta.style.display = "block";

                alerta.textContent =

                    "Sin señal en GPIO36: revisar divisor de voltaje del panel.";

            } else {

                alerta.style.display = "none";

            }

        }

    } catch (error) {

        console.error("ERROR:", error);

    }

}



actualizarDatos();

setInterval(actualizarDatos, 1000);


