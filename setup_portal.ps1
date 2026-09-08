# ============================================================
# WiFiPads Captive Portal Platform - Automated Setup Script
# Compatible with Windows 10/11 + PowerShell 5.1+
# ============================================================

param(
    [switch]$NonInteractive,
    [switch]$SkipMigration
)

$ErrorActionPreference = "Stop"
$ProjectDir = $PSScriptRoot
$TempDir = Join-Path $env:TEMP "captive-setup"

function Write-Header {
    param([string]$msg)
    Write-Host ""
    Write-Host "==========================================" -ForegroundColor Cyan
    Write-Host "  $msg" -ForegroundColor Cyan
    Write-Host "==========================================" -ForegroundColor Cyan
}

function Write-Step {
    param([string]$msg)
    Write-Host "[+] $msg" -ForegroundColor Green
}

function Write-Warning2 {
    param([string]$msg)
    Write-Host "[!] $msg" -ForegroundColor Yellow
}

function Write-Err {
    param([string]$msg)
    Write-Host "[X] $msg" -ForegroundColor Red
}

# ---- Check/Install PHP ----
Write-Header "STEP 1: PHP Installation Check"

$phpPath = $null
$phpCandidates = @(
    "php",
    "C:\php\php.exe",
    "C:\laragon\bin\php\php-8.2*\php.exe",
    "C:\xampp\php\php.exe",
    "C:\wamp64\bin\php\php*\php.exe"
)

foreach ($candidate in $phpCandidates) {
    try {
        $found = Get-Command $candidate -ErrorAction SilentlyContinue
        if ($found) { $phpPath = $found.Source; break }
    } catch {}
    
    $resolved = Resolve-Path $candidate -ErrorAction SilentlyContinue
    if ($resolved) {
        $phpPath = $resolved.Path
        break
    }
}

if (-not $phpPath) {
    Write-Warning2 "PHP not found. Downloading PHP 8.3 (NTS x64)..."
    New-Item -ItemType Directory -Path "C:\php" -Force | Out-Null
    New-Item -ItemType Directory -Path $TempDir -Force | Out-Null

    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    $releasesUrl = "https://windows.php.net/downloads/releases/"
    $matchedZip = "php-8.3.33-nts-Win32-vs16-x64.zip"

    try {
        $html = (Invoke-WebRequest -Uri $releasesUrl -UseBasicParsing -TimeoutSec 15).Content
        $pattern = 'php-8\.3\.\d+-nts-Win32-vs16-x64\.zip'
        $regexMatch = [regex]::Match($html, $pattern)
        if ($regexMatch.Success) {
            $matchedZip = $regexMatch.Value
        }
    } catch {
        Write-Warning2 "Could not fetch releases list dynamically, using fallback package: $matchedZip"
    }

    $phpZip = Join-Path $TempDir $matchedZip
    $phpUrl = "$releasesUrl$matchedZip"

    Write-Step "Downloading PHP from $phpUrl"
    Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip -UseBasicParsing

    Write-Step "Extracting PHP to C:\php"
    Expand-Archive -Path $phpZip -DestinationPath "C:\php" -Force

    # Copy and configure php.ini
    $phpIniSample = "C:\php\php.ini-development"
    $phpIni = "C:\php\php.ini"
    if (Test-Path $phpIniSample) {
        Copy-Item $phpIniSample $phpIni -Force
        
        $iniContent = Get-Content $phpIni -Raw
        # Enable extension_dir
        $iniContent = $iniContent -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
        $iniContent = $iniContent -replace ';extension_dir = "ext"', 'extension_dir = "C:\php\ext"'
        
        # Enable required extensions
        $extensionsToEnable = @(
            'pdo_pgsql', 'pgsql', 'pdo_sqlite', 'sqlite3', 'sockets',
            'mbstring', 'openssl', 'tokenizer', 'xml',
            'curl', 'fileinfo', 'gd', 'zip', 'intl'
        )
        foreach ($ext in $extensionsToEnable) {
            $iniContent = $iniContent -replace ";extension=$ext", "extension=$ext"
        }
        
        Set-Content -Path $phpIni -Value $iniContent
        Write-Step "php.ini configured with required extensions and extension_dir"
    }

    $phpPath = "C:\php\php.exe"
    $env:PATH = "C:\php;" + $env:PATH
    Write-Step "PHP installed at C:\php\php.exe"
} else {
    Write-Step "PHP found at: $phpPath"
    $phpDir = Split-Path $phpPath -Parent
    $env:PATH = "$phpDir;" + $env:PATH
}

