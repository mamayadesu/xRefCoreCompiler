<?php

error_reporting(E_ALL);

define("MAIN_THREAD", false);
define("DEV_MODE", false);

/* parent thread port */
$port = 0x0000;
/* port of super global array */
$gaport = 0x0000;
$gapid = 0x0000;

/* port for receiving data from sga */
$garecport = rand(10000, 60000);
$__CLASSNAME = "";
$__JSONNEWARGS = [];
$__PARENTPID = 0x0000;
/* {RANDOMKEY} */

if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    exit;
}
$mypid = getmypid() . "";
/*if (!socket_connect($sock, "127.0.0.1", $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    if ($errorcode != 0)
    {
        exit;
    }
}*/

$data = array("receivedpid" => $mypid);
$json = json_encode($data);
$length = strlen($json);
$lenstr = str_repeat("0", 16 - strlen($length . "")) . $length;

if (!socket_sendto($sock, $lenstr, 16, 0, "127.0.0.1", $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    if ($errorcode != 0)
    {
        exit;
    }
}

if (!socket_sendto($sock, $json, $length, 0, "127.0.0.1", $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    if ($errorcode != 0)
    {
        exit;
    }
}

require_once __DIR__ . DIRECTORY_SEPARATOR . "common.php";

including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

spl_autoload_register(function(string $className)
{
    $_className = "";
    if (!class_exists($className))
    {
        $_className = __DIR__ . DIRECTORY_SEPARATOR . $className . ".php";

        if (strtoupper(substr(PHP_OS, 0, 3)) != "WIN")
        {
            $_className = str_replace("\\", "/", $_className);
        }
        require_once $_className;
    }
});

$gasock = socket_create(AF_INET, SOCK_DGRAM, 0);
while (true)
{
    if (@socket_bind($gasock, "127.0.0.1", $garecport))
    {
        break;
    }
    $garecport = rand(10000, 60000);
}
new \Threading\__DataManager1($gasock, $garecport);
new \Threading\__DataManager2($sock, $port);

$superglobalarraythreaded = new \Threading\Threaded($gapid, [], "\\Threading\\__SuperGlobalArrayThreaded", $sock, $gaport, new \stdClass());
$superglobalarray = new \Threading\SuperGlobalArray();
$superglobalarray->__setSga($superglobalarraythreaded);

function __onshutdown()
{
    foreach (\Threading\Thread::GetAllChildThreads() as $threaded)
    {
        if ($threaded->IsRunning())
        {
            $threaded->Kill();
        }
    }
}

register_shutdown_function("__onshutdown");

$__CLASSNAME::__SetParentThreadPid($__PARENTPID);
$thread = new $__CLASSNAME();
$thread->__setdata($sock, $port, new \Threading\ParentThreadedObject($sock, $port, $thread));
$thread->Threaded($__JSONNEWARGS);