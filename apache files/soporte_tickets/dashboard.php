<?php
session_start();
require_once 'includes/config.php';
requireLogin();

$conn = getDBConnection();
$cliente_id = $_SESSION['cliente_id'];

// Obtener estadísticas
$stats = [
    'total' => 0,
    'abiertos' => 0,
    'en_proceso' => 0,
    'resueltos' => 0
];

$sql = "SELECT estado, COUNT(*) as total FROM ticket WHERE id_cliente = ? GROUP BY estado";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $stats['total'] += $row['total'];
    if ($row['estado'] == 'abierto') $stats['abiertos'] = $row['total'];
    if ($row['estado'] == 'en_proceso') $stats['en_proceso'] = $row['total'];
    if ($row['estado'] == 'resuelto' || $row['estado'] == 'cerrado') $stats['resueltos'] += $row['total'];
}

// Obtener últimos tickets
$sql = "SELECT t.*, a.nombre as nombre_admin 
        FROM ticket t 
        LEFT JOIN administrador a ON t.id_adm = a.id_adm 
        WHERE t.id_cliente = ? 
        ORDER BY t.fecha_creado DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$tickets = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Sistema de Soporte</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard-header">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></h1>
            <a href="nuevo_ticket.php" class="btn btn-primary">+ Nuevo Ticket</a>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">📊</div>
                <div class="stat-info">
                    <h3>Total</h3>
                    <p class="stat-number"><?php echo $stats['total']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon abierto">🆕</div>
                <div class="stat-info">
                    <h3>Abiertos</h3>
                    <p class="stat-number"><?php echo $stats['abiertos']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon proceso">⚙️</div>
                <div class="stat-info">
                    <h3>En Proceso</h3>
                    <p class="stat-number"><?php echo $stats['en_proceso']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon resuelto">✅</div>
                <div class="stat-info">
                    <h3>Resueltos</h3>
                    <p class="stat-number"><?php echo $stats['resueltos']; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Lista de Tickets -->
        <div class="tickets-section">
            <h2>Mis Tickets Recientes</h2>
            
            <?php if ($tickets->num_rows > 0): ?>
                <div class="tickets-table-container">
                    <table class="tickets-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ticket = $tickets->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $ticket['id_ticket']; ?></td>
                                    <td class="ticket-title"><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $ticket['prioridad']; ?>">
                                            <?php echo ucfirst($ticket['prioridad']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-estado-<?php echo $ticket['estado']; ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($ticket['estado'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creado'])); ?></td>
                                    <td>
                                        <a href="ver_ticket.php?id=<?php echo $ticket['id_ticket']; ?>" 
                                           class="btn btn-small">Ver Detalle</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($stats['total'] > 10): ?>
                    <div class="text-center" style="margin-top: 20px;">
                        <a href="mis_tickets.php" class="btn btn-secondary">Ver Todos los Tickets</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No tienes tickets registrados aún.</p>
                    <a href="nuevo_ticket.php" class="btn btn-primary">Crear mi primer ticket</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    $tickets->close();
    $conn->close();
    ?>
</body>
</html>
