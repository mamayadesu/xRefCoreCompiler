echo off
set mydir=%CD%
for %%a in (".") do set CURRENT_DIR_NAME=%%~na
cd C:\php
php C:\Users\Admin\Documents\PHPStorm\xRefCoreCompiler\xRefCoreCompiler\xRefCoreCompiler.phar --projectdir "%mydir%" --skip 1