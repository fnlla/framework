$ErrorActionPreference = 'Stop'

$root = (Get-Item -Path (Join-Path $PSScriptRoot '..\\..')).FullName
$tools = Join-Path $root 'tools'
$phpstan = Join-Path $tools 'vendor\bin\phpstan'

try {
  if (-not (Test-Path $phpstan)) {
    Write-Host '==> Installing PHPStan toolchain'
    Push-Location $tools
    composer install --no-interaction --prefer-dist --no-progress
    Pop-Location
  }

  Write-Host '==> Running PHPStan'
  & $phpstan analyse -c (Join-Path $root 'tools\phpstan.neon')
  if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}
finally {
  Remove-Item -Recurse -Force (Join-Path $tools 'vendor') -ErrorAction SilentlyContinue
  Remove-Item -Force (Join-Path $tools 'composer.lock') -ErrorAction SilentlyContinue
}
