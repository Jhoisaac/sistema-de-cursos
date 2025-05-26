<?php
$page_title = "Inicio";
require_once '../includes/funciones.php';
require_once '../includes/header.php';

$sistema = new SistemaInscripcion();
$cursos_destacados = array_slice($sistema->obtenerCursos(), 0, 3);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1 class="display-4 fw-bold mb-4">
                    Transforma tu Futuro con Nuestros Cursos
                </h1>
                <p class="lead mb-4">
                    Descubre una amplia variedad de cursos diseñados para potenciar tus habilidades 
                    y acelerar tu crecimiento profesional.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="../public/cursos.php" class="btn btn-light btn-lg">
                        <i class="fas fa-book me-2"></i>Ver Cursos
                    </a>
                    <?php if (verificarAutenticacion()): ?>
                        <a href="../public/mis_cursos.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-graduation-cap me-2"></i>Mis Cursos
                        </a>
                    <?php else: ?>
                        <a href="../public/login.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Comenzar Ahora
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-graduation-cap" style="font-size: 15rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <i class="fas fa-users text-primary mb-3" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold">500+</h3>
                    <p class="text-muted">Estudiantes Inscritos</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <i class="fas fa-book text-success mb-3" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold">25+</h3>
                    <p class="text-muted">Cursos Disponibles</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <i class="fas fa-chalkboard-teacher text-warning mb-3" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold">15+</h3>
                    <p class="text-muted">Instructores Expertos</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-4">
                    <i class="fas fa-certificate text-info mb-3" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold">95%</h3>
                    <p class="text-muted">Tasa de Satisfacción</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cursos Destacados -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Cursos Destacados</h2>
            <p class="lead text-muted">Descubre los cursos más populares de nuestra plataforma</p>
        </div>
        
        <div class="row">
            <?php if (!empty($cursos_destacados)): ?>
                <?php foreach ($cursos_destacados as $curso): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card course-card h-100">
                            <div class="card-header text-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h5>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="card-text flex-grow-1">
                                    <?php echo htmlspecialchars(substr($curso['descripcion'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="course-details">
                                    <p class="course-instructor mb-2">
                                        <i class="fas fa-user me-2"></i>
                                        <?php echo htmlspecialchars($curso['instructor']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-calendar me-2"></i>
                                        <?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-users me-2"></i>
                                        <?php echo $curso['cupos_disponibles']; ?> cupos
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="course-price">$<?php echo number_format($curso['precio'], 2); ?></span>
                                        <a href="../public/cursos.php?curso=<?php echo $curso['id_curso']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Ver Curso
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No hay cursos disponibles en este momento.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="../public/cursos.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-eye me-2"></i>Ver Todos los Cursos
            </a>
        </div>
    </div>
</section>

<!-- Características -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">¿Por Qué Elegirnos?</h2>
        </div>
        
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h4>Horarios Flexibles</h4>
                    <p class="text-muted">Estudia a tu ritmo con horarios que se adapten a tu estilo de vida.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="text-center p-4">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-medal fa-2x"></i>
                    </div>  
                    <h4>Certificación</h4>
                    <p class="text-muted">Obtén certificados reconocidos que validen tus nuevas habilidades.</p>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="text-center p-4">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-headset fa-2x"></i>
                    </div>
                    <h4>Soporte 24/7</h4>
                    <p class="text-muted">Apoyo continuo de nuestro equipo de especialistas.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>