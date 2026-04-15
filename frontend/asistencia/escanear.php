<?php
// frontend/asistencia/escanear.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// GUARDIÁN: Solo Auxiliares (y SuperAdmin/Admin para pruebas) pueden estar aquí
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['SuperAdmin', 'Admin', 'Auxiliar', 'Director'])) {
    header("Location: ../../index.php");
    exit();
}

include '../../config/db.php';
include '../includes/header_admin.php'; 
?>

<div class="container mt-4 pb-5">
    
    <div class="text-center mb-5">
        <h2 class="fw-bold text-primary mb-1"><i class="bi bi-qr-code-scan me-2"></i>Control de Acceso (Puerta)</h2>
        <div class="d-inline-block bg-white px-4 py-2 rounded-pill shadow-sm border mt-2">
            <h3 id="reloj_digital" class="fw-bold text-dark font-monospace mb-0" style="letter-spacing: 2px;">--:--:--</h3>
            <small class="text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 700;"><?php echo date('d / m / Y'); ?></small>
        </div>
    </div>

    <div class="row justify-content-center g-4">
        
        <div class="col-md-5 col-lg-4">
            <div class="card shadow border-0 h-100 border-top border-primary border-4 rounded-4 overflow-hidden">
                <div class="card-body text-center p-4 d-flex flex-column justify-content-center bg-light bg-opacity-50">
                    
                    <div class="mb-4 mt-2">
                        <i class="bi bi-upc-scan text-primary opacity-75" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h5 class="fw-bold text-dark">Escanea el Carnet</h5>
                    <p class="text-muted small mb-3">Asegúrate de que el cursor esté parpadeando en la caja de abajo.</p>
                    
                    <input type="text" id="codigo_barra" class="form-control form-control-lg text-center fw-bold text-primary shadow-sm border-primary" 
                           placeholder="Haz clic aquí y escanea..." autofocus autocomplete="off" style="letter-spacing: 2px; font-size: 1.2rem;">
                    
                    <small class="text-muted mt-3 d-block"><i class="bi bi-keyboard me-1"></i> El sistema leerá automáticamente.</small>
                </div>
            </div>
        </div>

        <div class="col-md-5 col-lg-4">
            <div class="card shadow border-0 h-100 border-top border-success border-4 rounded-4" id="tarjetaResultado">
                <div class="card-body text-center p-4 d-flex flex-column justify-content-center align-items-center">
                    
                    <div class="position-relative mb-3">
                        <img id="resFoto" src="../../assets/fotos_alumnos/default.jpg" class="rounded-circle border border-3 shadow-sm" style="width: 130px; height: 130px; object-fit: cover;">
                        <span id="resIconoFlotante" class="position-absolute bottom-0 start-100 translate-middle p-2 bg-secondary border border-light rounded-circle text-white d-none">
                            <i class="bi bi-hourglass"></i>
                        </span>
                    </div>

                    <h4 id="resNombres" class="fw-bold text-dark mb-0">Esperando lectura...</h4>
                    <p id="resSalon" class="text-muted small mb-3">-</p>
                    
                    <div id="resMensaje" class="w-100 py-3 rounded-3 bg-light text-secondary fw-bold border">
                        <i class="bi bi-three-dots me-1"></i> Listo para escanear
                    </div>
                    
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // 1. RELOJ EN TIEMPO REAL 
    function actualizarReloj() {
        const ahora = new Date();
        let horas = ahora.getHours();
        let minutos = ahora.getMinutes();
        let segundos = ahora.getSeconds();
        let ampm = horas >= 12 ? 'P. M.' : 'A. M.';
        
        horas = horas % 12;
        horas = horas ? horas : 12; 
        
        horas = horas < 10 ? '0' + horas : horas;
        minutos = minutos < 10 ? '0' + minutos : minutos;
        segundos = segundos < 10 ? '0' + segundos : segundos;
        
        let textoHora = horas + ':' + minutos + ':' + segundos + ' ' + ampm;
        document.getElementById('reloj_digital').innerText = textoHora;
    }

    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    // 2. MANTENER EL FOCO EN EL INPUT 
    document.addEventListener('click', function() {
        document.getElementById('codigo_barra').focus();
    });

    // 3. DETECTAR EL ENTER DEL ESCÁNER
    document.getElementById('codigo_barra').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); 
            let codigo = this.value.trim();
            if (codigo !== "") {
                procesarEscaneo(codigo);
            }
            this.value = '';
        }
    });

    // 4. FUNCIÓN PARA ENVIAR AL BACKEND
    function procesarEscaneo(codigo) {
        document.getElementById('resNombres').innerText = "Buscando...";
        document.getElementById('resSalon').innerText = "-";
        document.getElementById('resMensaje').className = "w-100 py-3 rounded-3 bg-light text-secondary fw-bold border";
        document.getElementById('resMensaje').innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Procesando...';
        document.getElementById('resIconoFlotante').classList.add('d-none');

        let formData = new FormData();
        formData.append('codigo_barra', codigo);

        // Apuntando directamente a tu archivo funcional
        fetch('../../backend/marcar_asistencia.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('resNombres').innerText = data.alumno.nombres + ' ' + data.alumno.apellidos;
                document.getElementById('resSalon').innerText = "Salón: " + data.alumno.salon;
                
                let foto_url = data.alumno.foto ? '../../assets/fotos_alumnos/' + data.alumno.foto : '../../assets/fotos_alumnos/default.jpg';
                document.getElementById('resFoto').src = foto_url;

                let bgColor, textColor, iconClass;
                if (data.asistencia.estado === 'PUNTUAL') {
                    bgColor = 'bg-success bg-opacity-10 border-success'; textColor = 'text-success'; iconClass = 'bi-check-circle-fill text-success';
                } else if (data.asistencia.estado === 'TARDE') {
                    bgColor = 'bg-warning bg-opacity-10 border-warning'; textColor = 'text-dark'; iconClass = 'bi-exclamation-triangle-fill text-warning';
                } else if (data.asistencia.estado === 'SALIDA') {
                    bgColor = 'bg-info bg-opacity-10 border-info'; textColor = 'text-primary'; iconClass = 'bi-box-arrow-right text-info';
                } else {
                    bgColor = 'bg-danger bg-opacity-10 border-danger'; textColor = 'text-danger'; iconClass = 'bi-x-circle-fill text-danger';
                }

                document.getElementById('resMensaje').className = `w-100 py-3 rounded-3 fw-bold border ${bgColor} ${textColor}`;
                document.getElementById('resMensaje').innerHTML = `<i class="bi ${iconClass} me-2"></i>` + data.mensaje;

                let iconoFlotante = document.getElementById('resIconoFlotante');
                iconoFlotante.className = `position-absolute bottom-0 start-100 translate-middle p-2 border border-light rounded-circle d-flex justify-content-center align-items-center bg-white shadow`;
                iconoFlotante.innerHTML = `<i class="bi ${iconClass} fs-5"></i>`;
                iconoFlotante.classList.remove('d-none');

            } else {
                document.getElementById('resFoto').src = '../../assets/fotos_alumnos/default.jpg';
                document.getElementById('resNombres').innerText = "Estudiante no encontrado";
                document.getElementById('resSalon').innerText = "Código: " + codigo;
                document.getElementById('resMensaje').className = "w-100 py-3 rounded-3 bg-danger bg-opacity-10 text-danger fw-bold border border-danger";
                document.getElementById('resMensaje').innerHTML = '<i class="bi bi-x-octagon-fill me-2"></i>' + data.mensaje;
                document.getElementById('resIconoFlotante').classList.add('d-none');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('resMensaje').innerText = "Error de conexión con el servidor.";
        });
    }
</script>
</body>
</html>