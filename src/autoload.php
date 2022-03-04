<?php

error_reporting(E_ALL);

define("MAIN_THREAD", true);
define("DEV_MODE", false);

if (!extension_loaded("sockets"))
{
    die("Sockets extension is required!\n");
}

$microtime = microtime(true);

$phar_file = \Phar::running(false);

if ($phar_file == "")
{
    fwrite(STDERR, "You must compile application before run it.\n");
    die(255);
}

if (DEV_MODE) echo "Initializing... [" . round((microtime(true) - $microtime), 6) . "]\n";

require_once "common.php";

if (DEV_MODE) echo "Checking PHP-version [" . round((microtime(true) - $microtime), 6) . "]\n";
if (version_compare(phpversion(), $_APP["php_version"], '<'))
{
    die($_APP["app_name"] . " " . $_APP["app_version"] . " requires at least PHP " . $_APP["php_version"] . ". Your version of PHP is " . phpversion() . "\n");
}

if (DEV_MODE) echo "Loading core... [" . round((microtime(true) - $microtime), 6) . "]\n";
including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

if (DEV_MODE) echo "Setting title [" . round((microtime(true) - $microtime), 6) . "]\n";
\Application\Application::SetTitle($_APP["app_name"]);

if (DEV_MODE) echo "Loading classes... [" . round((microtime(true) - $microtime), 6) . "]\n";
foreach ($namespaces as $ns)
{
    if ($ns == "Program")
    {
        echo "Don't insert 'Program' into namespaces. It is deprecated\n";
        continue;
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
            $err .= $notLoadedPackage . " is missing " . $notLoadedClass . " (tried to use in " . $GLOBALS["__QUEUE1"][$notLoadedClass][$notLoadedPackage] . " on line " . $GLOBALS["__QUEUE2"][$notLoadedClass][$notLoadedPackage] . ")\n";
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
    fwrite(STDERR, "\nFatal Error: Class 'Program\\Main' not found.\n\n");
    exit(255);
}

$args = [];

if (DEV_MODE) echo "Reading on-start args... [" . round((microtime(true) - $microtime), 6) . "]\n";
if (isset($argv))
{
    $args = $argv;
}

if (DEV_MODE) var_dump($args);

if (IS_WINDOWS)
{
    __CHECK_READKEY();
}

function __onshutdown()
{
    define("SHUTTINGDOWN", true);
    $superglobalarray = \Threading\SuperGlobalArray::GetInstance();
    if ($superglobalarray != null)
    {
        $superglobalarraythreaded = $superglobalarray->____getthread();
        if ($superglobalarraythreaded != null)
        {
            $superglobalarraythreaded->Kill();
        }
    }

    foreach (\Threading\Thread::GetAllChildThreads() as $threaded)
    {if(!$threaded instanceof \Threading\Threaded) continue;
        if ($threaded->IsRunning())
        {
            $threaded->Kill();
        }
    }
}

register_shutdown_function("__onshutdown");

if (DEV_MODE) echo "Starting application... [" . round((microtime(true) - $microtime), 6) . "]\n";
new \Program\Main($args);