

#Añadir lineas en fichero log.
function Log-Accion {
    param([string]$Accion, [string]$Resultado)
    $hora = Get-Date -Format "HH:mm:ss"
    Add-Content -Path $LOG_FILE -Value "$hora - [$Accion] - [$Resultado]"
}

#Añadir cabecera cada vez que se realicé un backup.
function Log-CabeceraDump {
    $fecha = Get-Date -Format "yyyy-MM-dd"
    Add-Content -Path $LOG_FILE -Value ""
    Add-Content -Path $LOG_FILE -Value "=================================================="
    Add-Content -Path $LOG_FILE -Value "Fichero : $ARCHIVO_SQL"
    Add-Content -Path $LOG_FILE -Value "Fecha de la copia de segu: $fecha"
    Add-Content -Path $LOG_FILE -Value "=================================================="
}

#Comprobación de dependencias.
$Dependencias = @("mysqldump", "aws")
foreach ($dep in $Dependencias) {
    if (-not (Get-Command $dep -ErrorAction SilentlyContinue)) { Write-Host "[ERROR] '$dep' no instalado o no en PATH" -ForegroundColor Red; Log-Accion "Dependencia $dep" "ERROR"; exit 1 }
    else { Write-Host "[OK] Dependencia encontrada: $dep" -ForegroundColor Green; Log-Accion "Dependencia $dep" "OK" }
}

# RUTAS ABSOLUTAS AWS
$USER_HOME       = [Environment]::GetFolderPath("UserProfile") 
$AWS_DIR         = Join-Path $USER_HOME ".aws"
$AWS_CREDENTIALS = Join-Path $AWS_DIR "credentials"

# Crear directorio y fichero si no existen
if (-not (Test-Path $AWS_DIR)) { New-Item -ItemType Directory -Path $AWS_DIR | Out-Null; Log-Accion "AWS" "Directorio .aws creado" }
else { Log-Accion "AWS Setup" "Directorio .aws existe" }

if (-not (Test-Path $AWS_CREDENTIALS)) { New-Item -ItemType File -Path $AWS_CREDENTIALS | Out-Null; Log-Accion "AWS" "Fichero credentials creado" }
else { Log-Accion "AWS Setup" "Fichero credentials existe" }


# COMPROBAR PERFIL DEFAULT AWS
$awsOk = $false
aws s3 ls --profile default 2>$null | Out-Null
if ($LASTEXITCODE -eq 0) { $awsOk = $true; Log-Accion "AWS Credentials" "Perfil default funcional"; Write-Host "[OK] Credenciales definidas." -ForegroundColor Green }
else { Log-Accion "AWS Credentials" "Perfil default inválido o no funciona" }


# PEDIR CREDENCIALES SOLO SI FALLA
if (-not $awsOk) {
    $AccessKey    = Read-Host "Introduce AWS Access Key"
    $SecretKey    = Read-Host "Introduce AWS Secret Key"
    $SessionToken = Read-Host "Introduce AWS Session Token (si aplica, si no dejar vacío)"

    $content = (Get-Content $AWS_CREDENTIALS -Raw) -replace "(?ms)^\[default\].*?(?=^\[|\z)", ""

    $newProfile = @"
[default]
aws_access_key_id = $AccessKey
aws_secret_access_key = $SecretKey
"@
    if ($SessionToken) { $newProfile += "aws_session_token = $SessionToken`n" }
    $newProfile += "region = us-east-1`n"

    $finalContent = if ($content) { "$content`n`n$newProfile" } else { $newProfile }
    Set-Content -Path $AWS_CREDENTIALS -Value $finalContent -Force
    Log-Accion "AWS Credentials" "Perfil default insertado/actualizado"

    aws s3 ls --profile default 2>$null | Out-Null
    if ($LASTEXITCODE -ne 0) { Write-Host "[ERROR] Credenciales AWS inválidas" -ForegroundColor Red; exit 1 }
    else { Write-Host "[OK] Credenciales AWS válidas" -ForegroundColor Green }
}


# Parámetros iniciales
Write-Host "`n--Parámetros iniciales--"
$userInput = Read-Host "Ingrese la ruta relativa o absoluta para guardar backups" #Ruta absoluta o relativa de directorio a guardar los backups.

