<?php

define("IS_WINDOWS", (strtoupper(substr(PHP_OS, 0, 3)) == "WIN"));

$_ALREADY_REGISTERED = [];
$GLOBALS["__QUEUE"] = [];
$GLOBALS["__QUEUE1"] = [];
$GLOBALS["__QUEUE2"] = [];
function including($path)
{
    global $_ALREADY_REGISTERED, $microtime, $argv;
    $regex11 = "/Class \'(.*?)\' not found/";
    $regex12 = "/Class \\\"(.*?)\\\" not found/";
    $regex21 = "/Interface \'(.*?)\' not found/";
    $regex22 = "/Interface \\\"(.*?)\\\" not found/";
    $regex31 = "/Trait \'(.*?)\' not found/";
    $regex32 = "/Trait \\\"(.*?)\\\" not found/";
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
                if (DEV_MODE) echo "Registering " . $obj1 . " [" . round((microtime(true) - $microtime), 6) . "]\n";
                
                try
                {
                    require $obj1;
                }
                catch (\Throwable $e)
                {
                    $msg = $e->getMessage();
                    if (preg_match($regex11, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex11, "$1", $msg);
                    }
                    else if (preg_match($regex12, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex12, "$1", $msg);
                    }
                    else if (preg_match($regex21, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex21, "$1", $msg);
                    }
                    else if (preg_match($regex22, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex22, "$1", $msg);
                    }
                    else if (preg_match($regex31, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex31, "$1", $msg);
                    }
                    else if (preg_match($regex32, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex32, "$1", $msg);
                    }
                    else
                    {
                        $err = $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
                        if (defined("APPLICATION"))
                        {
                            $file = $e->getFile();
                            $fileSplitted = explode(DIRECTORY_SEPARATOR, $file);
                            for ($i = 1; $i <= count(explode(DIRECTORY_SEPARATOR, dirname($argv[0]))); $i++)
                            {
                                array_shift($fileSplitted);
                            }
                            $err = $e->getMessage() . " in " . implode(DIRECTORY_SEPARATOR, $fileSplitted) . " on line " . $e->getLine();
                            echo $err;
                        }
                        else
                        {
                            fwrite(STDERR, $err);
                        }
                        die(255);
                    }
                    if (!isset($GLOBALS["__QUEUE"][$missingClass]))
                    {
                        $GLOBALS["__QUEUE"][$missingClass] = [];
                        $GLOBALS["__QUEUE1"][$missingClass] = [];
                        $GLOBALS["__QUEUE2"][$missingClass] = [];
                    }
                    if (DEV_MODE) echo "Package " . $obj1 . " is missing '" . $missingClass . "'. Waiting when this class will be loaded... [" . round((microtime(true) - $microtime), 6) . "]\n";
                    $GLOBALS["__QUEUE"][$missingClass][$obj1] = $obj1;
                    $GLOBALS["__QUEUE1"][$missingClass][$obj1] = $e->getFile();
                    $GLOBALS["__QUEUE2"][$missingClass][$obj1] = $e->getLine();
                }
                if (count($GLOBALS["__QUEUE"]) > 0)
                {
                    foreach ($GLOBALS["__QUEUE"] as $notLoadedClass => $value)
                    {
                        if (class_exists($notLoadedClass) || interface_exists($notLoadedClass))
                        {
                            foreach ($GLOBALS["__QUEUE"][$notLoadedClass] as $queueFileName => $value1)
                            {
                                unset($GLOBALS["__QUEUE"][$notLoadedClass][$queueFileName]);
                                unset($GLOBALS["__QUEUE1"][$notLoadedClass][$queueFileName]);
                                unset($GLOBALS["__QUEUE2"][$notLoadedClass][$queueFileName]);
                                if (DEV_MODE) echo "'" . $notLoadedClass . "' was loaded! Trying to register " . $queueFileName . " [" . round((microtime(true) - $microtime), 6) . "]\n";
                                try
                                {
                                    require $queueFileName;
                                }
                                catch (\Throwable $e)
                                {
                                    $msg = $e->getMessage();
                                    if (preg_match($regex11, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex11, "$1", $msg);
                                    }
                                    else if (preg_match($regex12, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex12, "$1", $msg);
                                    }
                                    else if (preg_match($regex21, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex21, "$1", $msg);
                                    }
                                    else if (preg_match($regex22, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex22, "$1", $msg);
                                    }
                                    else if (preg_match($regex31, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex31, "$1", $msg);
                                    }
                                    else if (preg_match($regex32, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex32, "$1", $msg);
                                    }
                                    else
                                    {
                                        $err = $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
                                        if (defined("APPLICATION"))
                                        {
                                            $file = $e->getFile();
                                            $fileSplitted = explode(DIRECTORY_SEPARATOR, $file);
                                            for ($i = 1; $i <= count(explode(DIRECTORY_SEPARATOR, dirname($argv[0]))); $i++)
                                            {
                                                array_shift($fileSplitted);
                                            }
                                            $err = $e->getMessage() . " in " . implode(DIRECTORY_SEPARATOR, $fileSplitted) . " on line " . $e->getLine();
                                            echo $err;
                                        }
                                        else
                                        {
                                            fwrite(STDERR, $err);
                                        }
                                        die(255);
                                    }
                                    if (!isset($GLOBALS["__QUEUE"][$missingClass1]))
                                    {
                                        $GLOBALS["__QUEUE"][$missingClass1] = [];
                                        $GLOBALS["__QUEUE1"][$missingClass1] = [];
                                        $GLOBALS["__QUEUE2"][$missingClass1] = [];
                                    }
                                    if (DEV_MODE) echo "And now package " . $obj1 . " is missing '" . $missingClass1 . "'. Okay... waiting when this class will be loaded... [" . round((microtime(true) - $microtime), 6) . "]\n";
                                    $GLOBALS["__QUEUE"][$missingClass1][$obj1] = $obj1;
                                    $GLOBALS["__QUEUE1"][$missingClass1][$obj1] = $e->getFile();
                                    $GLOBALS["__QUEUE2"][$missingClass1][$obj1] = $e->getLine();
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

if (DEV_MODE) echo "Reading app.json [" . round((microtime(true) - $microtime), 6) . "]\n";

$_APP = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "app.json"), true);
$__FILE__ = __FILE__;
if (DEV_MODE) var_dump($_APP);

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
    return "1.10.0.0";
}

function __CHECK_READKEY() : string
{
    global $microtime;
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
            if (DEV_MODE) echo "Deleting readkey.exe [" . round((microtime(true) - $microtime), 6) . "]\n";
            \IO\FileDirectory::Delete($readkey_file);
        }
        if (DEV_MODE) echo "Copying readkey.exe [" . round((microtime(true) - $microtime), 6) . "]\n";
        \IO\FileDirectory::Copy(dirname(__FILE__) . "/__xrefcore/IO/readkey.exe", $readkey_file);
    }
    return $readkey_file;
}

function __EXCEPTION_HANDLER(Throwable $e) : void
{
    global $argv;
    $file = $e->getFile();
    $line = $e->getLine();
    $trace = $e->getTrace();
    array_pop($trace);
    //array_pop($trace);
    $traceLength = count($trace);
    if (MAIN_THREAD)
    {
        $trace[$traceLength - 1]["file"] = "{main}";
    }
    else
    {
        $trace[$traceLength - 1]["file"] = "{thread}";
    }
    $trace[$traceLength - 1]["line"] = 0;
    if (isset($e->__xrefcoreexception) && $e->__xrefcoreexception)
    {
        $file = $trace[0]["file"];
        $line = $trace[0]["line"];
        array_shift($trace);
        $traceLength = count($trace);
    }
    if (DEV_MODE) var_dump($trace);
    if (defined("APPLICATION"))
    {
        $fileSplitted = explode(DIRECTORY_SEPARATOR, $file);
        for ($i = 1; $i <= count(explode(DIRECTORY_SEPARATOR, dirname($argv[0]))); $i++)
        {
            array_shift($fileSplitted);
        }
        $file = implode(DIRECTORY_SEPARATOR, $fileSplitted);
    }
    $err = "\nUncaught " . get_class($e) . " '" . $e->getMessage() . "' in " . $file . " on line " . $line . "\nStack trace:\n";
    $path_to_app = \Application\Application::GetExecutableFileName();
    $path_to_app = "phar://" . str_replace("\\", "/", $path_to_app) . "/__xrefcore/";
    foreach ($trace as $idx => $row)
    {
        if ($idx != $traceLength - 1 && isset($row["line"]))
        {
            $row["line"] = " on line " . $row["line"];
        }
        else
        {
            $row["line"] = "";
        }
        $arguments = [];
        if (isset($row["args"]))
        {
            foreach ($row["args"] as $arg)
            {
                switch (gettype($arg))
                {
                    case "string":
                        $arguments[] = "\"" . $arg . "\"";
                        break;

                    case "integer":
                    case "float":
                    case "double":
                        $arguments[] = $arg . "";
                        break;

                    case "array":
                        $arguments[] = "Array";
                        break;

                    case "object":
                        $arguments[] = get_class($arg);
                        break;
                }
            }
        }

        if (isset($row["file"]))
        {
            $row["file"] = str_replace("\\", "/", $row["file"]);
            if (strpos($row["file"], $path_to_app) !== false)
            {
                continue;
            }
        }
        $err .= "    ...in " . (isset($row["file"]) ? $row["file"] : "{ANONYMOUS}") . " [" . $row["class"] . $row["type"] . $row["function"] . "(" . implode(", ", $arguments) . ")]" . $row["line"] . "\n";
    }
    $err .= "\n";
    if (defined("APPLICATION"))
    {
        fwrite(STDERR, $err);
    }
    else echo $err;
    exit(255);
}

function __EXCEPTION_HANDLER1(Throwable $e)
{
    $err = "\nUncaught " . get_class($e) . " '" . $e->getMessage() . "' in " . $e->getFile() . " on line " . $e->getLine();
    fwrite(STDERR, $err);
}

if (MAIN_THREAD)
{
    set_exception_handler("__EXCEPTION_HANDLER");
}