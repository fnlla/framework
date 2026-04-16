$ErrorActionPreference = 'Stop'

$root = (Get-Item -Path (Join-Path $PSScriptRoot '..\\..')).FullName
$tools = Join-Path $root 'tools'
$harness = Join-Path $tools 'harness'
$phpunit = Join-Path $tools 'vendor\bin\phpunit'
$coveragePath = $env:COVERAGE_PATH
if (-not $coveragePath) {
  $coveragePath = Join-Path $root '.artifacts\coverage.xml'
}

try {
  if (-not (Test-Path $phpunit)) {
    Write-Host '==> Installing PHPUnit toolchain'
    Push-Location $tools
    composer install --no-interaction --prefer-dist --no-progress
    Pop-Location
  }

  if (-not (Test-Path (Join-Path $harness 'vendor\autoload.php'))) {
    Write-Host '==> Installing harness dependencies'
    Push-Location $harness
    composer install --no-interaction --prefer-dist --no-progress
    Pop-Location
  }

  Write-Host '==> Running PHPUnit with coverage (core + packages)'
  $coverageDir = Split-Path -Parent $coveragePath
  if ($coverageDir -and -not (Test-Path $coverageDir)) {
    New-Item -ItemType Directory -Path $coverageDir | Out-Null
  }
  & $phpunit -c (Join-Path $root 'tools\phpunit.xml') --coverage-clover $coveragePath
  if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}
finally {
  Remove-Item -Recurse -Force (Join-Path $tools 'vendor') -ErrorAction SilentlyContinue
  Remove-Item -Force (Join-Path $tools 'composer.lock') -ErrorAction SilentlyContinue
}
