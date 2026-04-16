$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..\\..")

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
  Write-Error "composer is required for dependency audit."
  exit 1
}

$locks = Get-ChildItem -Path $root -Recurse -Filter composer.lock -File | Where-Object {
  $_.FullName -notmatch "\\\\vendor\\\\"
}

if (-not $locks) {
  Write-Host "No composer.lock files found. Skipping audit."
  exit 0
}

foreach ($lock in $locks) {
  $dir = $lock.DirectoryName
  Write-Host "==> composer audit in $dir"
  Push-Location $dir
  try {
    composer audit --no-interaction --no-ansi
  } finally {
    Pop-Location
  }
}
