param(
    [switch]$Strict
)

$ErrorActionPreference = 'Stop'

$root = (Get-Item -Path (Join-Path $PSScriptRoot '..\\..')).FullName
Set-Location $root

$mode = if ($Strict) { 'strict' } else { 'tracked' }

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host 'ERROR: php is required to build Finella UI docs.'
    exit 1
}
php scripts\\docs\\build-ui-docs.php
if ($LASTEXITCODE -ne 0) {
    Write-Host 'ERROR: failed to build Finella UI docs.'
    exit $LASTEXITCODE
}

$gitAvailable = $false
try {
    $null = git rev-parse --is-inside-work-tree 2>$null
    if ($LASTEXITCODE -eq 0) { $gitAvailable = $true }
} catch {}

$allowlist = @()

function Normalize-Path([string]$path) {
    $clean = $path
    if ($clean.StartsWith($root)) {
        $clean = $clean.Substring($root.Length)
    }
    $clean = $clean.TrimStart('\', '/')
    return $clean
}

function Is-Allowed([string]$path) {
    foreach ($allowed in $allowlist) {
        if ($path.StartsWith($allowed)) { return $true }
    }
    return $false
}

function Is-Tracked([string]$path) {
    if (-not $gitAvailable) { return $false }
    $output = git ls-files -z -- $path 2>$null
    return ($LASTEXITCODE -eq 0 -and [string]::IsNullOrEmpty($output) -eq $false)
}

$presentDirs = New-Object System.Collections.Generic.List[string]
$trackedDirs = New-Object System.Collections.Generic.List[string]
$presentFiles = New-Object System.Collections.Generic.List[string]
$trackedFiles = New-Object System.Collections.Generic.List[string]

function Collect-Dirs([string]$name) {
    Get-ChildItem -Recurse -Directory -Filter $name -ErrorAction SilentlyContinue | ForEach-Object {
        $clean = Normalize-Path $_.FullName
        if (Is-Allowed $clean) { return }
        $presentDirs.Add($clean)
        if (Is-Tracked $clean) { $trackedDirs.Add($clean) }
    }
}

function Collect-Files-Name([string]$name) {
    Get-ChildItem -Recurse -File -Filter $name -ErrorAction SilentlyContinue | ForEach-Object {
        $clean = Normalize-Path $_.FullName
        if (Is-Allowed $clean) { return }
        $presentFiles.Add($clean)
        if (Is-Tracked $clean) { $trackedFiles.Add($clean) }
    }
}

function Collect-Files-Path([string]$pattern) {
    Get-ChildItem -Recurse -File -ErrorAction SilentlyContinue | Where-Object { $_.FullName -like $pattern } | ForEach-Object {
        $clean = Normalize-Path $_.FullName
        if (Is-Allowed $clean) { return }
        $presentFiles.Add($clean)
        if (Is-Tracked $clean) { $trackedFiles.Add($clean) }
    }
}

Collect-Dirs 'vendor'
Collect-Dirs '.composer-cache'
Collect-Dirs '.composer-home*'
Collect-Dirs '.phpunit.cache'
Collect-Dirs 'coverage'
Collect-Dirs '.idea'
Collect-Dirs '.vscode'

Collect-Files-Path '*\storage\sessions\*'
Collect-Files-Path '*\storage\logs\*'
Collect-Files-Path '*\storage\cache\*'
Collect-Files-Name '*.log'
Collect-Files-Name 'tmp-*.zip~'
Collect-Files-Name '.phpunit.result.cache'
Collect-Files-Name '.DS_Store'
Collect-Files-Name 'Thumbs.db'

$presentDirs = $presentDirs | Sort-Object -Unique
$trackedDirs = $trackedDirs | Sort-Object -Unique
$presentFiles = $presentFiles | Sort-Object -Unique
$trackedFiles = $trackedFiles | Sort-Object -Unique

Write-Host "Release hygiene check ($mode mode)"
if ($trackedDirs.Count -gt 0 -or $trackedFiles.Count -gt 0) {
    Write-Host 'TRACKED DIRS:'
    if ($trackedDirs.Count -gt 0) { $trackedDirs | ForEach-Object { Write-Host " - $_" } } else { Write-Host ' - (none)' }
    Write-Host 'TRACKED FILES:'
    if ($trackedFiles.Count -gt 0) { $trackedFiles | ForEach-Object { Write-Host " - $_" } } else { Write-Host ' - (none)' }
} else {
    Write-Host 'TRACKED DIRS: (none)'
    Write-Host 'TRACKED FILES: (none)'
}

if ($presentDirs.Count -gt 0 -or $presentFiles.Count -gt 0) {
    Write-Host 'PRESENT DIRS:'
    if ($presentDirs.Count -gt 0) { $presentDirs | ForEach-Object { Write-Host " - $_" } } else { Write-Host ' - (none)' }
    Write-Host 'PRESENT FILES:'
    if ($presentFiles.Count -gt 0) { $presentFiles | ForEach-Object { Write-Host " - $_" } } else { Write-Host ' - (none)' }
} else {
    Write-Host 'PRESENT DIRS: (none)'
    Write-Host 'PRESENT FILES: (none)'
}

$fail = $false
if ($mode -eq 'strict') {
    if ($presentDirs.Count -gt 0 -or $presentFiles.Count -gt 0) { $fail = $true }
} else {
    if ($trackedDirs.Count -gt 0 -or $trackedFiles.Count -gt 0) { $fail = $true }
}

if ($fail) {
    Write-Host 'Release hygiene check failed.'
    Write-Host 'Remove the listed paths above (vendor/, Composer caches, storage/{sessions,logs,cache}, tmp-*.zip~, phpunit caches, coverage/, .DS_Store, Thumbs.db, .idea/, .vscode/) and re-run:'
    Write-Host '  scripts\\release\\check-release-hygiene.ps1'
    exit 1
}

Write-Host 'Release hygiene check passed.'

