<?php
$page_title = "Mis Cursos";
require_once '../includes/funciones.php';
require_once '../includes/header.php';

// Requiere autenticación
requiereAutenticacion();

$sistema = new SistemaInscripcion();
$usuario = obtenerUsuarioSesion();
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones (cancelar y eliminar inscripción)
if ($_POST && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'cancelar' && isset($_POST['id_inscripcion'])) {
        $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : 'Cancelado por el usuario';
        $resultado = $sistema->cancelarInscripcion($_POST['id_inscripcion'], $usuario['id_usuario'], $motivo);
        
        $mensaje = $resultado['message'];
        $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
    }
    
    if ($_POST['accion'] === 'eliminar' && isset($_POST['id_inscripcion'])) {
        $resultado = $sistema->eliminarInscripcion($_POST['id_inscripcion'], $usuario['id_usuario']);
        
        $mensaje = $resultado['message'];
        $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
        
        if ($resultado['success']) {
            $mensaje = 'Inscripción al curso "' . $resultado['curso_eliminado'] . '" eliminada permanentemente';
        }
    }
}

// Obtener inscripciones del usuario
$inscripciones = $sistema->obtenerInscripcionesUsuario($usuario['id_usuario']);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="fas fa-graduation-cap me-2"></i>Mis Cursos</h1>
            <p class="text-muted mb-0">Bienvenido, <?php echo htmlspecialchars($usuario['nombre_completo']); ?></p>
        </div>
        <div>
            <a href="cursos.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Inscribirme a más cursos
            </a>
        </div>
    </div>
    
    <!-- Mostrar mensajes -->
    <?php if (!empty($mensaje)): ?>
        <?php echo mostrarAlerta($tipo_mensaje, $mensaje); ?>
    <?php endif; ?>
    
    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <?php
        $total_inscripciones = count($inscripciones);
        $confirmadas = count(array_filter($inscripciones, function($i) { return $i['estado_inscripcion'] === 'confirmada'; }));
        $pendientes = count(array_filter($inscripciones, function($i) { return $i['estado_inscripcion'] === 'pendiente'; }));
        $canceladas = count(array_filter($inscripciones, function($i) { return $i['estado_inscripcion'] === 'cancelada'; }));
        ?>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $total_inscripciones; ?></h3>
                    <small>Total Inscripciones</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $confirmadas; ?></h3>
                    <small>Confirmadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $pendientes; ?></h3>
                    <small>Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $canceladas; ?></h3>
                    <small>Canceladas</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de inscripciones -->
    <?php if (!empty($inscripciones)): ?>
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-list me-2"></i>Mis Inscripciones</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Curso</th>
                                <th>Instructor</th>
                                <th>Fecha Inicio</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Fecha Inscripción</th>
                                <th>Código</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $inscripcion): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($inscripcion['nombre_curso']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($inscripcion['descripcion'], 0, 60)) . '...'; ?>
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($inscripcion['instructor']); ?></td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($inscripcion['fecha_inicio'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            Fin: <?php echo date('d/m/Y', strtotime($inscripcion['fecha_fin'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            $<?php echo number_format($inscripcion['precio'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $estado_clases = [
                                            'confirmada' => 'success',
                                            'pendiente' => 'warning',
                                            'cancelada' => 'danger'
                                        ];
                                        $clase = $estado_clases[$inscripcion['estado_inscripcion']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $clase; ?>">
                                            <?php echo ucfirst($inscripcion['estado_inscripcion']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($inscripcion['fecha_inscripcion'])); ?>
                                    </td>
                                    <td>
                                        <code class="small">
                                            <?php echo htmlspecialchars($inscripcion['codigo_confirmacion']); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Botón Ver Detalles -->
                                            <button type="button" class="btn btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detalleModal<?php echo $inscripcion['id_inscripcion']; ?>"
                                                    title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <!-- Botón Cancelar (solo si no está cancelada) -->
                                            <?php if ($inscripcion['estado_inscripcion'] !== 'cancelada'): ?>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#cancelarModal<?php echo $inscripcion['id_inscripcion']; ?>"
                                                        title="Cancelar inscripción">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Botón Eliminar (solo si está cancelada) -->
                                            <?php if ($inscripcion['estado_inscripcion'] === 'cancelada'): ?>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#eliminarModal<?php echo $inscripcion['id_inscripcion']; ?>"
                                                        title="Eliminar inscripción">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
            <h3>No tienes inscripciones aún</h3>
            <p class="text-muted mb-4">¡Explora nuestros cursos y comienza tu aprendizaje!</p>
            <a href="cursos.php" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i>Ver Cursos Disponibles
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modales para cada inscripción -->
<?php foreach ($inscripciones as $inscripcion): ?>
    <!-- Modal de Detalles -->
    <div class="modal fade" id="detalleModal<?php echo $inscripcion['id_inscripcion']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Detalles del Curso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Información del Curso</h6>
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($inscripcion['nombre_curso']); ?></p>
                            <p><strong>Instructor:</strong> <?php echo htmlspecialchars($inscripcion['instructor']); ?></p>
                            <p><strong>Descripción:</strong><br><?php echo htmlspecialchars($inscripcion['descripcion']); ?></p>
                            <p><strong>Precio:</strong> $<?php echo number_format($inscripcion['precio'], 2); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Información de Inscripción</h6>
                            <p><strong>Fecha de Inicio:</strong> <?php echo date('d/m/Y', strtotime($inscripcion['fecha_inicio'])); ?></p>
                            <p><strong>Fecha de Fin:</strong> <?php echo date('d/m/Y', strtotime($inscripcion['fecha_fin'])); ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge bg-<?php echo $estado_clases[$inscripcion['estado_inscripcion']] ?? 'secondary'; ?>">
                                    <?php echo ucfirst($inscripcion['estado_inscripcion']); ?>
                                </span>
                            </p>
                            <p><strong>Código:</strong> <code><?php echo htmlspecialchars($inscripcion['codigo_confirmacion']); ?></code></p>
                            <p><strong>Inscripción:</strong> <?php echo date('d/m/Y H:i', strtotime($inscripcion['fecha_inscripcion'])); ?></p>
                            <?php if (!empty($inscripcion['observaciones'])): ?>
                                <p><strong>Observaciones:</strong><br><?php echo htmlspecialchars($inscripcion['observaciones']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Cancelación -->
    <?php if ($inscripcion['estado_inscripcion'] !== 'cancelada'): ?>
        <div class="modal fade" id="cancelarModal<?php echo $inscripcion['id_inscripcion']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Cancelar Inscripción
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" name="accion" value="cancelar">
                            <input type="hidden" name="id_inscripcion" value="<?php echo $inscripcion['id_inscripcion']; ?>">
                            
                            <div class="alert alert-warning">
                                <strong>¿Está seguro de cancelar esta inscripción?</strong>
                                <br>
                                Curso: <strong><?php echo htmlspecialchars($inscripcion['nombre_curso']); ?></strong>
                                <br><br>
                                <small><i class="fas fa-info-circle me-1"></i>
                                Podrá eliminar permanentemente el registro después de cancelarlo.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="motivo<?php echo $inscripcion['id_inscripcion']; ?>" class="form-label">
                                    Motivo de cancelación (opcional):
                                </label>
                                <textarea class="form-control" 
                                          id="motivo<?php echo $inscripcion['id_inscripcion']; ?>" 
                                          name="motivo" rows="3" 
                                          placeholder="Ingrese el motivo de la cancelación..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-check me-2"></i>Confirmar Cancelación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Modal de Eliminación (solo para inscripciones canceladas) -->
    <?php if ($inscripcion['estado_inscripcion'] === 'cancelada'): ?>
        <div class="modal fade" id="eliminarModal<?php echo $inscripcion['id_inscripcion']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-trash me-2"></i>
                            Eliminar Inscripción
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_inscripcion" value="<?php echo $inscripcion['id_inscripcion']; ?>">
                            
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>¡ATENCIÓN! Esta acción no se puede deshacer.</strong>
                                <br><br>
                                Se eliminará permanentemente el registro de inscripción al curso:
                                <br>
                                <strong><?php echo htmlspecialchars($inscripcion['nombre_curso']); ?></strong>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Información del registro:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Fecha de inscripción: <?php echo date('d/m/Y H:i', strtotime($inscripcion['fecha_inscripcion'])); ?></li>
                                    <li>Estado: <span class="badge bg-danger">Cancelada</span></li>
                                    <li>Código: <code><?php echo htmlspecialchars($inscripcion['codigo_confirmacion']); ?></code></li>
                                </ul>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmarEliminacion<?php echo $inscripcion['id_inscripcion']; ?>" required>
                                <label class="form-check-label text-danger fw-bold" for="confirmarEliminacion<?php echo $inscripcion['id_inscripcion']; ?>">
                                    Confirmo que deseo eliminar permanentemente este registro
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Eliminar Permanentemente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?><?php