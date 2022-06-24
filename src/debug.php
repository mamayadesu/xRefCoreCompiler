<?php
declare(ticks = 1);

define("DEBUG_MODE", true);

$socket = socket_create(AF_INET, SOCK_DGRAM, 0);

$port = rand(10000, 60000);

while (!@socket_bind($socket, "127.0.0.1", $port))
    $port = rand(10000, 60000);

\IO\Console::WriteLine("Application starting in debug mode. Run xRefCoreDebugger and use port " . $port . " to attach your application process.");
\IO\Console::WriteLine("And now press any key to start application in default mode or press SPACE to start application in manually iterate mode.");
$key = \IO\Console::ReadKey();

$GLOBALS["__xrefcore_debug.manually_iterate_mode"] = false;
if ($key == "spacebar")
{
    $GLOBALS["__xrefcore_debug.manually_iterate_mode"] = true;
    \IO\Console::WriteLine("Waiting for xRefCoreDebugger...");
}
$GLOBALS["__xrefcore_debug.sharedObjects"] = [];
$GLOBALS["__xrefcore_debug.file"] = "Unknown";
$GLOBALS["__xrefcore_debug.clearFile"] = "Unknown";
$GLOBALS["__xrefcore_debug.line"] = 0;
$GLOBALS["__xrefcore_debug.socket"] = $socket;
$GLOBALS["__xrefcore_debug.port"] = $port;
$GLOBALS["__xrefcore_debug.connectedPort"] = -1;
// window height 30
function __xrefcore_debug() : void
{
    $socket = $GLOBALS["__xrefcore_debug.socket"];

    $e = new Exception("");
    $trace = $e->getTrace();
    $xc = "__xrefcore/";
    $stackItem = null;
    $filename = "";
    $file = "Unknown";
    $clearFile = "Unknown";
    $line = 0;

    $pharRoot = "phar://" . str_replace("\\", "/", \Application\Application::GetExecutableFileName()) . "/";
    foreach ($trace as $si)
    {
        if (isset($si["file"]))
        {
            if (str_replace("\\", "/", $si["file"]) == str_replace("\\", "/", \Application\Application::GetExecutableFileName()))
                continue;
            $f = str_replace($pharRoot, "", $si["file"]);
            if (!in_array($f, ["autoload.php", "common.php", "debug.php", "thread.php"]) && substr($f, 0, strlen($xc)) != $xc)
            {
                $clearFile = $f;
                $stackItem = $si;
                break;
            }
        }
    }
    if ($stackItem != null)
    {
        $file = $stackItem["file"];
        $line = $stackItem["line"];
    }
    if ($file == "Unknown" && $GLOBALS["__xrefcore_debug.file"] != "Unknown")
    {
        return;
    }
    $remote_ip = "";
    $remote_port = 0;
    // if point execution was changed
    // $file == "Unknown" means that application just started
    if (($file != $GLOBALS["__xrefcore_debug.file"] || $line != $GLOBALS["__xrefcore_debug.line"]) || $file == "Unknown")
    {
        $GLOBALS["__xrefcore_debug.file"] = $file;
        $GLOBALS["__xrefcore_debug.line"] = $line;
        $GLOBALS["__xrefcore_debug.clearFile"] = $clearFile;
        $query = "";
        restart:
        if ($GLOBALS["__xrefcore_debug.manually_iterate_mode"])
        {
            while (!($has_unread_data = \Threading\Thread::ReadLongQuery($socket, $query, $remote_ip, $remote_port, false)))
            {}
        }
        else
            $has_unread_data = \Threading\Thread::ReadLongQuery($socket, $query, $remote_ip, $remote_port, false);

        if ($has_unread_data)
        {
            $json = json_decode($query, true);

            $action = $json["action"];
            if (!isset($json["port"]) || !is_int($json["port"]))
            {
                \Threading\Thread::SendLongQuery($socket, json_encode(array("error" => "An error occurred. Debugger port was not received.", "error_code" => 0)), $remote_ip, $remote_port);
                return;
            }
            $remote_port = $json["port"];
            if ($action == "attach")
            {
                if ($GLOBALS["__xrefcore_debug.connectedPort"] != -1)
                {
                    if ($GLOBALS["__xrefcore_debug.connectedPort"] != $remote_port)
                        \Threading\Thread::SendLongQuery($socket, json_encode(array("error" => "Some another xRefCoreDebugger already attached to this process.", "error_code" => 1)), $remote_ip, $remote_port);
                    else
                        \Threading\Thread::SendLongQuery($socket, json_encode(array("error" => "You've already attached to this process.", "file" => $file, "line" => $line, "clearFile" => $clearFile, "mim" => $GLOBALS["__xrefcore_debug.manually_iterate_mode"], "error_code" => 2)), $remote_ip, $remote_port);
                }
                else
                {
                    $GLOBALS["__xrefcore_debug.connectedPort"] = $json["port"];
                    \Threading\Thread::SendLongQuery($socket, json_encode(array("action" => "attached", "file" => $file, "line" => $line, "clearFile" => $clearFile, "mim" => $GLOBALS["__xrefcore_debug.manually_iterate_mode"])), $remote_ip, $remote_port);
                    \IO\Console::WriteLine("xRefCoreDebugger ATTACHED");
                }
                if ($GLOBALS["__xrefcore_debug.manually_iterate_mode"])
                    goto restart;
            }
            else
            {
                if ($GLOBALS["__xrefcore_debug.connectedPort"] != $remote_port)
                {
                    \Threading\Thread::SendLongQuery($socket, json_encode(array("error" => "You're not attached to process.", "error_code" => 3)), $remote_ip, $remote_port);
                    if ($GLOBALS["__xrefcore_debug.manually_iterate_mode"])
                        goto restart;
                    return;
                }
                if ($action == "detach")
                {
                    //\Threading\Thread::SendLongQuery($socket, json_encode(array("action" => "detached")), $remote_ip, $remote_port);
                    $GLOBALS["__xrefcore_debug.connectedPort"] = -1;
                    $GLOBALS["__xrefcore_debug.manually_iterate_mode"] = false;
                    \IO\Console::WriteLine("xRefCoreDebugger DETACHED");
                    return;
                }
                if ($action == 'iterate')
                {
                    if (!$GLOBALS["__xrefcore_debug.manually_iterate_mode"])
                    {
                        $GLOBALS["__xrefcore_debug.manually_iterate_mode"] = true;
                        \IO\Console::WriteLine("xRefCoreDebugger ENABLED MANUALLY ITERATE MODE");
                        goto restart;
                    }
                }
                if ($action == 'noiterate')
                {
                    if (!$GLOBALS["__xrefcore_debug.manually_iterate_mode"])
                    {
                        $GLOBALS["__xrefcore_debug.manually_iterate_mode"] = false;
                        \IO\Console::WriteLine("xRefCoreDebugger DISABLED MANUALLY ITERATE MODE");
                    }
                }
            }
        }
        if ($GLOBALS["__xrefcore_debug.manually_iterate_mode"])
            \Threading\Thread::SendLongQuery($socket, json_encode(array("action" => "iterated", "file" => $file, "line" => $line, "clearFile" => $clearFile)), "127.0.0.1", $GLOBALS["__xrefcore_debug.connectedPort"]);
    }
}

function xcd(object ...$objects) : void
{
    foreach ($objects as $obj)
    {
        if (!in_array($obj, $GLOBALS["__xrefcore_debug.sharedObjects"]))
        {
            $GLOBALS["__xrefcore_debug.sharedObjects"][] = $obj;
        }
    }
}

$GLOBALS["system.tick_functions"]["__xrefcore_debug"] = "__xrefcore_debug";