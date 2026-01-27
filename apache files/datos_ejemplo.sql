-- Script de ejemplo para insertar datos de prueba
-- Ejecutar después de crear la base de datos

USE soporte;

-- Insertar clientes de ejemplo
-- NOTA: Las contraseñas están hasheadas con password_hash()
-- Contraseña para todos los usuarios de ejemplo: "password123"

INSERT INTO cliente (nombre, contrasena, telefono, email) VALUES
('juan_perez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0001', 'juan.perez@email.com'),
('maria_garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0002', 'maria.garcia@email.com'),
('carlos_lopez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0003', 'carlos.lopez@email.com');

-- Insertar administradores de ejemplo
INSERT INTO administrador (nombre, contrasena, sucursal, email, telefono) VALUES
('Admin Principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Central', 'admin@soporte.com', '555-1000'),
('Soporte Técnico 1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Norte', 'soporte1@soporte.com', '555-1001'),
('Soporte Técnico 2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sur', 'soporte2@soporte.com', '555-1002');

-- Insertar tickets de ejemplo
INSERT INTO ticket (id_adm, id_cliente, titulo, descripcion, prioridad, estado, observaciones, fecha_creado, fecha_resuelto) VALUES
(1, 1, 'No puedo acceder a mi cuenta', 'He intentado restablecer mi contraseña varias veces pero no recibo el correo de recuperación.', 'alta', 'resuelto', 'Se reenvió el correo de recuperación y se verificó que llegara correctamente. Cliente pudo acceder a su cuenta.', '2025-01-20 10:30:00', '2025-01-20 14:45:00'),

(2, 1, 'Error al cargar imágenes', 'Cuando intento subir imágenes al sistema, aparece un error 500. He probado con diferentes navegadores.', 'media', 'en_proceso', 'Se está investigando el problema con el equipo de desarrollo. Parece ser un problema con el tamaño máximo de archivo.', '2025-01-25 09:15:00', NULL),

(NULL, 2, 'Consulta sobre facturación', '¿Cuándo se emitirá la factura del mes de enero? Necesito presentarla para contabilidad.', 'baja', 'abierto', NULL, '2025-01-26 16:20:00', NULL),

(1, 2, 'Sistema lento en horas pico', 'El sistema se vuelve muy lento entre las 2pm y 4pm. A veces tarda hasta 30 segundos en cargar una página.', 'alta', 'resuelto', 'Se optimizaron las consultas a la base de datos y se aumentó la capacidad del servidor. El rendimiento ha mejorado significativamente.', '2025-01-22 11:00:00', '2025-01-24 10:00:00'),

(3, 3, 'No recibo notificaciones por correo', 'Configuré las notificaciones pero no me llegan los correos cuando hay actualizaciones importantes.', 'media', 'en_proceso', 'Se verificó la configuración del correo electrónico. El problema está relacionado con el servidor de correo. Trabajando en solución.', '2025-01-24 13:45:00', NULL),

(NULL, 3, 'Solicitud de nueva funcionalidad', 'Me gustaría que el sistema permitiera exportar reportes en formato Excel además de PDF.', 'baja', 'abierto', NULL, '2025-01-27 08:30:00', NULL);

-- Verificar que todo se insertó correctamente
SELECT 'Clientes insertados:' as '', COUNT(*) as total FROM cliente;
SELECT 'Administradores insertados:' as '', COUNT(*) as total FROM administrador;
SELECT 'Tickets insertados:' as '', COUNT(*) as total FROM ticket;

-- Mostrar información de login
SELECT '
===================================
INFORMACIÓN DE LOGIN PARA PRUEBAS
===================================

Usuarios de cliente (contraseña para todos: password123):
- Usuario: juan_perez
- Usuario: maria_garcia
- Usuario: carlos_lopez

Nota: Las contraseñas están hasheadas con bcrypt.
Para crear nuevas contraseñas hasheadas, puedes usar:
<?php echo password_hash("tu_contraseña", PASSWORD_DEFAULT); ?>
' as 'INFORMACIÓN';
