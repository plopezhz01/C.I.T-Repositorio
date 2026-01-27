# Sistema de Tickets de Soporte

Sistema web de gestión de tickets de soporte técnico desarrollado con PHP y MySQL.

## 📋 Características

- **Autenticación de clientes**: Login seguro con contraseñas hasheadas
- **Gestión de tickets**: Crear, ver y seguir el estado de tickets de soporte
- **Filtros avanzados**: Filtrar tickets por estado y prioridad
- **Estadísticas**: Dashboard con resumen de tickets
- **Seguimiento**: Ver historial completo y observaciones de cada ticket
- **Responsive**: Diseño adaptable a dispositivos móviles

## 🛠️ Requisitos

- Apache 2.4 o superior
- PHP 7.4 o superior
- MySQL 5.7 o superior / MariaDB 10.3 o superior
- Extensiones PHP requeridas:
  - mysqli
  - session

## 📦 Instalación

### 1. Clonar/Copiar archivos

Copia todos los archivos del proyecto a tu directorio web de Apache:

```bash
# En Linux (Ubuntu/Debian)
sudo cp -r soporte_tickets/ /var/www/html/

# En Windows con XAMPP
# Copiar a: C:\xampp\htdocs\soporte_tickets\

# En macOS con MAMP
# Copiar a: /Applications/MAMP/htdocs/soporte_tickets/
```

### 2. Crear la base de datos

Ejecuta el script SQL principal para crear la base de datos y las tablas:

```sql
-- Ejecutar en MySQL/phpMyAdmin el contenido del archivo SQL que te proporcionaron
CREATE DATABASE IF NOT EXISTS soporte 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE soporte;

-- Continuar con las tablas...
```

### 3. Insertar datos de ejemplo (Opcional)

Para probar el sistema, ejecuta el archivo `datos_ejemplo.sql`:

```bash
mysql -u root -p < datos_ejemplo.sql
```

O importa el archivo en phpMyAdmin.

### 4. Configurar la conexión a la base de datos

Edita el archivo `includes/config.php` con tus credenciales de MySQL:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'soporte');
```

### 5. Configurar permisos (Linux)

```bash
sudo chown -R www-data:www-data /var/www/html/soporte_tickets
sudo chmod -R 755 /var/www/html/soporte_tickets
```

## 🚀 Uso

### Acceder al sistema

1. Abre tu navegador y ve a: `http://localhost/soporte_tickets/`
2. Serás redirigido a la página de login

### Credenciales de prueba

Si instalaste los datos de ejemplo, puedes usar:

**Usuarios de cliente:**
- Usuario: `juan_perez` | Contraseña: `password123`
- Usuario: `maria_garcia` | Contraseña: `password123`
- Usuario: `carlos_lopez` | Contraseña: `password123`

### Funcionalidades disponibles

**Para clientes:**
- ✅ Iniciar sesión
- ✅ Ver dashboard con estadísticas
- ✅ Crear nuevos tickets de soporte
- ✅ Ver lista completa de sus tickets
- ✅ Filtrar tickets por estado y prioridad
- ✅ Ver detalles completos de cada ticket
- ✅ Seguir el progreso de resolución
- ✅ Ver observaciones del administrador
- ✅ Cerrar sesión

## 📁 Estructura del proyecto

```
soporte_tickets/
├── css/
│   └── style.css              # Estilos CSS
├── includes/
│   ├── config.php             # Configuración y funciones comunes
│   └── header.php             # Header común para todas las páginas
├── dashboard.php              # Panel principal del cliente
├── index.php                  # Página de inicio (redirección)
├── login.php                  # Página de inicio de sesión
├── logout.php                 # Cerrar sesión
├── mis_tickets.php            # Lista completa de tickets con filtros
├── nuevo_ticket.php           # Formulario para crear ticket
├── ver_ticket.php             # Ver detalle de un ticket
└── datos_ejemplo.sql          # Datos de prueba (opcional)
```

## 🔐 Seguridad

El sistema implementa las siguientes medidas de seguridad:

- ✅ Contraseñas hasheadas con `password_hash()` (bcrypt)
- ✅ Prepared statements para prevenir SQL Injection
- ✅ Validación y sanitización de inputs con `htmlspecialchars()`
- ✅ Sesiones seguras con cookies HttpOnly
- ✅ Verificación de autenticación en todas las páginas protegidas
- ✅ Protección CSRF mediante validación de sesión

## 🎨 Personalización

### Cambiar colores

Edita las variables CSS en `css/style.css`:

```css
:root {
    --primary-color: #2563eb;
    --primary-hover: #1d4ed8;
    --secondary-color: #64748b;
    /* ... más colores */
}
```

### Modificar logo

Edita el texto en `includes/header.php`:

```html
<div class="logo">
    <h2>🎫 Tu Empresa de Soporte</h2>
</div>
```

## 🐛 Solución de problemas

### Error de conexión a la base de datos

- Verifica que MySQL esté corriendo
- Comprueba las credenciales en `includes/config.php`
- Asegúrate de que la base de datos existe

### Páginas en blanco

- Activa la visualización de errores en PHP:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```
- Revisa los logs de Apache: `/var/log/apache2/error.log`

### Problemas con sesiones

- Verifica que el directorio de sesiones tenga permisos de escritura
- Comprueba que las cookies estén habilitadas en el navegador

### Estilos CSS no cargan

- Verifica la ruta relativa en los archivos PHP
- Limpia la caché del navegador (Ctrl + F5)
- Comprueba permisos del archivo CSS

## 📝 Crear nuevos clientes

Para crear nuevos usuarios cliente, necesitas insertar registros en la tabla `cliente` con contraseñas hasheadas:

```php
<?php
// Genera una contraseña hasheada
echo password_hash("tu_contraseña", PASSWORD_DEFAULT);
?>
```

Luego inserta en la base de datos:

```sql
INSERT INTO cliente (nombre, contrasena, telefono, email) VALUES
('nuevo_usuario', '$2y$10$hash_generado...', '555-1234', 'email@ejemplo.com');
```

## 🔄 Próximas mejoras sugeridas

- [ ] Panel de administrador para gestionar tickets
- [ ] Sistema de notificaciones por email
- [ ] Adjuntar archivos a los tickets
- [ ] Chat en tiempo real
- [ ] Historial de cambios en tickets
- [ ] Exportar reportes en PDF/Excel
- [ ] Sistema de calificación de soporte
- [ ] Multi-idioma

## 📄 Licencia

Este proyecto es de código abierto y está disponible para uso personal y comercial.

## 👨‍💻 Soporte

Para reportar problemas o sugerir mejoras, por favor documenta:
- Versión de PHP y MySQL
- Mensaje de error completo
- Pasos para reproducir el problema

---

Desarrollado con ❤️ para facilitar la gestión de soporte técnico
