<?php
$page_title = "Inscripción";
require_once '../includes/funciones.php';
require_once '../includes/header.php';

$sistema = new SistemaInscripcion();
$cursos = $sistema->obtenerCursos();
$curso_seleccionado = isset($_GET['curso']) ? (int)$_GET['curso'] : null;
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_POST) {
    $datos = $sistema->sanitizarDatos($_POST);
    
    // Validaciones
    $errores = [];
    
    if (empty($datos['cedula']) || !$sistema->validarCedula($datos['cedula'])) {
        $errores[] = 'Cédula ecuatoriana inválida';
    } elseif (!$sistema->validarCedulaUnica($datos['cedula'])) {
        $errores[] = 'La cédula ya está registrada en el sistema';
    }
    
    if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email inválido';
    } elseif (!$sistema->validarEmailUnico($datos['email'])) {
        $errores[] = 'El email ya está registrado en el sistema';
    }
    
    if (empty($datos['nombres']) || strlen($datos['nombres']) < 2) {
        $errores[] = 'Los nombres son requeridos (mínimo 2 caracteres)';
    }
    
    if (empty($datos['apellidos']) || strlen($datos['apellidos']) < 2) {
        $errores[] = 'Los apellidos son requeridos (mínimo 2 caracteres)';
    }
    
    if (empty($datos['telefono']) || !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
        $errores[] = 'Teléfono inválido (debe tener 10 dígitos)';
    }
    
    if (empty($datos['fecha_nacimiento'])) {
        $errores[] = 'Fecha de nacimiento es requerida';
    } else {
        $edad = date_diff(date_create($datos['fecha_nacimiento']), date_create('today'))->y;
        if ($edad < 16) {
            $errores[] = 'Debe ser mayor de 16 años para inscribirse';
        }
    }
    
    if (empty($datos['id_curso']) || !is_numeric($datos['id_curso'])) {
        $errores[] = 'Debe seleccionar un curso válido';
    } else {
        $curso = $sistema->obtenerCursoPorId($datos['id_curso']);
        if (!$curso) {
            $errores[] = 'El curso seleccionado no existe o no está disponible';
        } elseif (!$sistema->verificarCuposDisponibles($datos['id_curso'])) {
            $errores[] = 'No hay cupos disponibles para el curso seleccionado';
        }
    }
    
    if (empty($errores)) {
        // Procesar inscripción
        $id_estudiante = $sistema->procesarEstudiante($datos);
        
        if ($id_estudiante) {
            $resultado = $sistema->procesarInscripcion($datos['id_curso'], $id_estudiante);
            
            if ($resultado['success']) {
                // Redirigir a página de confirmación
                header("Location: confirmacion.php?codigo=" . $resultado['codigo_confirmacion']);
                exit;
            } else {
                $mensaje = $resultado['message'];
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = 'Error al procesar los datos del estudiante';
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = 'Por favor corrija los siguientes errores:<br>• ' . implode('<br>• ', $errores);
        $tipo_mensaje = 'error';
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-container">
                <div class="text-center mb-4">
                    <h1 class="display-5 fw-bold">Inscripción a Curso</h1>
                    <p class="text-muted">Complete el formulario para inscribirse en nuestros cursos</p>
                </div>
                
                <!-- Mostrar mensajes -->
                <?php if (!empty($mensaje)): ?>
                    <?php echo mostrarAlerta($tipo_mensaje, $mensaje); ?>
                <?php endif; ?>
                
                <form id="inscripcionForm" method="POST" action="" novalidate>
                    <!-- Selección de Curso -->
                    <div class="mb-4">
                        <label for="id_curso" class="form-label fw-bold">
                            <i class="fas fa-book me-2"></i>Curso a Inscribirse *
                        </label>
                        <select class="form-select form-select-lg" id="id_curso" name="id_curso" required>
                            <option value="">Seleccione un curso...</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?php echo $curso['id_curso']; ?>" 
                                        <?php echo ($curso_seleccionado == $curso['id_curso']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($curso['nombre_curso']); ?> - 
                                    $<?php echo number_format($curso['precio'], 2); ?> - 
                                    Inicio: <?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Datos Personales -->
                    <h4 class="mb-3"><i class="fas fa-user me-2"></i>Datos Personales</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" 
                                   placeholder="1234567890" maxlength="10" required
                                   value="<?php echo isset($_POST['cedula']) ? htmlspecialchars($_POST['cedula']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                                   value="<?php echo isset($_POST['fecha_nacimiento']) ? htmlspecialchars($_POST['fecha_nacimiento']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" 
                                   placeholder="Juan Carlos" required
                                   value="<?php echo isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                   placeholder="Pérez González" required
                                   value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Información de Contacto -->
                    <h4 class="mb-3"><i class="fas fa-envelope me-2"></i>Información de Contacto</h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="juan.perez@email.com" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   placeholder="0987654321" maxlength="10" required
                                   value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="3" 
                                  placeholder="Ingrese su dirección completa"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Términos y Condiciones -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terminos" required>
                            <label class="form-check-label" for="terminos">
                                Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#terminosModal">términos y condiciones</a> 
                                y autorizo el tratamiento de mis datos personales *
                            </label>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="row">
                        <div class="col-md-6">
                            <a href="cursos.php" class="btn btn-outline-secondary btn-lg w-100">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Cursos
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-user-plus me-2"></i>Inscribirse Ahora
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Términos y Condiciones -->
<div class="modal fade" id="terminosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Aceptación de Términos</h6>
                <p>Al inscribirse en nuestros cursos, usted acepta cumplir con estos términos y condiciones.</p>
                
                <h6>2. Inscripción y Pagos</h6>
                <p>La inscripción se considera válida una vez completado el proceso y recibido el código de confirmación.</p>
                
                <h6>3. Política de Cancelación</h6>
                <p>Las cancelaciones deben realizarse con al menos 48 horas de anticipación al inicio del curso.</p>
                
                <h6>4. Protección de Datos</h6>
                <p>Sus datos personales serán tratados de acuerdo con nuestra política de privacidad y la normativa vigente.</p>
                
                <h6>5. Responsabilidades del Estudiante</h6>
                <p>El estudiante se compromete a asistir puntualmente y participar activamente en las actividades del curso.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
