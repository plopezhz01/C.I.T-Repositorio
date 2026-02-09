=== Proyecto WordPress ===

Nombre del proyecto: soporte tickets
Autor: Nombre del autor o empresa
Sitio web: https://ejemplo.com
Versión inicial: 1.0.0
Fecha de creación: 2026-02-09
Licencia: GPLv2 o posterior

== Descripción ==

Este proyecto es una instalación de WordPress utilizada para el sitio web "Mi Sitio WordPress". 
Incluye el núcleo de WordPress, un tema personalizado y varios plugins adicionales.

== Estructura de carpetas ==

/wp-admin      Panel de administración de WordPress.
/wp-includes   Archivos del núcleo de WordPress.
/wp-content    Contiene temas, plugins y subidas de medios.
/wp-content/themes   Temas instalados, incluido el tema activo del sitio.
/wp-content/plugins  Plugins instalados y activos/inactivos.
/wp-content/uploads  Imágenes, documentos y otros archivos subidos.

/wp-config.php Archivo de configuración principal de WordPress.
/.htaccess     Reglas de servidor web (URLs amigables, seguridad, etc.).

== Requisitos ==

- PHP 8.0 o superior recomendado.
- MySQL/MariaDB con soporte para WordPress.
- Servidor web (Apache, Nginx u otro compatible).
- Extensiones PHP habituales para WordPress (curl, mbstring, mysqli, etc.).

== Instalación / Despliegue ==

1. Subir todos los archivos al servidor en el directorio público del dominio.
2. Crear una base de datos vacía para WordPress.
3. Editar wp-config.php con los datos de conexión a la base de datos.
4. Acceder a https://ejemplo.com/wp-admin/ para completar el asistente de instalación.
5. Configurar los enlaces permanentes y los ajustes básicos desde el panel.

== Copias de seguridad ==

Se recomienda realizar copias de seguridad periódicas de:
- Base de datos (todas las tablas de WordPress).
- Carpeta /wp-content (temas, plugins, uploads).

== Notas adicionales ==

- No modificar archivos del núcleo de WordPress; usar temas hijo o plugins personalizados.
- Documentar cualquier cambio manual en temas o plugins en este archivo.
- Registrar aquí versiones, cambios importantes y tareas pendientes.

== Historial de cambios ==

1.0.0
- Instalación inicial de WordPress.
- Subida del tema personalizado.
- Instalación de los plugins básicos.
