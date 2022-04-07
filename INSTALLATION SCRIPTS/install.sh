#!/bin/bash
echo "oOoOoOoOo xRefCoreCompiler Installer oOoOoOoOo"

if [ "$EUID" -ne "0" ]
then
	echo "Please run installer as root or via sudo"
	exit
fi

echo "[1/5] Downloading the latest release..."

if [ -f "xRefCoreCompiler.phar" ]
then
    rm xRefCoreCompiler.phar
fi

wget -q --show-progress -O xRefCoreCompiler.phar https://github.com/mamayadesu/xRefCoreCompiler/raw/main/xRefCoreCompiler.phar

echo "[2/5] Checking your PHP-configuration...";

PHP="php"

if [ "$1" = "--php" ]
then
	PHP=$2
fi

ISPHPOK=1
$PHP -r "exit(123);" 2> /dev/null
if [ $? -ne 123 ]
then
    ISPHPOK=0
fi

if [ "$ISPHPOK" = "0" ]
then
    echo "ERROR! PHP not found or is not installed. You can try run this script with '--php <PATH TO BINARY>' argument. Example: './install.sh --php /usr/bin/php7.4'"
    exit
fi

if [ -d "$HOME/.xRefCoreCompiler" ] 
then
    echo "Do nothing" > /dev/null
else
    mkdir $HOME/.xRefCoreCompiler
fi

if [ -f "$HOME/.xRefCoreCompiler/tests.php" ]
then
    rm $HOME/.xRefCoreCompiler/tests.php
fi

wget -q -O $HOME/.xRefCoreCompiler/tests.php https://raw.githubusercontent.com/mamayadesu/xRefCoreCompiler/main/tests.php

$PHP $HOME/.xRefCoreCompiler/tests.php

if [ $? -eq 255 ]
then
    rm $HOME/.xRefCoreCompiler/tests.php
    exit
fi

if [ $? -ne 0 ]
then
    echo "An error occurred while checking your PHP-configuration"
    exit
fi

rm $HOME/.xRefCoreCompiler/tests.php

echo "[3/5] Installing..."

if [ -d /usr/bin/xRefCoreCompiler ] 
then
    echo "Do nothing" > /dev/null
else
    mkdir /usr/bin/xRefCoreCompiler
fi

if [ -f /usr/bin/xRefCoreCompiler/xRefCoreCompiler.phar ]
then
    rm /usr/bin/xRefCoreCompiler/xRefCoreCompiler.phar
fi

mv xRefCoreCompiler.phar /usr/bin/xRefCoreCompiler/xRefCoreCompiler.phar

echo "[4/5] Configuring xRefCoreCompiler..."

if [ -f "/usr/bin/xrefcore-compiler" ]
then
    rm /usr/bin/xrefcore-compiler
fi

echo "$PHP /usr/bin/xRefCoreCompiler/xRefCoreCompiler.phar \$@" > /usr/bin/xrefcore-compiler

chmod 755 /usr/bin/xrefcore-compiler

xrefcore-compiler --configure

echo "[5/5] Done! Use 'xrefcore-compiler' command to run."
