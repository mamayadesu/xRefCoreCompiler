<?php

error_reporting(E_ALL);

$port = 0x0000;
$__CLASSNAME = "";
$__JSONNEWARGS = [];
$__PARENTPID = 0x0000;
// {RANDOMKEY}

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

including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

$namespaces = $_APP["namespaces"];
$priorities = $_APP["priorities"];

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
    return "1.7.4.3";
}

spl_autoload_register(function(string $className)
{
    if (!class_exists($className))
    {
        if (DIRECTORY_SEPARATOR == "/")
        {
            $className = str_replace("\\", "/", $className);
        }
        require_once __DIR__ . DIRECTORY_SEPARATOR . $className . ".php";
    }
});

if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    exit;
}
$mypid = getmypid() . '';
if (!socket_connect($sock, '127.0.0.1', $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    if ($errorcode != 0)
    {
        exit;
    }
}

$data = array('receivedpid' => $mypid);
$json = json_encode($data);
$length = strlen($json);
$lenstr = str_repeat("0", 16 - strlen($length . '')) . $length;

if (!socket_send($sock, $lenstr, 16, 0))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    if ($errorcode != 0)
    {
        exit;
    }
}

if (!socket_send($sock, $json, $length, 0))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    if ($errorcode != 0)
    {
        exit;
    }
}
new \Threading\__DataManager2($sock);

$__CLASSNAME::__SetParentThreadPid($__PARENTPID);
$thread = new $__CLASSNAME();
$thread->__setdata($sock, $port, new \Threading\ParentThreadedObject($sock, $port, $thread));
$thread->Threaded($__JSONNEWARGS);