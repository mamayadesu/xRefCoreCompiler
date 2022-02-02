<?php

error_reporting(E_ALL);

if (!extension_loaded("sockets"))
{
    die("Sockets extension is required!\n");
}

$dev = false;

$phar_file = \Phar::running(false);

if ($phar_file == "")
{
    $file = basename(__FILE__);
    if ($file != "autoload_dev.php")
    {
        die("The name of autoload file must be 'autoload.php'");
    }
}

if ($dev) echo "Initializing...\n";

require_once "common.php";

if ($dev) echo "Checking PHP-version\n";
if (version_compare(phpversion(), $_APP["php_version"], '<'))
{
    die($_APP["app_name"] . " " . $_APP["app_version"] . " requires at least PHP " . $_APP["php_version"] . ". Your version of PHP is " . phpversion() . "\n");
}

if ($dev) echo "Loading core...\n";
including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

if ($dev) echo "Setting title\n";
\Application\Application::SetTitle($_APP["app_name"]);

if ($dev) echo "Loading classes...\n";
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

$args = [];

if ($dev) echo "Reading on-start args...\n";
if (isset($argv))
{
    $args = $argv;
}

if ($dev) var_dump($args);

if ($dev) echo "Initializing super global array thread...\n";
$superglobalarraythreaded = \Threading\__SuperGlobalArrayThread::Run([], new \stdClass());

if ($dev) echo "Initializing super global array...\n";
$superglobalarray = new \Threading\SuperGlobalArray();
$superglobalarray->__setSga($superglobalarraythreaded);

function __onshutdown()
{
    global $superglobalarraythreaded;
    $superglobalarraythreaded->Kill();

    foreach (\Threading\Thread::GetAllChildThreads() as $threaded)
    {if(!$threaded instanceof \Threading\Threaded) continue;
        if ($threaded->IsRunning())
        {
            $threaded->Kill();
        }
    }
}

register_shutdown_function("__onshutdown");

if ($dev) echo "Starting application...\n";
new \Program\Main($args);