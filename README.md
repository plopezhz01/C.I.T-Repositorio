# C.I.T - Centro Integral de Tickets

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![GitHub](https://img.shields.io/badge/GitHub-C.I.T--Repositorio-blue?logo=github)](https://github.com/plopezhz01/C.I.T-Repositorio)
[![Status](https://img.shields.io/badge/Status-Active-brightgreen)]()

> **Sistema integral de gestión de tickets con múltiples plataformas y infraestructura cloud**

---

## 📋 Índice

- [Descripción General](#descripción-general)
- [Características](#características)
- [Tecnologías Utilizadas](#tecnologías-utilizadas)
- [Estructura del Repositorio](#estructura-del-repositorio)
- [Instalación Rápida](#instalación-rápida)
- [Componentes](#componentes)
- [Documentación](#documentación)
- [Contribuciones](#contribuciones)
- [Licencia](#licencia)

---

## 🎯 Descripción General

**C.I.T** es una solución completa de gestión de tickets diseñada para administradores de sistemas que necesitan un sistema robusto, escalable y profesional para la gestión de incidencias y soporte técnico.

El proyecto implementa la misma funcionalidad en múltiples plataformas:
- **PHP + Apache** para aplicaciones web tradicionales
- **WordPress** como plugin para integración con CMS
- **AWS CloudFormation** para despliegue en infraestructura cloud
- **PowerShell** para automatización y backup en entornos Windows

---

## ✨ Características

### 🌐 Sistema Web (PHP)
- ✅ Gestión completa de tickets
- ✅ Sistema de autenticación seguro
- ✅ Dashboard interactivo
- ✅ Base de datos MySQL/MariaDB
- ✅ Interface responsive

### 🔌 Plugin WordPress
- ✅ Integración nativa con WordPress
- ✅ Sistema de soporte-tickets
- ✅ Compatible con WordPress 5.0+

### ☁️ Infraestructura AWS
- ✅ Plantilla CloudFormation lista para producción
- ✅ Configuración IaC (Infrastructure as Code)
- ✅ Escalabilidad automática

### 🔧 Automatización PowerShell
- ✅ Scripts de backup automatizado
- ✅ Gestión de recursos Windows
- ✅ Funciones de administración

---

## 🛠️ Tecnologías Utilizadas

| Componente | Tecnología | Versión |
|-----------|-----------|---------|
| **Web Backend** | PHP | 7.4+ |
| **Web Server** | Apache | 2.4+ |
| **Base de Datos** | MySQL/MariaDB | 5.7+ |
| **CMS** | WordPress | 5.0+ |
| **Cloud** | AWS CloudFormation | Latest |
| **Automation** | PowerShell | 5.0+ |
| **Control de Versiones** | Git | 2.0+ |

---

## 📁 Estructura del Repositorio

```
C.I.T-Repositorio/
├── README.md                          # Este archivo
├── LICENSE                            # Licencia MIT
├── .gitignore                         # Configuración Git
├── docs/                              # Documentación completa
│   ├── presentation/
│   │   └── C.I.T-Project-Presentation.md
│   └── guides/
│       ├── aws-deployment.md
│       ├── powershell-scripts.md
│       └── wordpress-installation.md
├── apache files/                      # Sistema PHP (tickets web)
│   ├── README.md
│   ├── index.php
│   ├── dashboard.php
│   ├── login.php
│   ├── mis_tickets.php
│   ├── nuevo_ticket.php
│   ├── ver_ticket.php
│   ├── logout.php
│   ├── css/                           # Estilos
│   ├── includes/                      # Funciones compartidas
│   └── datos_ejemplo.sql              # Base de datos inicial
├── aws/                               # Infraestructura CloudFormation
│   ├── README.md
│   └── aws.yaml                       # Plantilla CloudFormation
├── powershell/                        # Scripts de automatización
│   ├── README.md
│   └── backup.ps1                     # Script de backup
└── wordpress/                         # Plugin WordPress
    ├── README.md
    └── soporte-tickets/               # Plugin de tickets
```

---

## ⚡ Instalación Rápida

### 🌐 Opción 1: PHP + Apache

```bash
# Clonar repositorio
git clone https://github.com/plopezhz01/C.I.T-Repositorio.git
cd C.I.T-Repositorio

# Copiar a directorio web
cp -r "apache files"/* /var/www/html/

# Configurar base de datos
mysql -u root -p < "apache files/datos_ejemplo.sql"
```

**📖 Documentación completa:** [Apache Setup Guide](docs/guides/apache-setup.md)

---

### ☁️ Opción 2: AWS CloudFormation

```bash
# Desplegar en AWS
aws cloudformation create-stack \
  --stack-name cit-stack \
  --template-body file://aws/aws.yaml
```

**📖 Documentación completa:** [AWS Deployment Guide](docs/guides/aws-deployment.md)

---

### 🔌 Opción 3: WordPress Plugin

1. Descarga la carpeta `wordpress/soporte-tickets/`
2. Copia a `/wp-content/plugins/`
3. Activa el plugin en WordPress

**📖 Documentación completa:** [WordPress Installation Guide](docs/guides/wordpress-installation.md)

---

### 🔧 Opción 4: PowerShell Scripts

```powershell
# Ejecutar script de backup
.\powershell\backup.ps1

# Programar ejecución automática
# Ver documentación para detalles
```

**📖 Documentación completa:** [PowerShell Scripts Guide](docs/guides/powershell-scripts.md)

---

## 🧩 Componentes

### 📦 Sistema Web PHP
Sistema completo de gestión de tickets con autenticación, dashboard y gestión de incidencias.

- **Versión:** 1.0.0
- **Requisitos:** PHP 7.4+, Apache 2.4+, MySQL 5.7+
- **Documentación:** [apache files/README.md](apache%20files/README.md)

---

### 🌐 Plugin WordPress
Plugin nativo para integrar gestión de tickets en sitios WordPress.

- **Versión:** 1.0.0
- **Requisitos:** WordPress 5.0+
- **Documentación:** [wordpress/README.md](wordpress/README.md)

---

### ☁️ CloudFormation
Plantilla IaC para desplegar la solución completa en AWS.

- **Servicio:** AWS CloudFormation
- **Recursos:** EC2, RDS, ALB, Security Groups
- **Documentación:** [aws/README.md](aws/README.md)

---

### ⚙️ PowerShell Scripts
Automatización y herramientas administrativas en PowerShell.

- **Versión:** 1.0
- **Requisitos:** PowerShell 5.0+
- **Documentación:** [powershell/README.md](powershell/README.md)

---

## 📚 Documentación

### 📋 Presentación del Proyecto
- **Archivo:** [docs/presentation/C.I.T-Project-Presentation.md](docs/presentation/C.I.T-Project-Presentation.md)
- Visión general, objetivos, arquitectura y casos de uso

### 🚀 Guías de Instalación

| Guía | Descripción |
|------|-------------|
| [AWS Deployment](docs/guides/aws-deployment.md) | Desplegar en AWS CloudFormation |
| [PowerShell Scripts](docs/guides/powershell-scripts.md) | Usar scripts de automatización |
| [WordPress Installation](docs/guides/wordpress-installation.md) | Instalar plugin en WordPress |

### 📖 Documentación por Componente
- [Sistema PHP](apache%20files/README.md)
- [AWS CloudFormation](aws/README.md)
- [PowerShell](powershell/README.md)
- [WordPress Plugin](wordpress/README.md)

---

## 🔄 Flujo de Trabajo

### Clonar el repositorio
```bash
git clone https://github.com/plopezhz01/C.I.T-Repositorio.git
cd C.I.T-Repositorio
```

### Crear una rama para cambios
```bash
git checkout -b feature/nueva-funcionalidad
```

### Hacer cambios y confirmar
```bash
git add .
git commit -m "Descripción clara del cambio"
git push -u origin feature/nueva-funcionalidad
```

### Crear un Pull Request
Abre un PR en GitHub para revisar cambios antes de mergear a `main`

---

## 🤝 Contribuciones

Este es un proyecto académico/profesional. Las contribuciones son bienvenidas:

1. Fork el repositorio
2. Crea una rama (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📄 Licencia

Este proyecto está licenciado bajo la **Licencia MIT** - ver archivo [LICENSE](LICENSE) para detalles.

**Resumen MIT:**
- ✅ Uso comercial permitido
- ✅ Modificación permitida
- ✅ Distribución permitida
- ✅ Uso privado permitido
- ⚠️ Se debe incluir aviso de licencia y copyright

---

## 📞 Contacto y Soporte

- **Autor:** [@plopezhz01](https://github.com/plopezhz01)
- **Ubicación:** Santander, Cantabria, España
- **Email:** Disponible en perfil GitHub
- **Issues:** [GitHub Issues](https://github.com/plopezhz01/C.I.T-Repositorio/issues)

---

## 📊 Estado del Proyecto

- ✅ Sistema PHP funcional
- ✅ Plugin WordPress disponible
- ✅ Plantilla AWS CloudFormation lista
- ✅ Scripts PowerShell implementados
- ⏳ Próximas mejoras: Tests automatizados, CI/CD

---

## 🎓 Propósito Educativo

Este proyecto fue desarrollado como parte del programa **ASIR** (Administración de Sistemas Informáticos en Red) como demostración de:

- Desarrollo web con PHP
- Administración de infraestructura en AWS
- Automatización con PowerShell
- Buenas prácticas de versionado con Git
- Documentación profesional de proyectos

---

**Última actualización:** 2 de Febrero de 2026  
**Versión:** 1.0.0

---

> "Un sistema de tickets bien documentado es la base de una administración profesional" 🎯