# Verify PHP version
$phpVersion = & $phpPath --version 2>&1 | Select-Object -First 1
Write-Step "PHP Version: $phpVersion"

# ---- Check/Install Composer ----
Write-Header "STEP 2: Composer Installation Check"

$composerPath = $null
$foundComposer = Get-Command composer -ErrorAction SilentlyContinue
if ($foundComposer) {
    $composerPath = $foundComposer.Source
} else {
    $foundComposerBat = Get-Command composer.bat -ErrorAction SilentlyContinue
    if ($foundComposerBat) {
        $composerPath = $foundComposerBat.Source
    }
}

if (-not $composerPath) {
    Write-Warning2 "Composer not found. Downloading Composer executable..."
    New-Item -ItemType Directory -Path $TempDir -Force | Out-Null
    
    $composerDest = Join-Path $ProjectDir "composer.phar"
    
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri "https://getcomposer.org/composer-stable.phar" -OutFile $composerDest -UseBasicParsing
    
    Write-Step "Composer downloaded to: $composerDest"
    
    # Create wrapper batch file
    $composerBat = Join-Path $ProjectDir "composer.bat"
    $batContent = "@echo off`r`n`"$phpPath`" `"%~dp0composer.phar`" %*"
    Set-Content -Path $composerBat -Value $batContent
    
    $composerPath = $composerBat
    Write-Step "Composer wrapper created at: $composerBat"
} else {
    Write-Step "Composer found at: $composerPath"
}

# ---- Initialize Laravel Project ----
Write-Header "STEP 3: Laravel 11 Project Initialization"

$artisanPath = Join-Path $ProjectDir "artisan"
if (-not (Test-Path $artisanPath)) {
    Write-Step "Creating Laravel project scaffold in temp directory..."
    $tempLaravel = Join-Path $env:TEMP "laravel-scaffold"
    
    if (Test-Path $tempLaravel) {
        Remove-Item $tempLaravel -Recurse -Force
    }
    
    & $phpPath $composerPath create-project laravel/laravel $tempLaravel --prefer-dist --no-interaction 2>&1
    
    Write-Step "Copying Laravel scaffold to project directory..."
    
    # Copy only files that don't already exist in the project
    $excludePatterns = @('app', 'config', 'database', 'resources', 'routes')
    
    Get-ChildItem $tempLaravel | Where-Object {
        $_.Name -notin $excludePatterns
    } | ForEach-Object {
        $dest = Join-Path $ProjectDir $_.Name
        if (-not (Test-Path $dest)) {
            Copy-Item $_.FullName $dest -Recurse -Force
            Write-Host "   Copied: $($_.Name)" -ForegroundColor Gray
        }
    }
    
    # Copy bootstrap, public, storage, tests directories
    foreach ($dir in @('bootstrap', 'public', 'storage', 'tests')) {
        $src = Join-Path $tempLaravel $dir
        $dst = Join-Path $ProjectDir $dir
        if ((Test-Path $src) -and -not (Test-Path $dst)) {
            Copy-Item $src $dst -Recurse -Force
            Write-Host "   Copied dir: $dir" -ForegroundColor Gray
        }
    }
    
    # Copy Laravel-specific config files that we haven't overwritten
    $srcConfig = Join-Path $tempLaravel "config"
    $dstConfig = Join-Path $ProjectDir "config"
    Get-ChildItem $srcConfig -File | Where-Object { -not (Test-Path (Join-Path $dstConfig $_.Name)) } | ForEach-Object {
        Copy-Item $_.FullName (Join-Path $dstConfig $_.Name) -Force
    }
    
    Write-Step "Laravel scaffold installed."
} else {
    Write-Step "Laravel scaffold already exists (artisan found)."
}

