=== Proyecto WordPress ===

Nombre del proyecto: C.I.P Tickets
Autor: C.I.P
Sitio web: https://192.168.31.84
Versión inicial: 1.0
Fecha de creación: 2026-02-09
Licencia: GPLv2 o posterior

== Descripción ==

Este proyecto es una instalación de WordPress para el sistema de gestión de tickets "C.I.P Tickets".
Incluye un plugin personalizado para soporte de tickets y un tema específico del grupo 7 para la interfaz del sitio.

== Estructura de carpetas ==

/wordpress
/wordpress/plugin/soporte-tickets Archivos del plugin personalizado de soporte de tickets.
/wordpress/temas/grupo7 Archivos del tema utilizado por el proyecto.
/wordpress/readme.txt Este archivo de información del proyecto.

== Plugin "soporte-tickets" ==

El directorio /plugin/soporte-tickets contiene el código del plugin encargado de la creación, gestión y visualización de tickets de soporte dentro de WordPress.
Cualquier nueva funcionalidad o corrección del sistema de tickets debe implementarse aquí y documentarse en la sección de historial de cambios.

== Tema "grupo7" ==

El directorio /temas/grupo7 contiene el tema activo del sitio, incluyendo plantillas, hojas de estilo y scripts.
Las modificaciones de diseño o estructura visual del sitio deben realizarse en este tema y anotarse en el historial de cambios del proyecto.

== Requisitos ==

PHP 8.0 o superior recomendado.

Servidor web compatible (Apache, Nginx, etc.).

MySQL o MariaDB con soporte para WordPress.

Extensiones PHP habituales para WordPress (mysqli, curl, mbstring, json, etc.).

== Instalación / Despliegue ==

Clonar o descargar este repositorio en el servidor.

Colocar la carpeta wordpress en el directorio público del servidor (por ejemplo, /var/www/html).

Crear una base de datos vacía para "C.I.P Tickets".

Configurar wp-config.php con los datos de conexión a la base de datos y las claves de seguridad.

Acceder a https://192.168.31.84/wp-admin/ y completar la instalación de WordPress.

Activar el tema grupo7 desde Apariencia → Temas.

Activar el plugin soporte-tickets desde Plugins → Instalados.

== Copias de seguridad ==

Se recomienda realizar copias de seguridad periódicas de:

Base de datos del sitio (todas las tablas de WordPress).

Carpeta /wordpress/wp-content, especialmente:

/plugin/soporte-tickets (código del plugin).

/temas/grupo7 (código del tema).

/uploads (archivos subidos por usuarios).

== Buenas prácticas ==

No modificar archivos del núcleo de WordPress; realizar cambios únicamente en el plugin soporte-tickets y en el tema grupo7.

Documentar en este archivo cualquier cambio relevante de código, estructura de base de datos o configuración especial del servidor.

== Historial de cambios ==

1.0 (2026-02-09)

Creación del proyecto "C.I.P Tickets".

Añadido plugin personalizado soporte-tickets.

Añadido tema grupo7 como tema principal del sitio.