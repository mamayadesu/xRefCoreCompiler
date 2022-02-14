<?php

$_ALREADY_REGISTERED = [];
$_QUEUE = [];
function including($path)
{
    global $_ALREADY_REGISTERED, $_QUEUE, $dev;
    $regex = "/Class \'(.*?)\' not found/";
    $regex1 = "/Interface \'(.*?)\' not found/";
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
                if ($dev) echo "Registering " . $obj1 . "\n";
                
                try
                {
                    require $obj1;
                }
                catch (\Throwable $e)
                {
                    $msg = $e->getMessage();
                    if (preg_match($regex, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex, "$1", $msg);
                    }
                    else if (preg_match($regex1, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex1, "$1", $msg);
                    }
                    else
                    {
                        die($e->getMessage());
                    }
                    if (!isset($_QUEUE[$missingClass]))
                    {
                        $_QUEUE[$missingClass] = [];
                    }
                    if ($dev) echo "Package " . $obj1 . " is missing '" . $missingClass . "'. Waiting when this class will be loaded...\n";
                    $_QUEUE[$missingClass][$obj1] = $obj1;
                }
                if (count($_QUEUE) > 0)
                {
                    foreach ($_QUEUE as $notLoadedClass => $value)
                    {
                        if (class_exists($notLoadedClass) || interface_exists($notLoadedClass))
                        {
                            foreach ($_QUEUE[$notLoadedClass] as $queueFileName => $value1)
                            {
                                unset($_QUEUE[$notLoadedClass][$queueFileName]);
                                if ($dev) echo "'" . $notLoadedClass . "' was loaded! Trying to register " . $queueFileName . "\n";
                                try
                                {
                                    require $queueFileName;
                                }
                                catch (\Throwable $e)
                                {
                                    $msg = $e->getMessage();
                                    if (preg_match($regex, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex, "$1", $msg);
                                    }
                                    else if (preg_match($regex1, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex1, "$1", $msg);
                                    }
                                    else
                                    {
                                        die($e->getMessage());
                                    }
                                    if (!isset($_QUEUE[$missingClass1]))
                                    {
                                        $_QUEUE[$missingClass1] = [];
                                    }
                                    if ($dev) echo "And now package " . $obj1 . " is missing '" . $missingClass . "'. Okay... waiting when this class will be loaded...\n";
                                    $_QUEUE[$missingClass1][$obj1] = $obj1;
                                }
                            }
                        }
                    }
                }
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

if (count($_QUEUE) > 0)
{
    echo "Next packages could not be loaded:\n";
    foreach ($_QUEUE as $notLoadedClass)
    {
        foreach ($_QUEUE[$notLoadedClass] as $notLoadedPackage)
        {
            echo $notLoadedPackage . " is missing " . $notLoadedClass;
        }
    }
    die(255);
}

if ($dev) echo "Reading app.json\n";

$_APP = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "app.json"), true);
$__FILE__ = __FILE__;
if ($dev) var_dump($_APP);

$namespaces = $_APP["namespaces"];

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
    return "1.9.0.0";
}

$is_windows = (strtoupper(substr(PHP_OS, 0, 3)) == "WIN");

function __CHECK_READKEY() : string
{
    global $dev;
    $hash = "4c5b90bfc69c23b6e2487a490bcdf1af";
    $readkey_path = sys_get_temp_dir() . "\\";
    $readkey_file = $readkey_path . "readkey" . __GET_FRAMEWORK_VERSION() . ".exe";
    if (!MAIN_THREAD)
    {
        return $readkey_file;
    }
    $check_file = file_exists($readkey_file);
    $check_hash = false;
    if ($check_file)
    {
        $check_hash = md5_file($readkey_file) == $hash;
    }
    if (!$check_file || !$check_hash)
    {
        if (!$check_hash)
        {
            if ($dev) echo "Deleting readkey.exe";
            \IO\FileDirectory::Delete($readkey_file);
        }
        if ($dev) echo "Copying readkey.exe\n";
        \IO\FileDirectory::Copy(dirname(__FILE__) . "/Core/IO/readkey.exe", $readkey_file);
    }
    return $readkey_file;
}