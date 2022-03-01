<?php

error_reporting(E_ALL);

define("MAIN_THREAD", false);
define("DEV_MODE", false);
define("APPLICATION", true);

require_once "common.php";

if (version_compare(phpversion(), $_APP["php_version"], '<'))
{
    echo $_APP["app_name"] . " " . $_APP["app_version"] . " requires at least PHP " . $_APP["php_version"] . ". Your version of PHP is " . phpversion() . "\n";
    die(255);
}

including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

foreach ($namespaces as $ns)
{
    if ($ns == "Program")
    {
        echo "Don't insert 'Program' into namespaces. It is deprecated\n";
        exit(255);
    }
    including(__DIR__ . DIRECTORY_SEPARATOR . $ns);
}

including(__DIR__ . DIRECTORY_SEPARATOR . "Program");

$nopackagescount = 0;
if (count($GLOBALS["__QUEUE"]) > 0)
{
    $err = "Next packages could not be loaded:\n";
    foreach ($GLOBALS["__QUEUE"] as $notLoadedClass => $value)
    {
        foreach ($GLOBALS["__QUEUE"][$notLoadedClass] as $notLoadedPackage)
        {
            $nopackagescount++;
            $f1 = $notLoadedPackage;
            $f2 = $GLOBALS["__QUEUE1"][$notLoadedClass][$notLoadedPackage];
            $f1s = explode(DIRECTORY_SEPARATOR, $f1);
            $f2s = explode(DIRECTORY_SEPARATOR, $f1);
            for ($i = 1; $i <= count(explode(DIRECTORY_SEPARATOR, dirname($argv[0]))); $i++)
            {
                array_shift($f1s);
                array_shift($f2s);
            }
            $f1 = implode(DIRECTORY_SEPARATOR, $f1s);
            $f2 = implode(DIRECTORY_SEPARATOR, $f2s);
            $err .= $f1 . " is missing " . $notLoadedClass . " (tried to use in " . $f2 . " on line " . $GLOBALS["__QUEUE2"][$notLoadedClass][$notLoadedPackage] . ")\n";
        }
    }
    if ($nopackagescount > 0)
    {
        if (!defined("APPLICATION"))
        {
            fwrite(STDERR, $err);
        }
        else echo $err;
        die(255);
    }
}

if (!class_exists("\\Program\\Main"))
{
    echo "Class 'Program\\Main' not found.";
    exit(255);
}

exit(0);