<?php
session_start();
require_once 'includes/config.php';
requireLogin();

$conn = getDBConnection();
$cliente_id = $_SESSION['cliente_id'];

// Filtros
$filtro_estado = $_GET['estado'] ?? 'todos';
$filtro_prioridad = $_GET['prioridad'] ?? 'todos';

// Construir consulta
$sql = "SELECT t.*, a.nombre as nombre_admin 
        FROM ticket t 
        LEFT JOIN administrador a ON t.id_adm = a.id_adm 
        WHERE t.id_cliente = ?";

$params = [$cliente_id];
$types = "i";

if ($filtro_estado != 'todos') {
    $sql .= " AND t.estado = ?";
    $params[] = $filtro_estado;
    $types .= "s";
}

if ($filtro_prioridad != 'todos') {
    $sql .= " AND t.prioridad = ?";
    $params[] = $filtro_prioridad;
    $types .= "s";
}

$sql .= " ORDER BY t.fecha_creado DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tickets = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tickets - Sistema de Soporte</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Mis Tickets de Soporte</h1>
            <a href="nuevo_ticket.php" class="btn btn-primary">+ Nuevo Ticket</a>
        </div>
        
        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" action="mis_tickets.php" class="filters-form">
                <div class="filter-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" onchange="this.form.submit()">
                        <option value="todos" <?php echo $filtro_estado == 'todos' ? 'selected' : ''; ?>>
                            Todos
                        </option>
                        <option value="abierto" <?php echo $filtro_estado == 'abierto' ? 'selected' : ''; ?>>
                            Abierto
                        </option>
                        <option value="en_proceso" <?php echo $filtro_estado == 'en_proceso' ? 'selected' : ''; ?>>
                            En Proceso
                        </option>
                        <option value="resuelto" <?php echo $filtro_estado == 'resuelto' ? 'selected' : ''; ?>>
                            Resuelto
                        </option>
                        <option value="cerrado" <?php echo $filtro_estado == 'cerrado' ? 'selected' : ''; ?>>
                            Cerrado
                        </option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="prioridad">Prioridad:</label>
                    <select id="prioridad" name="prioridad" onchange="this.form.submit()">
                        <option value="todos" <?php echo $filtro_prioridad == 'todos' ? 'selected' : ''; ?>>
                            Todas
                        </option>
                        <option value="baja" <?php echo $filtro_prioridad == 'baja' ? 'selected' : ''; ?>>
                            Baja
                        </option>
                        <option value="media" <?php echo $filtro_prioridad == 'media' ? 'selected' : ''; ?>>
                            Media
                        </option>
                        <option value="alta" <?php echo $filtro_prioridad == 'alta' ? 'selected' : ''; ?>>
                            Alta
                        </option>
                        <option value="critica" <?php echo $filtro_prioridad == 'critica' ? 'selected' : ''; ?>>
                            Crítica
                        </option>
                    </select>
                </div>
                
                <?php if ($filtro_estado != 'todos' || $filtro_prioridad != 'todos'): ?>
                    <a href="mis_tickets.php" class="btn btn-small btn-secondary">Limpiar Filtros</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Tabla de Tickets -->
        <?php if ($tickets->num_rows > 0): ?>
            <div class="tickets-table-container">
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Asignado a</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = $tickets->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $ticket['id_ticket']; ?></td>
                                <td class="ticket-title">
                                    <?php echo htmlspecialchars($ticket['titulo']); ?>
                                </td>
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
                                <td>
                                    <?php 
                                    echo $ticket['nombre_admin'] 
                                        ? htmlspecialchars($ticket['nombre_admin']) 
                                        : '<span class="text-muted">Sin asignar</span>'; 
                                    ?>
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
        <?php else: ?>
            <div class="empty-state">
                <p>No se encontraron tickets con los filtros seleccionados.</p>
                <?php if ($filtro_estado != 'todos' || $filtro_prioridad != 'todos'): ?>
                    <a href="mis_tickets.php" class="btn btn-secondary">Ver todos los tickets</a>
                <?php else: ?>
                    <a href="nuevo_ticket.php" class="btn btn-primary">Crear mi primer ticket</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    $tickets->close();
    $conn->close();
    ?>
</body>
</html>
