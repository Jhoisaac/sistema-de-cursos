<?php
$page_title = "Cursos Disponibles";
require_once '../includes/funciones.php';
require_once '../includes/header.php';

$sistema = new SistemaInscripcion();
$cursos = $sistema->obtenerCursos();
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$mensaje = '';
$tipo_mensaje = '';

// Procesar inscripción si el usuario está autenticado
if ($_POST && isset($_POST['inscribirse']) && verificarAutenticacion()) {
    $id_curso = (int)$_POST['id_curso'];
    $usuario = obtenerUsuarioSesion();
    
    $resultado = $sistema->procesarInscripcionUsuario($id_curso, $usuario['id_usuario']);
    
    if ($resultado['success']) {
        header("Location: confirmacion.php?codigo=" . $resultado['codigo_confirmacion']);
        exit;
    } else {
        $mensaje = $resultado['message'];
        $tipo_mensaje = 'error';
    }
}

// Filtrar cursos por búsqueda si existe
if (!empty($busqueda)) {
    $cursos = array_filter($cursos, function($curso) use ($busqueda) {
        return stripos($curso['nombre_curso'], $busqueda) !== false || 
               stripos($curso['descripcion'], $busqueda) !== false ||
               stripos($curso['instructor'], $busqueda) !== false;
    });
}
?>

<div class="container my-5">
    <!-- Header de la página -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Cursos Disponibles</h1>
        <p class="lead text-muted">Explora nuestra amplia selección de cursos profesionales</p>
    </div>
    
    <!-- Barra de búsqueda -->
    <div class="row mb-4">
        <div class="col-lg-6 mx-auto">
            <form method="GET" action="">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="buscar" 
                           placeholder="Buscar cursos..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Mostrar mensajes -->
    <?php if (!empty($mensaje)): ?>
        <?php echo mostrarAlerta($tipo_mensaje, $mensaje); ?>
    <?php endif; ?>
    
    <!-- Resultados de búsqueda -->
    <?php if (!empty($busqueda)): ?>
        <div class="alert alert-info">
            <i class="fas fa-search me-2"></i>
            Se encontraron <?php echo count($cursos); ?> resultado(s) para: 
            <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong>
            <a href="cursos.php" class="btn btn-sm btn-outline-info ms-2">Ver todos</a>
        </div>
    <?php endif; ?>
    
    <!-- Lista de cursos -->
    <div class="row">
        <?php if (!empty($cursos)): ?>
            <?php foreach ($cursos as $curso): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card course-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text flex-grow-1">
                                <?php echo htmlspecialchars($curso['descripcion']); ?>
                            </p>
                            
                            <div class="course-details mt-auto">
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>Instructor:
                                        </small>
                                        <div class="course-instructor">
                                            <?php echo htmlspecialchars($curso['instructor']); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>Inicio:
                                        </small>
                                        <div>
                                            <?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>Duración:
                                        </small>
                                        <div>
                                            <?php 
                                            $inicio = new DateTime($curso['fecha_inicio']);
                                            $fin = new DateTime($curso['fecha_fin']);
                                            $diferencia = $inicio->diff($fin);
                                            echo $diferencia->days . ' días';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>Cupos:
                                        </small>
                                        <div>
                                            <?php echo $curso['cupos_disponibles']; ?> disponibles
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="course-price">
                                        $<?php echo number_format($curso['precio'], 2); ?>
                                    </div>
                                    <?php if (verificarAutenticacion()): ?>
                                        <?php
                                        $usuario = obtenerUsuarioSesion();
                                        $ya_inscrito = $sistema->verificarInscripcionExistente($curso['id_curso'], $usuario['id_usuario']);
                                        ?>
                                        <?php if ($ya_inscrito): ?>
                                            <span class="btn btn-outline-success btn-sm disabled">
                                                <i class="fas fa-check me-1"></i>Ya Inscrito
                                            </span>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="id_curso" value="<?php echo $curso['id_curso']; ?>">
                                                <button type="submit" name="inscribirse" class="btn btn-success btn-sm">
                                                    <i class="fas fa-user-plus me-1"></i>Inscribirse
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="../public/login.php?redirect=<?php echo urlencode('../public/cursos.php'); ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h3>No se encontraron cursos</h3>
                    <?php if (!empty($busqueda)): ?>
                        <p class="text-muted">No hay cursos que coincidan con tu búsqueda.</p>
                        <a href="cursos.php" class="btn btn-primary">Ver todos los cursos</a>
                    <?php else: ?>
                        <p class="text-muted">No hay cursos disponibles en este momento.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Call to action -->
    <?php if (!empty($cursos)): ?>
        <div class="text-center mt-5 py-5 bg-light rounded">
            <h3>¿No encontraste lo que buscabas?</h3>
            <p class="text-muted mb-3">Contáctanos y te ayudaremos a encontrar el curso perfecto para ti.</p>
            <a href="mailto:info@cursossistema.com" class="btn btn-outline-primary">
                <i class="fas fa-envelope me-2"></i>Contáctanos
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>