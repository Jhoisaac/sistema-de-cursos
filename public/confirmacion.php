<?php
$page_title = "Confirmación de Inscripción";
require_once '../includes/funciones.php';
require_once '../includes/header.php';

$sistema = new SistemaInscripcion();
$codigo_confirmacion = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
$inscripcion = null;

if (!empty($codigo_confirmacion)) {
    $inscripcion = $sistema->obtenerDetallesInscripcion($codigo_confirmacion);
}

if (!$inscripcion) {
    header("Location: index.php");
    exit;
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="confirmation-container">
                <!-- Icono de éxito -->
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h1 class="display-5 fw-bold text-success mb-3">¡Inscripción Exitosa!</h1>
                <p class="lead mb-4">Su inscripción ha sido procesada correctamente</p>
                
                <!-- Código de confirmación -->
                <div class="mb-4">
                    <h5>Código de Confirmación:</h5>
                    <div class="confirmation-code">
                        <?php echo htmlspecialchars($inscripcion['codigo_confirmacion']); ?>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Guarde este código para futuras referencias
                    </small>
                </div>
                
                <hr class="my-4">
                
                <!-- Detalles del curso -->
                <div class="row text-start">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <i class="fas fa-book me-2"></i>Detalles del Curso
                                </h5>
                                <p class="mb-2">
                                    <strong>Curso:</strong><br>
                                    <?php echo htmlspecialchars($inscripcion['nombre_curso']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Instructor:</strong><br>
                                    <?php echo htmlspecialchars($inscripcion['instructor']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Fecha de Inicio:</strong><br>
                                    <?php echo date('d/m/Y', strtotime($inscripcion['fecha_inicio'])); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Fecha de Fin:</strong><br>
                                    <?php echo date('d/m/Y', strtotime($inscripcion['fecha_fin'])); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Precio:</strong><br>
                                    <span class="text-success fs-5 fw-bold">
                                        $<?php echo number_format($inscripcion['precio'], 2); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h5 class="card-title text-info">
                                    <i class="fas fa-user me-2"></i>Datos del Estudiante
                                </h5>
                                <p class="mb-2">
                                    <strong>Nombre Completo:</strong><br>
                                    <?php echo htmlspecialchars($inscripcion['nombres'] . ' ' . $inscripcion['apellidos']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Email:</strong><br>
                                    <?php echo htmlspecialchars($inscripcion['email']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Teléfono:</strong><br>
                                    <?php echo htmlspecialchars($inscripcion['telefono']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Fecha de Inscripción:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($inscripcion['fecha_inscripcion'])); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Estado:</strong><br>
                                    <span class="status-badge status-confirmed">
                                        <?php echo ucfirst($inscripcion['estado_inscripcion']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <!-- Instrucciones -->
                <div class="alert alert-info text-start">
                    <h6><i class="fas fa-lightbulb me-2"></i>Próximos Pasos:</h6>
                    <ol class="mb-0">
                        <li>Guarde su código de confirmación en un lugar seguro</li>
                        <li>Revise su email para recibir más información sobre el curso</li>
                        <li>Realice el pago correspondiente según las instrucciones que recibirá</li>
                        <li>Prepárese para el inicio del curso el <?php echo date('d/m/Y', strtotime($inscripcion['fecha_inicio'])); ?></li>
                    </ol>
                </div>
                
                <!-- Botones de acción -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <a href="index.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-home me-2"></i>Ir al Inicio
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="cursos.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-book me-2"></i>Ver Más Cursos
                        </a>
                    </div>
                    <div class="col-md-4">
                        <button onclick="window.print()" class="btn btn-outline-info w-100">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información de contacto -->
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <h5><i class="fas fa-question-circle me-2"></i>¿Necesita Ayuda?</h5>
                    <p class="mb-3">Si tiene alguna pregunta sobre su inscripción, no dude en contactarnos:</p>
                    <div class="row">
                        <div class="col-md-4">
                            <i class="fas fa-envelope text-primary mb-2"></i>
                            <p><strong>Email:</strong><br>info@cursossistema.com</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-phone text-success mb-2"></i>
                            <p><strong>Teléfono:</strong><br>+593 2 123-4567</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-clock text-warning mb-2"></i>
                            <p><strong>Horario:</strong><br>Lun-Vie 8:00-18:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para impresión */
@media print {
    .navbar, .footer, .btn, .alert {
        display: none !important;
    }
    
    .confirmation-container {
        box-shadow: none !important;
        border: 2px solid #000 !important;
    }
    
    .confirmation-icon {
        color: #000 !important;
    }
    
    .card {
        border: 1px solid #000 !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>