# Convertir a ruta absoluta
try { $BACKUP_FOLDER = Convert-Path $userInput -ErrorAction Stop } catch { $BACKUP_FOLDER = Join-Path (Get-Location) $userInput }

$FECHA        = Get-Date -Format "yyyyMMdd_HHmmss"
$LOG_FILE     = Join-Path $BACKUP_FOLDER "backups.log"
$GLOBAL:ARCHIVO_SQL = Join-Path $BACKUP_FOLDER "backup_$FECHA.sql"
$ARCHIVO_ZIP  = "$ARCHIVO_SQL.zip"

# Crear directorio y log si no existen
if (-not (Test-Path $BACKUP_FOLDER)) { New-Item -ItemType Directory -Path $BACKUP_FOLDER | Out-Null; Log-Accion "Crear directorio backup" "OK - $BACKUP_FOLDER" }
if (-not (Test-Path $LOG_FILE)) { New-Item -Path $LOG_FILE -ItemType File | Out-Null }


Log-CabeceraDump #Comienza el proceso de backup. Añadimos cabecerá para diferenciar entre los diferentes posibles backups.
Log-Accion "Inicio script" "Backup iniciado" 

# DATOS RDS Y S3
$RDS_HOST     = "soporte.cxxxxx.us-east-1.rds.amazonaws.com"
$RDS_USER     = "xxx"
$RDS_PASSWORD = "xxx"
$RDS_DATABASE = "xxx"
$S3_BUCKET    = "backupwordpresssoporte"
$S3_REGION    = "us-east-1"

# PASO 1: BACKUP BD
Write-Host "`n[1/3] Haciendo dump..."
mysqldump --host=$RDS_HOST --user=$RDS_USER --password=$RDS_PASSWORD --routines --events --triggers --result-file=$ARCHIVO_SQL $RDS_DATABASE 2>$null

if ($LASTEXITCODE -eq 0) { Write-Host "[OK] Dump creado: $ARCHIVO_SQL" -ForegroundColor Green; Log-Accion "Dump BD" "OK - $ARCHIVO_SQL" }
else { Write-Host "[ERROR] Error al hacer dump" -ForegroundColor Red; Log-Accion "Dump BD" "ERROR"; exit 1 }

# PASO 2: COMPRIMIR
Write-Host "`n[2/3] Comprimiendo archivo..."
Compress-Archive -Path $ARCHIVO_SQL -DestinationPath $ARCHIVO_ZIP -Force
if (Test-Path $ARCHIVO_ZIP) { Write-Host "[OK] Archivo comprimido: $ARCHIVO_ZIP" -ForegroundColor Green; Log-Accion "Compresión" "OK - $ARCHIVO_ZIP" }
else { Write-Host "[ERROR] Error al compimir" -ForegroundColor Red; Log-Accion "Compresión" "ERROR"; exit 1 }

# PASO 3: SUBIR A S3
Write-Host "`n[3/3] Subiendo a S3..."
$s3_path = "s3://$S3_BUCKET/backups/backup_$FECHA.sql.zip"
aws s3 cp $ARCHIVO_ZIP $s3_path --region $S3_REGION >$null 2>&1
if ($LASTEXITCODE -eq 0) { Write-Host "[OK] Backup subido a: $s3_path" -ForegroundColor Green; Log-Accion "Subida S3" "OK" }
else { Write-Host "[ERROR] Error al subir a S3" -ForegroundColor Red; Log-Accion "Subida S3" "ERROR"; exit 1 }

# PASO 4: LIMPIAR LOCALES
Remove-Item $ARCHIVO_ZIP -Force
if (-not (Test-Path $ARCHIVO_ZIP)) { Write-Host "[OK] Archivo local eliminado" -ForegroundColor Green; Log-Accion "Limpieza local" "OK" }
else { Write-Host "[ERROR] No se pudo eliminar archivo local" -ForegroundColor Red; Log-Accion "Limpieza local" "ERROR" }

Log-Accion "Fin script" "Backup completado correctamente"
Write-Host "`nBackup completado. Archivo: backup_$FECHA.sql.zip en $s3_path"

 