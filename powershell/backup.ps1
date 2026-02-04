# -----------------------------
# COMPROBAR DEPENDENCIAS
# -----------------------------
$Dependencias = @("gzip", "aws")

foreach ($dep in $Dependencias) {
    if (-not (Get-Command $dep -ErrorAction SilentlyContinue)) {
        Write-Host "✗ Error: '$dep' no está instalado o no se encuentra en el PATH." -ForegroundColor Red
        Log-Accion "Comprobación dependencias" "'$dep' no encontrado"
        exit 1
    } else {
        Write-Host "[✓] Dependencia encontrada: $dep" -ForegroundColor Green
    }
}

# -----------------------------
# PARÁMETROS INICIALES
# -----------------------------

# Solicitar ruta para backups
$BACKUP_FOLDER = Read-Host "Ingrese la ruta del directorio donde guardar los backups"

# Detectar separador de rutas según sistema operativo
$Sep = if ($IsWindows) { "\" } else { "/" }

# Obtener fecha actual
$FECHA = Get-Date -Format "yyyyMMdd_HHmmss"

# Archivos de backup y log
$LOG_FILE    = "$BACKUP_FOLDER${Sep}backups.log"
$ARCHIVO_SQL = "$BACKUP_FOLDER${Sep}backup_$FECHA.sql"
$ARCHIVO_ZIP = "$ARCHIVO_SQL.gz"

# Crear carpeta si no existe
if (-not (Test-Path $BACKUP_FOLDER)) {
    New-Item -ItemType Directory -Path $BACKUP_FOLDER | Out-Null
    Write-Host "[✓] Carpeta creada: $BACKUP_FOLDER" -ForegroundColor Green
} else {
    Write-Host "[✓] Carpeta existente: $BACKUP_FOLDER" -ForegroundColor Green
}

# Crear archivo de log si no existe
if (-not (Test-Path $LOG_FILE)) {
    New-Item -Path $LOG_FILE -ItemType File | Out-Null
    Write-Host "[✓] Archivo de log creado: $LOG_FILE" -ForegroundColor Green
} else {
    Write-Host "[✓] Archivo de log existente: $LOG_FILE" -ForegroundColor Green
}

# -----------------------------
# FUNCIONES
# -----------------------------
function Log-Accion {
    param(
        [string]$Accion,
        [string]$Resultado
    )
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $linea = "[$timestamp] - [$Accion] - [$Resultado]"
    Add-Content -Path $LOG_FILE -Value $linea
}

# -----------------------------
# CONFIGURACIÓN CREDENCIALES AWS
# -----------------------------
Write-Host "Paso inicial: Configuración de credenciales AWS"

$AWS_ACCESS_KEY_ID = Read-Host "Ingrese su AWS Access Key ID"
$AWS_SECRET_ACCESS_KEY = Read-Host "Ingrese su AWS Secret Access Key" -AsSecureString

# Convertir Secret Key a texto plano para AWS CLI
$AWS_SECRET_ACCESS_KEY = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto(
    [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($AWS_SECRET_ACCESS_KEY)
)

# Exportar variables de entorno
$env:AWS_ACCESS_KEY_ID     = $AWS_ACCESS_KEY_ID
$env:AWS_SECRET_ACCESS_KEY = $AWS_SECRET_ACCESS_KEY

Write-Host "[✓] Credenciales AWS configuradas correctamente" -ForegroundColor Green
Log-Accion "Configuración AWS" "Credenciales configuradas correctamente"

# -----------------------------
# DATOS RDS Y S3
# -----------------------------
$RDS_HOST     = "soporte(hay que cambiarlo).rds.amazonaws.com"
$RDS_USER     = "XXXX"
$RDS_PASSWORD = "XXXX"
$RDS_DATABASE = "XXXX"

$S3_BUCKET    = "hay que poner aqui el arn del s3"
$S3_REGION    = "us-east-1"

# -----------------------------
# PASO 1: HACER BACKUP DE LA BASE DE DATOS
# -----------------------------
Write-Host "`nBase de datos seleccionada: $RDS_DATABASE`n"
Write-Host "[1/3] Haciendo dump de la base de datos..."

mysqldump `
    --host=$RDS_HOST `
    --user=$RDS_USER `
    --password=$RDS_PASSWORD `
    --single-transaction `
    --routines `
    --events `
    --triggers `
    --result-file=$ARCHIVO_SQL `
    $RDS_DATABASE

if ($LASTEXITCODE -eq 0) {
    Write-Host "[✓] Dump creado: $ARCHIVO_SQL" -ForegroundColor Green
    Log-Accion "Dump de base de datos" "correcto"
} else {
    Write-Host "[✗] Error al hacer dump" -ForegroundColor Red
    Log-Accion "Dump de base de datos" "error"
    exit 1
}

# -----------------------------
# PASO 2: COMPRIMIR ARCHIVO
# -----------------------------
Write-Host "[2/3] Comprimiendo archivo..."
gzip $ARCHIVO_SQL

if ($LASTEXITCODE -eq 0) {
    Write-Host "[✓] Archivo comprimido: $ARCHIVO_ZIP" -ForegroundColor Green
    Log-Accion "Compresión" "correcto"
} else {
    Write-Host "[✗] Error al comprimir" -ForegroundColor Red
    Log-Accion "Compresión" "error"
    exit 1
}

# -----------------------------
# PASO 3: SUBIR A S3
# -----------------------------
Write-Host "[3/3] Subiendo a S3..."
$s3_path = "s3://$S3_BUCKET/backups/backup_$FECHA.sql.gz"
aws s3 cp $ARCHIVO_ZIP $s3_path --region $S3_REGION

if ($LASTEXITCODE -eq 0) {
    Write-Host "[✓] Backup subido a: $s3_path" -ForegroundColor Green
    Log-Accion "Subida a S3" "correcto"
} else {
    Write-Host "[✗] Error al subir a S3" -ForegroundColor Red
    Log-Accion "Subida a S3" "error"
    exit 1
}

# -----------------------------
# PASO 4: LIMPIAR ARCHIVOS LOCALES
# -----------------------------
Write-Host "`nLimpiando archivos temporales..."
Remove-Item $ARCHIVO_ZIP -Force

if (-not (Test-Path $ARCHIVO_ZIP)) {
    Write-Host "[✓] Archivo local eliminado" -ForegroundColor Green
    Log-Accion "Limpieza archivos locales" "correcto"
} else {
    Write-Host "[✗] Error al eliminar archivo local" -ForegroundColor Red
    Log-Accion "Limpieza archivos locales" "error"
}

# -----------------------------
# RESUMEN FINAL
# -----------------------------
$FECHA_FINAL = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
Write-Host "`n+=== BACKUP COMPLETADO EXITOSAMENTE (Equipo 7) ===+" -ForegroundColor Green
Write-Host "Archivo de backup: backup_$FECHA.sql.gz"
Write-Host "Ubicación S3: $s3_path"
Log-Accion "Creación del backup" "completado - $FECHA_FINAL"

Write-Host "`nArchivo de log: $LOG_FILE"
