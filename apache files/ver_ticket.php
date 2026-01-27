<?php
session_start();
require_once 'includes/config.php';
requireLogin();

$ticket_id = $_GET['id'] ?? 0;
$cliente_id = $_SESSION['cliente_id'];

if (!$ticket_id) {
    header('Location: mis_tickets.php');
    exit();
}

$conn = getDBConnection();

// Obtener información del ticket
$sql = "SELECT t.*, a.nombre as nombre_admin, a.email as email_admin, a.telefono as telefono_admin
        FROM ticket t 
        LEFT JOIN administrador a ON t.id_adm = a.id_adm 
        WHERE t.id_ticket = ? AND t.id_cliente = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $ticket_id, $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $conn->close();
    header('Location: mis_tickets.php');
    exit();
}

$ticket = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $ticket['id_ticket']; ?> - Sistema de Soporte</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Ticket #<?php echo $ticket['id_ticket']; ?></h1>
            <a href="mis_tickets.php" class="btn btn-secondary">← Volver a Mis Tickets</a>
        </div>
        
        <div class="ticket-detail">
            <!-- Información Principal -->
            <div class="ticket-info-card">
                <div class="ticket-header-info">
                    <h2><?php echo htmlspecialchars($ticket['titulo']); ?></h2>
                    <div class="ticket-badges">
                        <span class="badge badge-<?php echo $ticket['prioridad']; ?>">
                            <?php echo ucfirst($ticket['prioridad']); ?>
                        </span>
                        <span class="badge badge-estado-<?php echo $ticket['estado']; ?>">
                            <?php echo str_replace('_', ' ', ucfirst($ticket['estado'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="ticket-meta">
                    <div class="meta-item">
                        <strong>Fecha de Creación:</strong>
                        <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creado'])); ?>
                    </div>
                    
                    <?php if ($ticket['fecha_resuelto']): ?>
                        <div class="meta-item">
                            <strong>Fecha de Resolución:</strong>
                            <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_resuelto'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="meta-item">
                        <strong>Asignado a:</strong>
                        <?php 
                        if ($ticket['nombre_admin']) {
                            echo htmlspecialchars($ticket['nombre_admin']);
                        } else {
                            echo '<span class="text-muted">Sin asignar</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Descripción -->
            <div class="ticket-section">
                <h3>Descripción del Problema</h3>
                <div class="ticket-description">
                    <?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?>
                </div>
            </div>
            
            <!-- Observaciones del Administrador -->
            <?php if (!empty($ticket['observaciones'])): ?>
                <div class="ticket-section observaciones-section">
                    <h3>Observaciones del Administrador</h3>
                    <div class="ticket-observaciones">
                        <?php echo nl2br(htmlspecialchars($ticket['observaciones'])); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Información del Administrador Asignado -->
            <?php if ($ticket['nombre_admin']): ?>
                <div class="ticket-section admin-section">
                    <h3>Información del Administrador Asignado</h3>
                    <div class="admin-info">
                        <div class="admin-detail">
                            <strong>Nombre:</strong>
                            <?php echo htmlspecialchars($ticket['nombre_admin']); ?>
                        </div>
                        <?php if ($ticket['email_admin']): ?>
                            <div class="admin-detail">
                                <strong>Email:</strong>
                                <a href="mailto:<?php echo htmlspecialchars($ticket['email_admin']); ?>">
                                    <?php echo htmlspecialchars($ticket['email_admin']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ($ticket['telefono_admin']): ?>
                            <div class="admin-detail">
                                <strong>Teléfono:</strong>
                                <a href="tel:<?php echo htmlspecialchars($ticket['telefono_admin']); ?>">
                                    <?php echo htmlspecialchars($ticket['telefono_admin']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Estado del Ticket -->
            <div class="ticket-timeline">
                <h3>Estado del Ticket</h3>
                <div class="timeline">
                    <div class="timeline-item <?php echo in_array($ticket['estado'], ['abierto', 'en_proceso', 'resuelto', 'cerrado']) ? 'completed' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>Ticket Creado</strong>
                            <small><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creado'])); ?></small>
                        </div>
                    </div>
                    
                    <div class="timeline-item <?php echo in_array($ticket['estado'], ['en_proceso', 'resuelto', 'cerrado']) ? 'completed' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>En Proceso</strong>
                            <?php if ($ticket['nombre_admin']): ?>
                                <small>Asignado a <?php echo htmlspecialchars($ticket['nombre_admin']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="timeline-item <?php echo in_array($ticket['estado'], ['resuelto', 'cerrado']) ? 'completed' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>Resuelto</strong>
                            <?php if ($ticket['fecha_resuelto']): ?>
                                <small><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_resuelto'])); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="timeline-item <?php echo $ticket['estado'] == 'cerrado' ? 'completed' : ''; ?>">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>Cerrado</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    $result->close();
    $conn->close();
    ?>
</body>
</html>
