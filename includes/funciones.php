<?php
require_once '../config/conexion.php';
require_once '../config/config.php';

session_start();

class SistemaInscripcion {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Registrar nuevo usuario
    public function registrarUsuario($datos) {
        try {
            // Verificar si el email o cédula ya existen
            if (!$this->validarEmailUnico($datos['email'])) {
                throw new Exception('El email ya está registrado');
            }
            
            if (!$this->validarCedulaUnica($datos['cedula'])) {
                throw new Exception('La cédula ya está registrada');
            }

            $this->conn->beginTransaction();                        
            
            // Hash de la contraseña
            $password_hash = password_hash($datos['clave'], PASSWORD_DEFAULT);
            
            // Insertar en tabla usuarios
            $query = "INSERT INTO usuarios (email, password_hash, nombres, apellidos, cedula, telefono, fecha_nacimiento, direccion) 
                     VALUES (:email, :password_hash, :nombres, :apellidos, :cedula, :telefono, :fecha_nacimiento, :direccion)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidos', $datos['apellidos']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':cedula', $datos['cedula']);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);                                                          
            $stmt->bindParam(':direccion', $datos['direccion']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':password_hash', $password_hash); 
            
            $stmt->execute();
            $id_usuario = $this->conn->lastInsertId();
            
            // Insertar en tabla estudiantes
            $query = "INSERT INTO estudiantes (id_usuario, cedula, nombres, apellidos, email, telefono, fecha_nacimiento, direccion) 
                     VALUES (:id_usuario, :cedula, :nombres, :apellidos, :email, :telefono, :fecha_nacimiento, :direccion)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':cedula', $datos['cedula']);
            $stmt->bindParam(':nombres', $datos['nombres']);
            $stmt->bindParam(':apellidos', $datos['apellidos']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':telefono', $datos['telefono']);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
            $stmt->bindParam(':direccion', $datos['direccion']);
            
            $stmt->execute();
            
            $this->conn->commit();
            return ['success' => true, 'id_usuario' => $id_usuario, 'message' => 'Usuario registrado exitosamente'];
            
        } catch(PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error en registro de usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];

        } catch(Exception $e) {
             if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }   
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Autenticar usuario
    public function autenticarUsuario($email, $password) {
        try {
            $query = "SELECT u.*, e.id_estudiante FROM usuarios u 
                     LEFT JOIN estudiantes e ON u.id_usuario = e.id_usuario 
                     WHERE u.email = :email AND u.estado = 'activo'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $usuario = $stmt->fetch();           
            
            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                // Actualizar último acceso
                $this->actualizarUltimoAcceso($usuario['id_usuario']);
                // Guardamos datos esenciales en sesión
                iniciarSesionUsuario($usuario);                

                return true;
            } else {
                return false;
            }
            
        } catch(PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    // Actualizar último acceso
    private function actualizarUltimoAcceso($id_usuario) {
        try {
            $query = "UPDATE usuarios SET fecha_ultimo_acceso = NOW() WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error actualizando último acceso: " . $e->getMessage());
        }
    }
    
    // Obtener usuario por ID
    public function obtenerUsuario($id_usuario) {
        try {
            $query = "SELECT u.*, e.id_estudiante FROM usuarios u 
                     LEFT JOIN estudiantes e ON u.id_usuario = e.id_usuario 
                     WHERE u.id_usuario = :id_usuario";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Error obteniendo usuario: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener inscripciones de un usuario
    public function obtenerInscripcionesUsuario($id_usuario) {
        try {
            $query = "SELECT i.*, c.nombre_curso, c.instructor, c.fecha_inicio, c.fecha_fin, c.precio,
                            c.descripcion, c.cupos_disponibles
                     FROM inscripciones i
                     JOIN cursos c ON i.id_curso = c.id_curso
                     JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                     WHERE e.id_usuario = :id_usuario
                     ORDER BY i.fecha_inscripcion DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error obteniendo inscripciones del usuario: " . $e->getMessage());
            return [];
        }
    }
    
    // Actualizar estado de inscripción
    public function actualizarInscripcion($id_inscripcion, $id_usuario, $nuevo_estado, $observaciones = '') {
        try {
            // Verificar que la inscripción pertenece al usuario
            $query = "SELECT i.* FROM inscripciones i
                     JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                     WHERE i.id_inscripcion = :id_inscripcion AND e.id_usuario = :id_usuario";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_inscripcion', $id_inscripcion);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Inscripción no encontrada o no autorizada'];
            }
            
            // Actualizar inscripción
            $query = "UPDATE inscripciones 
                     SET estado_inscripcion = :estado, observaciones = :observaciones 
                     WHERE id_inscripcion = :id_inscripcion";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindParam(':observaciones', $observaciones);
            $stmt->bindParam(':id_inscripcion', $id_inscripcion);
            
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Inscripción actualizada exitosamente'];
            
        } catch(PDOException $e) {
            error_log("Error actualizando inscripción: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    // Cancelar inscripción
    public function cancelarInscripcion($id_inscripcion, $id_usuario, $motivo = '') {
        return $this->actualizarInscripcion($id_inscripcion, $id_usuario, 'cancelada', $motivo);
    }

    // Eliminar inscripción (solo para canceladas)
    public function eliminarInscripcion($id_inscripcion, $id_usuario) {
        try {
            // Verificar que la inscripción pertenece al usuario y está cancelada
            $query = "SELECT i.*, c.nombre_curso FROM inscripciones i
                     JOIN cursos c ON i.id_curso = c.id_curso
                     JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                     WHERE i.id_inscripcion = :id_inscripcion 
                           AND e.id_usuario = :id_usuario 
                           AND i.estado_inscripcion = 'cancelada'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_inscripcion', $id_inscripcion);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            $inscripcion = $stmt->fetch();
            
            if (!$inscripcion) {
                return ['success' => false, 'message' => 'Inscripción no encontrada o no se puede eliminar'];
            }
            
            // Eliminar inscripción
            $query = "DELETE FROM inscripciones WHERE id_inscripcion = :id_inscripcion";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_inscripcion', $id_inscripcion);
            
            if ($stmt->execute()) {
                return [
                    'success' => true, 
                    'message' => 'Inscripción eliminada exitosamente',
                    'curso_eliminado' => $inscripcion['nombre_curso']
                ];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar la inscripción'];
            }
            
        } catch(PDOException $e) {
            error_log("Error eliminando inscripción: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    // Verificar si usuario ya está inscrito en un curso
    public function verificarInscripcionExistente($id_curso, $id_usuario) {
        try {
            $query = "SELECT i.* FROM inscripciones i
                     JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                     WHERE i.id_curso = :id_curso AND e.id_usuario = :id_usuario 
                           AND i.estado_inscripcion != 'cancelada'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_curso', $id_curso);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            error_log("Error verificando inscripción existente: " . $e->getMessage());
            return false;
        }
    }
    
    // Procesar inscripción para usuario autenticado
    public function procesarInscripcionUsuario($id_curso, $id_usuario) {
        try {
            // Obtener datos del estudiante
            $query = "SELECT id_estudiante FROM estudiantes WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            $estudiante = $stmt->fetch();
            
            if (!$estudiante) {
                return ['success' => false, 'message' => 'Estudiante no encontrado'];
            }
            
            // Verificar si ya está inscrito
            if ($this->verificarInscripcionExistente($id_curso, $id_usuario)) {
                return ['success' => false, 'message' => 'Ya está inscrito en este curso'];
            }
            
            // Verificar cupos disponibles
            if (!$this->verificarCuposDisponibles($id_curso)) {
                return ['success' => false, 'message' => 'No hay cupos disponibles'];
            }
            
            // Procesar inscripción (sin transacción anidada)
            $resultado = $this->procesarInscripcion($id_curso, $estudiante['id_estudiante']);
            
            return $resultado;
            
        } catch(PDOException $e) {
            error_log("Error procesando inscripción de usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    // Obtener todos los cursos activos
    public function obtenerCursos() {
        try {
            $query = "SELECT * FROM cursos WHERE estado = 'activo' AND fecha_inicio > CURDATE() ORDER BY fecha_inicio ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error al obtener cursos: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtener curso específico por ID
    public function obtenerCursoPorId($id_curso) {
        try {
            $query = "SELECT * FROM cursos WHERE id_curso = :id_curso AND estado = 'activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Error al obtener curso: " . $e->getMessage());
            return false;
        }
    }
    
    // Validar cédula ecuatoriana
    public function validarCedula($cedula) {
        if (strlen($cedula) != 10) return false;
        
        $digitos = str_split($cedula);
        $region = substr($cedula, 0, 2);
        
        if ($region < 1 || $region > 24) return false;
        
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $num = intval($digitos[$i]);
            if ($i % 2 == 0) {
                $num *= 2;
                if ($num > 9) $num -= 9;
            }
            $sum += $num;
        }
        
        $verificador = (10 - ($sum % 10)) % 10;
        return $verificador == intval($digitos[9]);
    }
    
    // Validar email único (actualizado para usuarios)
    public function validarEmailUnico($email, $id_usuario = null) {
        try {
            $query = "SELECT id_usuario FROM usuarios WHERE email = :email";
            if ($id_usuario) {
                $query .= " AND id_usuario != :id_usuario";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            if ($id_usuario) {
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            return $stmt->rowCount() == 0;
        } catch(PDOException $e) {
            error_log("Error al validar email: " . $e->getMessage());
            return false;
        }
    }
    
    // Validar cédula única (actualizado para usuarios)
    public function validarCedulaUnica($cedula, $id_usuario = null) {
        try {
            $query = "SELECT id_usuario FROM usuarios WHERE cedula = :cedula";
            if ($id_usuario) {
                $query .= " AND id_usuario != :id_usuario";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cedula', $cedula);
            if ($id_usuario) {
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            return $stmt->rowCount() == 0;
        } catch(PDOException $e) {
            error_log("Error al validar cédula: " . $e->getMessage());
            return false;
        }
    }
    
    // Insertar o actualizar estudiante
    public function procesarEstudiante($datos) {
        try {
            // Verificar si el estudiante ya existe por email o cédula
            $query = "SELECT id_estudiante FROM estudiantes WHERE email = :email OR cedula = :cedula";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':cedula', $datos['cedula']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Estudiante existe, obtener ID
                $estudiante = $stmt->fetch();
                return $estudiante['id_estudiante'];
            } else {
                // Insertar nuevo estudiante
                $query = "INSERT INTO estudiantes (cedula, nombres, apellidos, email, telefono, fecha_nacimiento, direccion) 
                         VALUES (:cedula, :nombres, :apellidos, :email, :telefono, :fecha_nacimiento, :direccion)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':cedula', $datos['cedula']);
                $stmt->bindParam(':nombres', $datos['nombres']);
                $stmt->bindParam(':apellidos', $datos['apellidos']);
                $stmt->bindParam(':email', $datos['email']);
                $stmt->bindParam(':telefono', $datos['telefono']);
                $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
                $stmt->bindParam(':direccion', $datos['direccion']);
                
                $stmt->execute();
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log("Error al procesar estudiante: " . $e->getMessage());
            return false;
        }
    }
    
    // Verificar disponibilidad de cupos
    public function verificarCuposDisponibles($id_curso) {
        try {
            $query = "SELECT c.cupos_disponibles, 
                            COUNT(i.id_inscripcion) as inscritos
                     FROM cursos c
                     LEFT JOIN inscripciones i ON c.id_curso = i.id_curso 
                                                AND i.estado_inscripcion != 'cancelada'
                     WHERE c.id_curso = :id_curso
                     GROUP BY c.id_curso";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            if ($resultado) {
                return ($resultado['cupos_disponibles'] - $resultado['inscritos']) > 0;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error al verificar cupos: " . $e->getMessage());
            return false;
        }
    }
    
    // Procesar inscripción
    public function procesarInscripcion($id_curso, $id_estudiante) {
        try {
            // Verificar si la conexión ya tiene una transacción activa
            $transaccion_iniciada = false;
            
            if (!$this->conn->inTransaction()) {
                $this->conn->beginTransaction();
                $transaccion_iniciada = true;
            }
            
            // Verificar si ya está inscrito
            $query = "SELECT id_inscripcion FROM inscripciones 
                     WHERE id_curso = :id_curso AND id_estudiante = :id_estudiante 
                           AND estado_inscripcion != 'cancelada'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                if ($transaccion_iniciada) {
                    $this->conn->rollBack();
                }
                return ['success' => false, 'message' => 'Ya está inscrito en este curso'];
            }
            
            // Verificar cupos disponibles
            if (!$this->verificarCuposDisponibles($id_curso)) {
                if ($transaccion_iniciada) {
                    $this->conn->rollBack();
                }
                return ['success' => false, 'message' => 'No hay cupos disponibles'];
            }
            
            // Generar código de confirmación
            $codigo_confirmacion = $this->generarCodigoConfirmacion();
            
            // Insertar inscripción
            $query = "INSERT INTO inscripciones (id_curso, id_estudiante, codigo_confirmacion, estado_inscripcion) 
                     VALUES (:id_curso, :id_estudiante, :codigo_confirmacion, 'confirmada')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
            $stmt->bindParam(':codigo_confirmacion', $codigo_confirmacion);
            
            $stmt->execute();
            $id_inscripcion = $this->conn->lastInsertId();
            
            if ($transaccion_iniciada) {
                $this->conn->commit();
            }
            
            return [
                'success' => true, 
                'id_inscripcion' => $id_inscripcion,
                'codigo_confirmacion' => $codigo_confirmacion,
                'message' => 'Inscripción exitosa'
            ];
            
        } catch(PDOException $e) {
            if ($transaccion_iniciada && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error al procesar inscripción: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }
    
    // Generar código de confirmación único
    private function generarCodigoConfirmacion() {
        return 'CONF-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }
    
    // Obtener detalles de inscripción
    public function obtenerDetallesInscripcion($codigo_confirmacion) {
        try {
            $query = "SELECT i.*, c.nombre_curso, c.instructor, c.fecha_inicio, c.fecha_fin, c.precio,
                            e.nombres, e.apellidos, e.email, e.telefono
                     FROM inscripciones i
                     JOIN cursos c ON i.id_curso = c.id_curso
                     JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
                     WHERE i.codigo_confirmacion = :codigo";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $codigo_confirmacion);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Error al obtener detalles: " . $e->getMessage());
            return false;
        }
    }
    
    // Sanitizar datos de entrada
    public function sanitizarDatos($datos) {
        $datos_limpios = [];
        foreach ($datos as $key => $value) {
            if (is_string($value)) {
                $datos_limpios[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $datos_limpios[$key] = $value;
            }
        }
        return $datos_limpios;
    }
}

// Verificar si el usuario está autenticado
function verificarAutenticacion() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Obtener datos del usuario de la sesión
function obtenerUsuarioSesion() {
    if (verificarAutenticacion()) {
        return [
            'id_usuario' => $_SESSION['usuario_id'],
            'id_estudiante' => $_SESSION['id_estudiante'] ?? null,
            'email' => $_SESSION['usuario_email'],
            'nombres' => $_SESSION['usuario_nombres'],
            'apellidos' => $_SESSION['usuario_apellidos'],
            'nombre_completo' => $_SESSION['usuario_nombres'] . ' ' . $_SESSION['usuario_apellidos']
        ];
    }
    return null;
}

// Iniciar sesión de usuario
function iniciarSesionUsuario($datos_usuario) {
    $_SESSION['usuario_id'] = $datos_usuario['id_usuario'];
    $_SESSION['id_estudiante'] = $datos_usuario['id_estudiante'];
    $_SESSION['usuario_email'] = $datos_usuario['email'];
    $_SESSION['usuario_nombres'] = $datos_usuario['nombres'];
    $_SESSION['usuario_apellidos'] = $datos_usuario['apellidos'];
    $_SESSION['tiempo_sesion'] = time();
}

// Cerrar sesión
function cerrarSesion() {
    session_unset();
    session_destroy();
}

// Redirigir a login si no está autenticado
function requiereAutenticacion($redirigir_a = '../public/login.php') {
    if (!verificarAutenticacion()) {
        header("Location: $redirigir_a");
        exit;
    }
}

// Redirigir si ya está autenticado
function redirigirSiAutenticado($redirigir_a = '../public/mis_cursos.php') {
    if (verificarAutenticacion()) {
        header("Location: $redirigir_a");
        exit;
    }
}

// Función para mostrar alertas
function mostrarAlerta($tipo, $mensaje) {
    $clases = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $clase = isset($clases[$tipo]) ? $clases[$tipo] : 'alert-info';
    
    return "<div class='alert {$clase} alert-dismissible fade show' role='alert'>
                {$mensaje}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}
?>