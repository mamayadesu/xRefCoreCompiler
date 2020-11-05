<?php

error_reporting(E_ALL);

$phar_file = \Phar::running(false);
if ($phar_file == "")
{
    $file = basename(__FILE__);
    if ($file != "autoload.php")
    {
        die("The name of autoload file must be 'autoload.php'");
    }
}

$_ALREADY_REGISTERED = [];
function including($path)
{
    global $_ALREADY_REGISTERED;
    $data = scandir($path);
    $splitFileName = [];
    $ext = "";
    $obj1 = "";
    $toNextIncluding = [];
    foreach ($data as $obj)
    {
        if ($obj == "." || $obj == "..")
        {
            continue;
        }
        $obj1 = $path . DIRECTORY_SEPARATOR . $obj;
        if (is_file($obj1))
        {
            $splitFileName = explode(".", $obj);
            if (count($splitFileName) < 2)
            {
                continue;
            }
            $ext = $splitFileName[count($splitFileName) - 1];
            if (strtolower($ext) == "php")
            {
                if (in_array($obj1, $_ALREADY_REGISTERED))
                {
                    continue;
                }
                require_once $obj1;
            }
        }
        else
        {
            $toNextIncluding[] = $obj1;
        }
    }
    foreach($toNextIncluding as $obj1)
    {
        including($obj1);
    }
}

$_APP = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "app.json"), true);
$__FILE__ = __FILE__;

if (version_compare(phpversion(), $_APP["php_version"], '<'))
{
    die($_APP["app_name"] . " " . $_APP["app_version"] . " requires at least PHP " . $_APP["php_version"] . ". Your version of PHP is " . phpversion());
}

including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

$namespaces = $_APP["namespaces"];
$priorities = $_APP["priorities"];

\Application\Application::SetTitle($_APP["app_name"]);

function __GET__APP()
{
    global $_APP;
    return $_APP;
}

function __GET__FILE__()
{
    global $__FILE__;
    return $__FILE__;
}

function __GET_FRAMEWORK_VERSION()
{
    return "1.7.4.1";
}

foreach ($priorities as $class)
{
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $class = $class . ".php";
    $class = __DIR__ . DIRECTORY_SEPARATOR . $class;
    require_once $class;
    $_ALREADY_REGISTERED[] = $class;
}

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

if (isset($argv))
{
    $args = $argv;
}

new \Program\Main($args);