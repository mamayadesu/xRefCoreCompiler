<?php
declare(ticks = 1);
error_reporting(E_ALL);
define("MAIN_THREAD", false);
define("DEV_MODE", false);

$bigArg = unserialize(base64_decode($argv[1]));

/* parent thread port */
$port = $bigArg["port"];
/* port of super global array */
$gaport = $bigArg["gaport"] ?? 0;
$gapid = $bigArg["gapid"] ?? 0;

/* port for receiving data from sga */
$garecport = rand(10000, 60000);
$__CLASSNAME = $bigArg["__CLASSNAME"];
$__ARGS = $bigArg["__ARGS"];
$__PARENTPID = $bigArg["__PARENTPID"];
$pathToPharContent = $bigArg["pathToPharContent"];

if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    exit;
}
$mypid = getmypid() . "";

$data = array("receivedpid" => $mypid);
$json = serialize($data);
$length = strlen($json);
$lenstr = str_repeat("0", 16 - strlen($length . "")) . $length;

require_once $pathToPharContent . "common.php";

including($pathToPharContent . "__xrefcore");

@cli_set_process_title(\Application\Application::GetName());

if (!\Threading\Thread::SendLongQuery($sock, $json, \Threading\Thread::ADDRESS, $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    if ($errorcode != 0)
    {
        exit;
    }
}

spl_autoload_register(function(string $className)
{
    global $pathToPharContent;
    $_className = "";
    if (!class_exists($className))
    {
        $_className = $pathToPharContent . $className . ".php";

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
    if (@socket_bind($gasock, \Threading\Thread::ADDRESS, $garecport))
    {
        break;
    }
    $garecport = rand(10000, 60000);
}
new \Threading\__DataManager1($gasock, $garecport);
new \Threading\__DataManager2($sock, $port);

$superglobalarraythreaded = new \Threading\Threaded($gapid, [], "\\Threading\\__SuperGlobalArrayThread", $sock, $gaport, new \stdClass());
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

function __onsignal(int $signal, $siginfo = null) : void
{}

register_shutdown_function("__onshutdown");
if (!IS_WINDOWS)
{
    pcntl_async_signals(true);
    pcntl_signal(SIGTERM, "__onsignal");
    pcntl_signal(SIGHUP, "__onsignal");
    pcntl_signal(SIGINT, "__onsignal");
}
else
{
    sapi_windows_set_ctrl_handler(function(int $event) : void
    {
        if ($event == PHP_WINDOWS_EVENT_CTRL_C)
            __onsignal($event);
    }, true);
}

\IO\Console::__setparentpid($__PARENTPID);
$__CLASSNAME::__SetParentThreadPid($__PARENTPID);
$thread = new $__CLASSNAME();
$thread->__setdata($sock, $port, new \Threading\ParentThreadedObject($sock, $port, $thread));

new \Scheduler\SchedulerMaster();
$thread->Threaded($__ARGS);