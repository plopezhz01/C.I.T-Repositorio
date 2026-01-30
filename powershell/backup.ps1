################################################################################
#Version alfa script powershell - c.i.t grupo7
################################################################################

# Datos de tu RDS
$RDS_HOST = "soporte(hay que cambiarlo).rds.amazonaws.com"
$RDS_USER = "root"
$RDS_PASSWORD = "Gruposiete" #Hay que hasearlo por seguridad
$RDS_DATABASE = "soporte"

# Datos de tu S3
$S3_BUCKET = "hay que poner aqui el arn del s3"
$S3_REGION = "us-east-1"

# Carpeta donde guardar backups
$BACKUP_FOLDER = "/backups" #La carpeta tendra que tener acceso restringido

# Crear carpeta si no existe
if (-not (Test-Path $BACKUP_FOLDER)) {
    New-Item -ItemType Directory -Path $BACKUP_FOLDER | Out-Null
}

# Nombres de archivos
$FECHA = Get-Date -Format "yyyyMMdd_HHmmss"
$ARCHIVO_SQL = "$BACKUP_FOLDER\backup_$FECHA.sql"
$ARCHIVO_ZIP = "$ARCHIVO_SQL.gz"

################################################################################
# PASO 2: HACER BACKUP DE LA BASE DE DATOS
################################################################################

Write-Host "Base de datos: $RDS_DATABASE"
Write-Host "Fecha: $FECHA"
Write-Host ""

Write-Host "[1/3] Haciendo dump de la base de datos..."

# Comando mysqldump
mysqldump `
    --host=$RDS_HOST `
    --user=$RDS_USER `
    --password=$RDS_PASSWORD `
    --single-transaction `
    --result-file=$ARCHIVO_SQL `
    $RDS_DATABASE

# Verificar si el dump fue exitoso
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Dump creado: $ARCHIVO_SQL" -ForegroundColor Green
} else {
    Write-Host "✗ Error al hacer dump" -ForegroundColor Red
    exit 1
}

################################################################################
# PASO 3: COMPRIMIR EL ARCHIVO
################################################################################

Write-Host "[2/3] Comprimiendo archivo..."

# Comprimir con gzip (debes tener gzip instalado)
gzip $ARCHIVO_SQL



################################################################################
# PASO 4: SUBIR A S3
################################################################################

Write-Host "[3/3] Subiendo a S3..."

# Comando AWS S3
$s3_path = "s3://$S3_BUCKET/backups/backup_$FECHA.sql.gz"
aws s3 cp $ARCHIVO_ZIP $s3_path --region $S3_REGION

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Backup subido a: $s3_path" -ForegroundColor Green
} else {
    Write-Host "✗ Error al subir a S3" -ForegroundColor Red
    exit 1
}

################################################################################
# PASO 5: LIMPIAR ARCHIVOS LOCALES (OPCIONAL)
################################################################################

Write-Host ""
Write-Host "Limpiando archivos temporales..."
Remove-Item $ARCHIVO_ZIP -Force
Write-Host "✓ Archivo local eliminado" -ForegroundColor Green

################################################################################
# RESUMEN FINAL
################################################################################

Write-Host ""
Write-Host "=== BACKUP COMPLETADO EXITOSAMENTE ===" -ForegroundColor Green
Write-Host "Archivo de backup: backup_$FECHA.sql.gz"
Write-Host "Ubicación S3: $s3_path"
#

