<?php

echo "Checking your PHP version... ";
if (version_compare(phpversion(), "7.4", "<"))
{
    echo "FAIL. xRefCoreCompiler requires PHP 7.4 at least. Your version is " . phpversion() . ".\n";
    exit(255);
} else echo "OK\n";

echo "Checking Sockets extension... ";
if (!extension_loaded("sockets"))
{
    echo "FAIL. Sockets extension is not loaded or installed.\n";
    exit(255);
} else echo "OK\n";

echo "Checking PHAR... ";
if (ini_get("phar.readonly") == "1")
{
    echo "FAIL. PHAR has read-only mode. Open your 'php.ini' and set phar.readonly to Off.\n";
    exit(255);
} else echo "OK\n";
exit(0);
