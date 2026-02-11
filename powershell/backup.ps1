$BACKUP_FOLDER = "C:\Backups"
$LOG_FILE      = Join-Path $BACKUP_FOLDER "backups.log"
$FECHA         = Get-Date -Format "yyyyMMdd_HHmmss"
$ARCHIVO_SQL   = Join-Path $BACKUP_FOLDER "backup_$FECHA.sql"
$ARCHIVO_ZIP   = "$ARCHIVO_SQL.zip"

$RDS_HOST     = "soporte.cpick8cz8rta.us-east-1.rds.amazonaws.com"
$RDS_USER     = "root"
$RDS_DATABASE = "soporte"
$RDS_PASSWORD = "Gruposiete"

$S3_BUCKET = "backupwordpresssoporte"
$S3_REGION = "us-east-1"
$S3_PATH   = "s3://$S3_BUCKET/backups/backup_$FECHA.sql.zip"

function Log-Accion {
    param([string]$Accion, [string]$Resultado)
    $hora = Get-Date -Format "HH:mm:ss"
    Add-Content -Path $LOG_FILE -Value "$hora - [$Accion] - [$Resultado]"
}

function Log-Cabecera {
    Add-Content -Path $LOG_FILE -Value ""
    Add-Content -Path $LOG_FILE -Value "=================================================="
    Add-Content -Path $LOG_FILE -Value "Backup BD MySQL"
    Add-Content -Path $LOG_FILE -Value "Fecha: $(Get-Date)"
    Add-Content -Path $LOG_FILE -Value "=================================================="
}

if (-not (Test-Path $BACKUP_FOLDER)) {
    New-Item -ItemType Directory -Path $BACKUP_FOLDER | Out-Null
}

if (-not (Test-Path $LOG_FILE)) {
    New-Item -ItemType File -Path $LOG_FILE | Out-Null
}

Log-Cabecera
Log-Accion "Inicio" "Script iniciado"

$Dependencias = @("mysqldump", "aws")

foreach ($dep in $Dependencias) {
    if (-not (Get-Command $dep -ErrorAction SilentlyContinue)) {
        Log-Accion "Dependencia $dep" "ERROR"
        exit 1
    }
    Log-Accion "Dependencia $dep" "OK"
}

mysqldump `
    --host=$RDS_HOST `
    --user=$RDS_USER `
    --password=$RDS_PASSWORD `
    --routines --events --triggers `
    --result-file=$ARCHIVO_SQL `
    $RDS_DATABASE 2>$null

if ($LASTEXITCODE -ne 0) {
    Log-Accion "Dump BD" "ERROR"
    exit 1
}
Log-Accion "Dump BD" "OK - $ARCHIVO_SQL"

Compress-Archive -Path $ARCHIVO_SQL -DestinationPath $ARCHIVO_ZIP -Force 2>$null

if (-not (Test-Path $ARCHIVO_ZIP)) {
    Log-Accion "Compresión" "ERROR"
    exit 1
}
Log-Accion "Compresión" "OK - $ARCHIVO_ZIP"

aws s3 cp $ARCHIVO_ZIP $S3_PATH --region $S3_REGION --only-show-errors *> $null

if ($LASTEXITCODE -ne 0) {
    Log-Accion "Subida S3" "ERROR"
    exit 1
}
Log-Accion "Subida S3" "OK - $S3_PATH"

Remove-Item $ARCHIVO_ZIP -Force 2>$null
Log-Accion "Limpieza local" "OK"

Log-Accion "Fin" "Backup completado correctamente"

 
