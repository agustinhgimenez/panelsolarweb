async function actualizarDatos() {

    try {

        // evita cache
        const response = await fetch('/api/ultimo.php?t=' + Date.now());

        const data = await response.json();

        console.log("DATOS:", data);

        // si hay error salir
        if (data.error) {
            console.error(data.error);
            return;
        }

        // actualizar frontend
        document.getElementById("current").innerText =
            parseFloat(data.current).toFixed(3) + " A";

        document.getElementById("vpanel").innerText =
            parseFloat(data.voltage).toFixed(3) + " V";

        document.getElementById("power").innerText =
            parseFloat(data.power).toFixed(4) + " W";

        document.getElementById("irradiance").innerText =
            parseFloat(data.irradiance).toFixed(1) + " W/m²";

    } catch (error) {

        console.error("ERROR:", error);

    }
}

// primera actualización
actualizarDatos();

// actualizar cada 2 segundos
setInterval(actualizarDatos, 2000);