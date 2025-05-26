<?php
$page_title = "Mi Perfil";
require_once '../includes/funciones.php';
require_once '../includes/header.php';

// Requiere autenticación
requiereAutenticacion();

$sistema = new SistemaInscripcion();
$usuario_sesion = obtenerUsuarioSesion();
$usuario = $sistema->obtenerUsuario($usuario_sesion['id_usuario']);
$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización de perfil
if ($_POST) {
    $datos = $sistema->sanitizarDatos($_POST);
    
    // Validaciones
    $errores = [];
    
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
            $errores[] = 'Debe ser mayor de 16 años';
        }
    }
    
    // Actualizar contraseña si se proporciona
    if (!empty($datos['nueva_password'])) {
        if (strlen($datos['nueva_password']) < 6) {
            $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }
        
        if ($datos['nueva_password'] !== $datos['confirmar_password']) {
            $errores[] = 'Las contraseñas no coinciden';
        }
        
        // Verificar contraseña actual si se está cambiando
        if (empty($datos['password_actual'])) {
            $errores[] = 'Debe ingresar su contraseña actual para cambiarla';
        }
    }
    
    if (empty($errores)) {
        try {
            $conn = (new Database())->getConnection();
            $conn->beginTransaction();
            
            // Verificar contraseña actual si se está cambiando
            if (!empty($datos['nueva_password'])) {
                if (!password_verify($datos['password_actual'], $usuario['password_hash'])) {
                    throw new Exception('La contraseña actual es incorrecta');
                }
            }
            
            // Actualizar tabla usuarios
            $query = "UPDATE usuarios SET nombres = :nombres, apellidos = :apellidos, 
                     telefono = :telefono, fecha_nacimiento = :fecha_nacimiento, direccion = :direccion";
            
            if (!empty($datos['nueva_password'])) {
                $query .= ", password_hash = :password_hash";
            }
            
            $query .= " WHERE id_usuario = :id_usuario";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidos', $datos['apellidos']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':id_usuario', $usuario_sesion['id_usuario']);
            
            if (!empty($datos['nueva_password'])) {
                $password_hash = password_hash($datos['nueva_password'], PASSWORD_DEFAULT);
                $stmt->bindParam(':password_hash', $password_hash);
            }
            
            $stmt->execute();
            
            // Actualizar tabla estudiantes
            $query = "UPDATE estudiantes SET nombres = :nombres, apellidos = :apellidos, 
                     telefono = :telefono, fecha_nacimiento = :fecha_nacimiento, direccion = :direccion
                     WHERE id_usuario = :id_usuario";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidos', $datos['apellidos']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':id_usuario', $usuario_sesion['id_usuario']);
            
            $stmt->execute();
            
            $conn->commit();
            
            // Actualizar datos de sesión
            $_SESSION['usuario_nombres'] = $datos['nombres'];
            $_SESSION['usuario_apellidos'] = $datos['apellidos'];
            
            $mensaje = 'Perfil actualizado exitosamente';
            $tipo_mensaje = 'success';
            
            // Recargar datos del usuario
            $usuario = $sistema->obtenerUsuario($usuario_sesion['id_usuario']);
            
        } catch(Exception $e) {
            $conn->rollBack();
            $mensaje = $e->getMessage();
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = 'Por favor corrija los siguientes errores:<br>• ' . implode('<br>• ', $errores);
        $tipo_mensaje = 'error';
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Mi Perfil
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Mostrar mensajes -->
                    <?php if (!empty($mensaje)): ?>
                        <?php echo mostrarAlerta($tipo_mensaje, $mensaje); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <!-- Información Personal -->
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-user me-2"></i>Información Personal
                        </h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" required
                                       value="<?php echo isset($_POST['nombres']) ? htmlspecialchars($_POST['nombres']) : htmlspecialchars($usuario['nombres']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" required
                                       value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : htmlspecialchars($usuario['apellidos']); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cedula" class="form-label">Cédula</label>
                                <input type="text" class="form-control" id="cedula" name="cedula" readonly
                                       value="<?php echo htmlspecialchars($usuario['cedula']); ?>">
                                <small class="text-muted">La cédula no se puede modificar</small>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" readonly
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>">
                                <small class="text-muted">El email no se puede modificar</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono *</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="10" required
                                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : htmlspecialchars($usuario['telefono']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                                       value="<?php echo isset($_POST['fecha_nacimiento']) ? htmlspecialchars($_POST['fecha_nacimiento']) : htmlspecialchars($usuario['fecha_nacimiento']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="3"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : htmlspecialchars($usuario['direccion']); ?></textarea>
                        </div>
                        
                        <hr>
                        
                        <!-- Cambio de Contraseña -->
                        <h5 class="text-warning mb-3">
                            <i class="fas fa-lock me-2"></i>Cambiar Contraseña
                        </h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Deje estos campos vacíos si no desea cambiar su contraseña.
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="password_actual" class="form-label">Contraseña Actual</label>
                                <input type="password" class="form-control" id="password_actual" name="password_actual">
                            </div>
                            <div class="col-md-4">
                                <label for="nueva_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="nueva_password" name="nueva_password" minlength="6">
                            </div>
                            <div class="col-md-4">
                                <label for="confirmar_password" class="form-label">Confirmar Nueva</label>
                                <input type="password" class="form-control" id="confirmar_password" name="confirmar_password">
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Información de Cuenta -->
                        <h5 class="text-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>Información de Cuenta
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha de Registro:</strong><br>
                                   <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Último Acceso:</strong><br>
                                   <?php echo $usuario['fecha_ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['fecha_ultimo_acceso'])) : 'N/A'; ?></p>
                            </div>
                        </div>
                        
                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="mis_cursos.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver a Mis Cursos
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>