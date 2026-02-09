<?php
session_start();
require_once 'includes/config.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (empty($nombre) || empty($contrasena)) {
        $error = 'Por favor, complete todos los campos';
    } else {
        $conn = getDBConnection();
        
        // Preparar consulta
        $stmt = $conn->prepare("SELECT id_cliente, nombre, contrasena FROM cliente WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $cliente = $result->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($contrasena, $cliente['contrasena'])) {
                // Login exitoso
                $_SESSION['cliente_id'] = $cliente['id_cliente'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Nombre de usuario o contraseña incorrectos';
            }
        } else {
            $error = 'Nombre de usuario o contraseña incorrectos';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Soporte</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Sistema de Soporte</h1>
                <p>Inicie sesión para gestionar sus tickets</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" class="login-form">
                <div class="form-group">
                    <label for="nombre">Nombre de Usuario</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
