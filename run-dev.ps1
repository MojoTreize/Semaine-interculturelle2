$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

php -d extension=sqlite3 -d extension=pdo_sqlite -S 127.0.0.1:8000 -t .
