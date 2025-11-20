[CmdletBinding()]
param(
    [switch]$SkipMigrate,
    [switch]$SkipFrontendBuild
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot

function Assert-Tool {
    param(
        [Parameter(Mandatory = $true)][string]$Name,
        [Parameter(Mandatory = $true)][string]$InstallHint
    )

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "$Name is required. $InstallHint"
    }
}

Push-Location $repoRoot
try {
    Write-Host "=== Simple WP Site Manager: Windows setup ==="

    Assert-Tool -Name 'php' -InstallHint 'Install PHP 8.2+ and ensure it is on PATH.'
    Assert-Tool -Name 'composer' -InstallHint 'Install Composer from https://getcomposer.org/download/ and ensure it is on PATH.'
    Assert-Tool -Name 'npm' -InstallHint 'Install Node.js 20+ from https://nodejs.org/en/download/.'

    Write-Host "Checking PHP SQLite extensions..."
    php -r "foreach (['sqlite3','pdo_sqlite'] as $ext) { if (!extension_loaded($ext)) { fwrite(STDERR, \"$ext extension is required.\" . PHP_EOL); exit(1); }}"

    if (-not (Test-Path '.env')) {
        Write-Host "Creating .env from .env.example"
        Copy-Item '.env.example' '.env'
    }

    if (-not (Test-Path 'database\database.sqlite')) {
        Write-Host "Creating database\\database.sqlite"
        New-Item -ItemType File 'database\database.sqlite' | Out-Null
    }

    Write-Host "Installing PHP dependencies..."
    composer install --no-interaction --ansi

    Write-Host "Generating app key..."
    php artisan key:generate --force --ansi

    if (-not $SkipMigrate) {
        Write-Host "Running migrations and seeders..."
        php artisan migrate --seed --force --ansi
    } else {
        Write-Host "Skipping migrations (--SkipMigrate set)."
    }

    Write-Host "Installing Node dependencies..."
    npm install

    if (-not $SkipFrontendBuild) {
        Write-Host "Building frontend assets..."
        npm run build
    } else {
        Write-Host "Skipping frontend build (--SkipFrontendBuild set)."
    }

    Write-Host "Windows setup complete."
}
finally {
    Pop-Location
}
