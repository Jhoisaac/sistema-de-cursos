<?php
require_once '../includes/funciones.php';
require_once '../config/conexion.php';
require_once '../includes/header.php';

$sistema = new SistemaInscripcion();

$errors = [];
$action = $_GET['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'register') {
        // Recogemos y validamos datos
        $data = [
            'nombres'         => trim($_POST['nombres']),
            'apellidos'       => trim($_POST['apellidos']),
            'email'            => trim($_POST['email']),
            'cedula'           => trim($_POST['cedula']),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento']),
            'direccion'       => trim($_POST['direccion']),
            'telefono'         => trim($_POST['telefono']),            
            'clave'            => trim($_POST['clave']),
            'confirmar_clave'  => trim($_POST['confirmar_clave'])            
        ];

        if ($data['clave'] !== $data['confirmar_clave']) {
            $errors[] = 'Las contraseñas no coinciden.';
        }
        // Verifica si los datos son válidos
        if (empty($errors)) {
            if ($sistema->registrarUsuario($data)) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Error al registrar. Intenta de nuevo.';
            }
        }
    }

    if ($_POST['action'] === 'login') {
        $usuario = trim($_POST['usuario']);
        $clave = $_POST['clave'];
        if ($sistema->autenticarUsuario($usuario, $clave)) {
            header('Location: cursos.php');
            exit;
        } else {
          echo 'Usuario o contraseña inválidos.';
            $errors[] = 'Usuario o contraseña inválidos.';
        }
    }
}
?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="card-title text-center mb-4">
              <?= $action === 'register' ? 'Registro' : 'Iniciar sesión' ?>
            </h3>

            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                  <div><?= htmlentities($e) ?></div>
                <?php endforeach; ?>
              </div>
            <?php elseif (isset($_GET['registered'])): ?>
              <div class="alert alert-success">¡Registro exitoso! Ya puedes iniciar sesión.</div>
            <?php endif; ?>

            <form method="post" action="login.php?action=<?= $action ?>">
              <input type="hidden" name="action" value="<?= $action ?>">
              
              <?php if ($action === 'register'): ?>
                <!-- Campos de registro (6 mínimos) -->
                <div class="mb-3">
                  <label class="form-label">Nombres</label>
                  <input type="text" name="nombres" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Apellidos</label>
                  <input type="text" name="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="cedula" class="form-label">Cédula *</label>
                  <input type="text" class="form-control" id="cedula" name="cedula" 
                        placeholder="1234567890" maxlength="10" required
                        value="<?php echo isset($_POST['cedula']) ? htmlspecialchars($_POST['cedula']) : ''; ?>">
                </div>
                <div class="mb-3">
                  <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                  <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required
                        value="<?php echo isset($_POST['fecha_nacimiento']) ? htmlspecialchars($_POST['fecha_nacimiento']) : ''; ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">Teléfono</label>
                  <input type="text" name="telefono" class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Dirección</label>
                  <input type="text" name="domicilio" class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Contraseña</label>
                  <input type="password" name="clave" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Confirmar contraseña</label>
                  <input type="password" name="confirmar_clave" class="form-control" required>
                </div>
              <?php else: ?>
                <!-- Formulario de login -->
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="text" name="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Contraseña</label>
                  <input type="password" name="clave" class="form-control" required>
                </div>
              <?php endif; ?>

              <button type="submit" class="btn btn-primary w-100">
                <?= $action === 'register' ? 'Registrarme' : 'Entrar' ?>
              </button>
            </form>
            <hr>
            <div class="text-center">
              <?php if ($action === 'register'): ?>
                ¿Ya tienes cuenta? 
                <a href="login.php?action=login">Iniciar sesión</a>
              <?php else: ?>
                ¿No tienes cuenta? 
                <a href="login.php?action=register">Regístrate</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include '../includes/footer.php'; ?>
