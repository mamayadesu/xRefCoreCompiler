$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
$isAdmin = $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

echo "oOoOoOoOo xRefCoreCompiler Installer oOoOoOoOo"
if ($isAdmin -eq $false) {
    echo "Please, run installer with administrator permissions!"
    pause
    exit
}

echo "[1/5] Downloading the latest release..."

if ((Test-Path -Path xRefCoreCompiler.phar -PathType Leaf) -eq $true) {
    Remove-Item xRefCoreCompiler.phar
}

Invoke-WebRequest https://github.com/mamayadesu/xRefCoreCompiler/raw/main/xRefCoreCompiler.phar -OutFile xRefCoreCompiler.phar > $NULL

echo "[2/5] Checking your PHP-configuration...";

$isphpok = $true
Try
{
    php -v > $NULL
}
Catch
{
    $isphpok = $false;
}

if ($isphpok -eq $false) {
    echo "ERROR! 'php' command not found or PHP is not installed. Please, register your PHP in Path enviroment."
    exit
}

if ((Test-Path -Path $HOME\.xRefCoreCompiler) -eq $false) {
    mkdir $HOME\.xRefCoreCompiler
}

if ((Test-Path -Path $HOME\.xRefCoreCompiler\tests.php -PathType Leaf) -eq $true) {
    Remove-Item $HOME\.xRefCoreCompiler\tests.php
}

Invoke-WebRequest https://raw.githubusercontent.com/mamayadesu/xRefCoreCompiler/main/tests.php -OutFile $HOME\.xRefCoreCompiler\tests.php > $NULL

php $HOME\.xRefCoreCompiler\tests.php

if ($LASTEXITCODE -eq 255) {
    exit
}

if ($LASTEXITCODE -ne 0) {
    echo "An error occurred while checking your PHP-configuration"
    exit
}

Remove-Item $HOME\.xRefCoreCompiler\tests.php

echo "[3/5] Installing..."

if ((Test-Path -Path $HOME\.xRefCoreCompiler\xRefCoreCompiler.phar -PathType Leaf) -eq $true) {
    Remove-Item $HOME\.xRefCoreCompiler\xRefCoreCompiler.phar
}
move xRefCoreCompiler.phar $HOME\.xRefCoreCompiler

echo "[4/5] Configuring xRefCoreCompiler..."

if ((Test-Path -Path $profile -PathType Leaf) -eq $false) {
    New-Item -path $profile -type file -force > $NULL
}

if (Get-Command 'xrefcore-compiler' -errorAction SilentlyContinue) {
    $check = $true
}
else {
    $check = $false
    function xrefcore-compiler { php $HOME\.xRefCoreCompiler\xRefCoreCompiler.phar $args }
}

if ($check -eq $false) {
    Add-Content $profile "`nfunction xrefcore-compiler { php `$HOME\.xRefCoreCompiler\xRefCoreCompiler.phar `$args }`n"
}
xrefcore-compiler --configure
echo "[5/5] Done! Now please run '.`$profile' command. Use 'xrefcore-compiler' command to run"
pause