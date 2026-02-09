<?php
session_start();
require_once 'includes/config.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $prioridad = $_POST['prioridad'] ?? 'media';
    $cliente_id = $_SESSION['cliente_id'];
    
    if (empty($titulo)) {
        $error = 'El título es obligatorio';
    } elseif (empty($descripcion)) {
        $error = 'La descripción es obligatoria';
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("INSERT INTO ticket (id_cliente, titulo, descripcion, prioridad) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $cliente_id, $titulo, $descripcion, $prioridad);
        
        if ($stmt->execute()) {
            $ticket_id = $stmt->insert_id;
            $success = "Ticket #$ticket_id creado exitosamente";
            
            // Limpiar el formulario
            $_POST = array();
        } else {
            $error = 'Error al crear el ticket. Inténtelo nuevamente.';
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
    <title>Nuevo Ticket - Sistema de Soporte</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Crear Nuevo Ticket de Soporte</h1>
            <a href="dashboard.php" class="btn btn-secondary">← Volver al Dashboard</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <a href="mis_tickets.php">Ver mis tickets</a>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="nuevo_ticket.php" class="ticket-form">
                <div class="form-group">
                    <label for="titulo">Título del Ticket *</label>
                    <input type="text" id="titulo" name="titulo" required 
                           maxlength="200"
                           placeholder="Resumen breve del problema"
                           value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
                    <small>Máximo 200 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="prioridad">Prioridad *</label>
                    <select id="prioridad" name="prioridad" required>
                        <option value="baja" <?php echo ($_POST['prioridad'] ?? '') == 'baja' ? 'selected' : ''; ?>>
                            Baja - Consulta general
                        </option>
                        <option value="media" <?php echo ($_POST['prioridad'] ?? 'media') == 'media' ? 'selected' : ''; ?>>
                            Media - Problema no urgente
                        </option>
                        <option value="alta" <?php echo ($_POST['prioridad'] ?? '') == 'alta' ? 'selected' : ''; ?>>
                            Alta - Problema importante
                        </option>
                        <option value="critica" <?php echo ($_POST['prioridad'] ?? '') == 'critica' ? 'selected' : ''; ?>>
                            Crítica - Servicio no disponible
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción Detallada *</label>
                    <textarea id="descripcion" name="descripcion" required 
                              rows="10"
                              placeholder="Describa el problema con el mayor detalle posible. Incluya pasos para reproducir el error, mensajes de error, capturas de pantalla, etc."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                    <small>Sea lo más específico posible para ayudarnos a resolver su caso más rápido</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Crear Ticket</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
        
        <div class="info-box">
            <h3>💡 Consejos para crear un buen ticket:</h3>
            <ul>
                <li>Use un título claro y descriptivo</li>
                <li>Proporcione todos los detalles relevantes en la descripción</li>
                <li>Incluya pasos para reproducir el problema</li>
                <li>Mencione cualquier mensaje de error que haya recibido</li>
                <li>Seleccione la prioridad apropiada según la urgencia</li>
            </ul>
        </div>
    </div>
</body>
</html>