# ---- Install Composer Dependencies ----
Write-Header "STEP 4: Installing Composer Dependencies"

Set-Location $ProjectDir
& $phpPath $composerPath install --no-interaction 2>&1
Write-Step "Composer dependencies installed."

# ---- Configure .env ----
Write-Header "STEP 5: Environment Configuration"

$envFile = Join-Path $ProjectDir ".env"
$envExample = Join-Path $ProjectDir ".env.example"

if (-not (Test-Path $envFile) -and (Test-Path $envExample)) {
    Copy-Item $envExample $envFile
    Write-Step ".env created from .env.example"
}

# Generate app key
if (Test-Path (Join-Path $ProjectDir "artisan")) {
    & $phpPath (Join-Path $ProjectDir "artisan") key:generate 2>&1
    Write-Step "Application key generated."
}

# ---- Database Setup ----
Write-Header "STEP 6: Database Migration & Seeding"

Write-Warning2 "Make sure PostgreSQL is running and you've configured .env with your DB credentials!"
Write-Warning2 "DB_CONNECTION=pgsql, DB_DATABASE=captive_portal, DB_USERNAME=postgres, DB_PASSWORD=..."

if (-not $SkipMigration) {
    $proceed = "n"
    if ($NonInteractive) {
        $proceed = "y"
    } else {
        $proceed = Read-Host "Proceed with migration? (y/n)"
    }

    if ($proceed -eq 'y' -or $proceed -eq 'Y') {
        try {
            & $phpPath (Join-Path $ProjectDir "artisan") migrate --seed --force 2>&1
            Write-Step "Database migrated and seeded."
        } catch {
            Write-Warning2 "Migration failed: $_. Please verify database connection in .env and run 'php artisan migrate --seed' manually."
        }
    } else {
        Write-Warning2 "Skipping migration. Run manually: php artisan migrate --seed"
    }
} else {
    Write-Step "Migration skipped as requested."
}

# ---- Storage Link ----
Write-Header "STEP 7: Storage Setup"
if (Test-Path (Join-Path $ProjectDir "artisan")) {
    & $phpPath (Join-Path $ProjectDir "artisan") storage:link 2>&1
}

# ---- Summary ----
Write-Header "SETUP COMPLETE!"
Write-Host ""
Write-Host "  Project Directory : $ProjectDir" -ForegroundColor White
Write-Host "  Admin Login       : http://localhost:8000/admin" -ForegroundColor White
Write-Host "  Captive Portal    : http://localhost:8000/portal?mac=AA:BB:CC:DD:EE:FF&ip=192.168.1.100&location=default-location" -ForegroundColor White
Write-Host ""
Write-Host "  Default Credentials:" -ForegroundColor White
Write-Host "    Superadmin : admin@wifipads.com / password123" -ForegroundColor Green
Write-Host "    Advertiser : sponsor@wifipads.com / password123" -ForegroundColor Green
Write-Host ""
Write-Host "  To start development server:" -ForegroundColor White
Write-Host "    php artisan serve" -ForegroundColor Yellow
Write-Host ""
Write-Host "  Next steps if RouterOS integration needed:" -ForegroundColor White
Write-Host "    1. Add router credentials in Admin > Locations" -ForegroundColor Gray
Write-Host "    2. Click 'Test Connection' to verify" -ForegroundColor Gray
Write-Host "    3. Configure MikroTik hotspot to redirect to this server" -ForegroundColor Gray
Write-Host ""
