<?php 
// 1. EL CANDADO DE SEGURIDAD (Obligatorio para que nadie entre por URL)
include_once '../../config/auth.php'; 

// 2. TUS INCLUDES ORIGINALES
include '../../config/db.php';
include '../includes/header_admin.php';
date_default_timezone_set('America/Lima');
?>

<nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1" style="font-size: 1rem;">
            <i class="bi bi-person-circle me-2"></i>
            Usuario: <?php echo $_SESSION['nombre_usuario']; ?> (<?php echo $_SESSION['rol']; ?>)
        </span>
        <a href="../../backend/logout.php" class="btn btn-outline-light btn-sm fw-bold" onclick="return confirm('¿Desea cerrar la sesión de asistencia?')">
            <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
        </a>
    </div>
</nav>

<div class="container pb-5">

    <div class="text-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-qr-code-scan me-2"></i>Control de Asistencia</h2>
        <h4 id="reloj" class="text-muted font-monospace display-6">--:--:--</h4>
        <p class="text-muted"><?php echo date("d/m/Y"); ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-5 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <div class="mb-4">
                        <i class="bi bi-upc-scan display-1 text-secondary opacity-50"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Escanea el Carnet Aquí</h5>
                    <input type="text" id="codigo_alumno" class="form-control form-control-lg text-center fw-bold" placeholder="Haz clic y escanea..." autocomplete="off" autofocus>
                    <div class="mt-3 text-muted small"><i class="bi bi-info-circle me-1"></i> El cursor debe estar en la caja.</div>
                    
                    <div id="loading" class="mt-3 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="small text-muted mt-2">Procesando...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            <div class="card shadow border-0 h-100" id="tarjetaResultado">
                <div class="card-body text-center p-5">
                    <img id="foto_alumno" src="../../assets/fotos_alumnos/default.jpg" class="rounded-circle border border-5 border-white shadow mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <h3 id="nombre_alumno" class="fw-bold text-dark text-truncate">Esperando...</h3>
                    <h5 id="grado_alumno" class="text-muted mb-4">-</h5>
                    <div id="mensaje_estado" class="alert alert-secondary fw-bold">
                        <i class="bi bi-hourglass-split me-2"></i> Listo para escanear
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. RELOJ 
    setInterval(() => {
        const ahora = new Date();
        document.getElementById('reloj').innerText = ahora.toLocaleTimeString('es-PE', { hour12: true });
    }, 1000);

    // 2. FOCO AUTOMÁTICO
    const inputCodigo = document.getElementById('codigo_alumno');
    document.addEventListener('click', () => inputCodigo.focus());

    // 3. DETECTAR ENTER
    inputCodigo.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            let codigo = this.value.trim();
            if(codigo.length > 0) {
                enviarAsistencia(codigo);
            }
            this.value = ""; 
        }
    });

    // 4. ENVIAR AL BACKEND 
    function enviarAsistencia(codigo) {
        document.getElementById('loading').classList.remove('d-none');
        
        let formData = new FormData();
        formData.append('codigo_barra', codigo);

        fetch('../../backend/marcar_asistencia.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error del Servidor: ${response.status} ${response.statusText}`);
            }
            return response.text();
        })
        .then(texto => {
            try {
                const data = JSON.parse(texto);
                document.getElementById('loading').classList.add('d-none');
                mostrarResultado(data);
            } catch (e) {
                console.error("Respuesta inválida:", texto);
                throw new Error("El sistema recibió datos corruptos. Revisa el archivo backend.");
            }
        })
        .catch(error => {
            console.error('Fallo Fetch:', error);
            document.getElementById('loading').classList.add('d-none');
            document.getElementById('mensaje_estado').className = 'alert alert-danger fw-bold';
            document.getElementById('mensaje_estado').innerHTML = `<i class="bi bi-x-circle me-2"></i> ${error.message}`;
        });
    }

    // 5. PINTAR RESULTADO 
    function mostrarResultado(data) {
        const nombreEl = document.getElementById('nombre_alumno');
        const gradoEl  = document.getElementById('grado_alumno');
        const fotoEl   = document.getElementById('foto_alumno');
        const msgEl    = document.getElementById('mensaje_estado');

        msgEl.className = 'alert fw-bold';
        
        if (data.status === 'success') {
            nombreEl.innerText = data.nombre;
            gradoEl.innerText  = data.grado;
            fotoEl.src         = "../../assets/fotos_alumnos/" + (data.foto ? data.foto : 'default.jpg');
            
            if (data.tipo === 'PUNTUAL') {
                msgEl.classList.add('alert-success');
                msgEl.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> PUNTUAL (${data.hora})`;
            } else if (data.tipo === 'TARDE') {
                msgEl.classList.add('alert-warning');
                msgEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i> TARDE (${data.hora})`;
            } else if (data.tipo === 'SALIDA') {
                msgEl.classList.add('alert-primary');
                msgEl.innerHTML = `<i class="bi bi-door-open-fill me-2"></i> SALIDA (${data.hora})`;
            }

        } else if (data.status === 'warning') {
            nombreEl.innerText = data.nombre;
            gradoEl.innerText  = data.grado;
            fotoEl.src         = "../../assets/fotos_alumnos/" + (data.foto ? data.foto : 'default.jpg');
            msgEl.classList.add('alert-info');
            msgEl.innerHTML = `<i class="bi bi-info-circle-fill me-2"></i> ${data.msg}`;

        } else {
            nombreEl.innerText = "No Encontrado";
            gradoEl.innerText  = "-";
            fotoEl.src         = "../../assets/fotos_alumnos/default.jpg";
            msgEl.classList.add('alert-danger');
            msgEl.innerHTML = `<i class="bi bi-x-circle-fill me-2"></i> ${data.msg}`;
        }
    }
</script>
</body>
</html>