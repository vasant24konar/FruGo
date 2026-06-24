#Requires -Version 5.1
# deploy.ps1 — Windows PowerShell bootstrap for Product Manager
# Usage: .\deploy.ps1
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Write-Info  { param($msg) Write-Host "[INFO]  $msg" -ForegroundColor Green }
function Write-Warn  { param($msg) Write-Host "[WARN]  $msg" -ForegroundColor Yellow }
function Write-Err   { param($msg) Write-Host "[ERROR] $msg" -ForegroundColor Red; exit 1 }

###############################################################################
# Prerequisite checks
###############################################################################
Write-Info "Checking prerequisites..."

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Err "Docker is not installed. Download Docker Desktop from https://www.docker.com/products/docker-desktop/"
}

# Verify the Docker daemon is actually running (not just installed)
Write-Info "Verifying Docker daemon is running..."
$dockerInfo = & docker info 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Warn "Docker Desktop is installed but the engine is not running."
    Write-Host "  -> Open Docker Desktop from the Start menu and wait for 'Engine running' at the bottom." -ForegroundColor Cyan
    Write-Host "  -> Then re-run: .\deploy.ps1" -ForegroundColor Cyan
    exit 1
}
Write-Info "Docker daemon is running."

$composeCmd = $null
if (Get-Command "docker" -ErrorAction SilentlyContinue) {
    $testCompose = & docker compose version 2>$null
    if ($LASTEXITCODE -eq 0) { $composeCmd = @("docker", "compose") }
}
if (-not $composeCmd) {
    if (Get-Command docker-compose -ErrorAction SilentlyContinue) {
        $composeCmd = @("docker-compose")
    } else {
        Write-Err "Docker Compose is not available. Ensure Docker Desktop is installed."
    }
}

Write-Info "Using compose command: $($composeCmd -join ' ')"

###############################################################################
# Environment configuration
###############################################################################
if (-not (Test-Path ".env")) {
    Write-Info "Creating .env from .env.example..."
    Copy-Item ".env.example" ".env"
    Write-Warn "Review .env and update credentials before production use."
}

###############################################################################
# Pre-pull images sequentially (prevents concurrent write contention on WSL2)
###############################################################################
$imagesToPull = @("php:8.2-fpm", "composer:2", "mysql:8.0", "nginx:1.25-alpine")
foreach ($img in $imagesToPull) {
    Write-Info "Pulling $img ..."
    & docker pull $img
    if ($LASTEXITCODE -ne 0) { Write-Err "Failed to pull $img." }
}

###############################################################################
# Build & start containers
###############################################################################
Write-Info "Building and starting containers..."
& $composeCmd[0] ($composeCmd[1..($composeCmd.Length-1)] + @("up", "-d", "--build"))
if ($LASTEXITCODE -ne 0) { Write-Err "Failed to start containers." }

###############################################################################
# Wait for MySQL to be healthy
###############################################################################
Write-Info "Waiting for database to be ready..."
$maxAttempts = 30
$attempt = 0
do {
    Start-Sleep -Seconds 2
    $attempt++
    $result = & $composeCmd[0] ($composeCmd[1..($composeCmd.Length-1)] + @("exec", "-T", "db", "mysqladmin", "ping", "-h", "localhost", "--silent")) 2>$null
    if ($LASTEXITCODE -eq 0) { break }
    Write-Host "." -NoNewline
    if ($attempt -ge $maxAttempts) { Write-Err "`nDatabase did not become ready in time." }
} while ($true)
Write-Host ""
Write-Info "Database is ready."

###############################################################################
# Application setup
###############################################################################
function Invoke-App {
    param([string[]]$artisanArgs)
    & $composeCmd[0] ($composeCmd[1..($composeCmd.Length-1)] + @("exec", "-T", "app") + $artisanArgs)
    if ($LASTEXITCODE -ne 0) { Write-Err "Command failed: $($artisanArgs -join ' ')" }
}

Write-Info "Installing Composer dependencies..."
Invoke-App @("composer", "install", "--no-dev", "--optimize-autoloader", "--no-interaction")

Write-Info "Generating application key..."
Invoke-App @("php", "artisan", "key:generate", "--force")

Write-Info "Running database migrations..."
Invoke-App @("php", "artisan", "migrate", "--force")

Write-Info "Seeding database..."
Invoke-App @("php", "artisan", "db:seed", "--force")

Write-Info "Caching configuration and routes..."
Invoke-App @("php", "artisan", "config:cache")
Invoke-App @("php", "artisan", "route:cache")
Invoke-App @("php", "artisan", "view:cache")

Write-Info "Setting storage permissions..."
Invoke-App @("chown", "-R", "www-data:www-data", "/var/www/storage", "/var/www/bootstrap/cache")

###############################################################################
# Done
###############################################################################
Write-Host ""
Write-Info "Deployment complete."
Write-Info "Application: http://localhost:8080"
Write-Info "Admin:  admin@example.com / Admin@1234"
Write-Info "User:   user@example.com  / User@1234!"
