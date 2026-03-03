Param(
    [string]$OutputPath = "docs/monitoring/STATUS_OTOMATIS.md",
    [switch]$RunTests
)

$ErrorActionPreference = "Stop"

$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

function Get-SafeOutput {
    Param(
        [scriptblock]$Script
    )

    try {
        return (& $Script | Out-String).Trim()
    } catch {
        return "Gagal mengambil data: $($_.Exception.Message)"
    }
}

$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
$branch = Get-SafeOutput { git rev-parse --abbrev-ref HEAD 2>$null }

$porcelain = @(git status --porcelain 2>$null)
$changed = $porcelain.Count
$added = ($porcelain | Where-Object { $_ -match "^\s*A|^\?\?" }).Count
$modified = ($porcelain | Where-Object { $_ -match "^\s*M|^M\s|^MM" }).Count
$deleted = ($porcelain | Where-Object { $_ -match "^\s*D|^D\s" }).Count
$untracked = ($porcelain | Where-Object { $_ -match "^\?\?" }).Count

$migrateStatus = Get-SafeOutput { php artisan migrate:status --no-ansi }
$dashboardRoutes = Get-SafeOutput { php artisan route:list --name=dashboard --no-ansi }
$testOutput = "Tidak dijalankan. Gunakan parameter -RunTests untuk menjalankan test."

if ($RunTests) {
    $testOutput = Get-SafeOutput { php artisan test --no-ansi }
}

$content = @"
# Status Otomatis Proyek

Terakhir diperbarui: $timestamp
Branch aktif: $branch

## Ringkasan Git
- Total file berubah: $changed
- Added: $added
- Modified: $modified
- Deleted: $deleted
- Untracked: $untracked

## Status Migrasi
~~~text
$migrateStatus
~~~

## Route Dashboard
~~~text
$dashboardRoutes
~~~

## Hasil Test
~~~text
$testOutput
~~~
"@

$outDir = Split-Path -Parent $OutputPath
if (-not (Test-Path $outDir)) {
    New-Item -ItemType Directory -Path $outDir -Force | Out-Null
}

Set-Content -Path $OutputPath -Value $content -Encoding UTF8
Write-Output "Status otomatis diperbarui: $OutputPath"
