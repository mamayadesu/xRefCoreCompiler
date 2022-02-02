echo off
setlocal enableextensions
cd /d "%~dp0"
set mydir=%CD%
for %%a in (".") do set CURRENT_DIR_NAME=%%~na
rem Put your path to `Core` folder
mklink /D "%mydir%\Core" "C:\Users\Admin\Documents\PHPStorm\xRefCore\xRefCore\Core"