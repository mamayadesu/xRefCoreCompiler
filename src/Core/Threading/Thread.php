<?php

namespace Threading;

use Application\Application;
use \Exception;
use IO\Console;

/**
 * Allows you to create new threads. At the same time, this class is used by the child thread to access the parent
 * @package Threading
 */

class Thread
{
    /**
     * @var string
     * @ignore
     */
    private static string $phpCommand = "";

    /**
     * @var int
     * @ignore
     */
    private static int $parentThreadPid = -1;

    /**
     * @var null
     * @ignore
     */
    private $__socket = null;

    /**
     * @var int
     * @ignore
     */
    private int $__parentsockport;

    /**
     * @var ParentThreadedObject|null
     * @ignore
     */
    private ?ParentThreadedObject $pto;

    /**
     * Thread constructor.
     * @throws Exception
     * @ignore
     */
    final function __construct()
    {
        if (self::$parentThreadPid == -1)
        {
            throw new Exception("Unable to initialize thread class. Use static method Run(array \$args) to create thread.");
        }
    }

    /**
     * This method calls automatically in child-thread when it was created
     *
     * @param array<int, string> $args Arguments passed by the parent thread
     */
    public function Threaded(array $args) : void
    {

    }

    /**
     * @return string
     * @ignore
     */
    private static function GetWindowsPhpCommand() : string
    {
        $cmd = "wmic process where \"processId=" . getmypid() . "\" get commandLine";
        exec($cmd, $output);
        $fullcommand = $output[1];
        $command = "";
        if (substr($fullcommand, 0, 1) == '"')
        {
            $command1 = str_split($fullcommand);
            $a = false;
            foreach ($command1 as $char)
            {
                $command .= $char;
                if ($char == '"')
                {
                    if (!$a)
                    {
                        $a = true;
                    }
                    else
                    {
                        break;
                    }
                }
            }
        }
        else
        {
            $command = explode(' ', $fullcommand)[0];
        }
        return $command;
    }

    /**
     * @return string
     * @ignore
     */
    private static function GetUnixPhpCommand() : string
    {
        $cmd = "ps -p " . getmypid();
        exec($cmd, $output);
        $pinfo3 = $output[1];
        $pinfo2 = explode(' ', $pinfo3);
        $pinfo1 = [];
        foreach ($pinfo2 as $item)
        {
            if ($item != "")
            {
                $pinfo1[] = $item;
            }
        }
        for ($i = 0; $i < 3; $i++)
        {
            array_shift($pinfo1);
        }
        $pinfo = implode(' ', $pinfo1);
        return $pinfo;
    }

    /**
     * @return string
     * @ignore
     */
    private static function GetPhpCommand() : string
    {
        if (self::$phpCommand != "")
        {
            return self::$phpCommand;
        }
        $cmd = "";
        if (Thread::IsWindows())
        {
            $cmd = self::GetWindowsPhpCommand();
        }
        else
        {
            $cmd = self::GetUnixPhpCommand();
        }
        self::$phpCommand = $cmd;
        return $cmd;
    }

    /**
     * @param int $parentThread
     * @throws Exception
     * @ignore
     */
    final static function __SetParentThreadPid(int $parentThread) : void
    {
        // For child thread
        if (self::$parentThreadPid != -1)
        {
            throw new Exception("This method is unavailable for user call");
        }
        self::$parentThreadPid = $parentThread;
    }

    /**
     * Returns a PID of parent thread
     *
     * @return int PID of parent thread
     */
    final function GetParentThreadPid() : int
    {
        // For child thread
        return self::$parentThreadPid;
    }

    /**
     * Returns TRUE if parent thread still running
     *
     * @return bool
     */
    final function IsParentStillRunning() : bool
    {
        if (Thread::IsWindows())
        {
            exec("tasklist /FI \"PID eq " . self::$parentThreadPid . "\" /FO csv | find /c /v \"\"", $output1);
            $output = rtrim(str_replace(" ", "", $output1[0]));
            $linesCount = intval($output);
            return $linesCount > 1;
        }
        else
        {
            return file_exists("/proc/" . self::$parentThreadPid);
        }
    }

    final static function IsWindows() : bool
    {
        return (strtolower(substr(php_uname(), 0, 7)) == "windows");
    }

    /**
     * Provides access to public methods and properties of threaded parent object
     *
     * @return ParentThreadedObject|null Threaded parent object
     */
    final function GetParentThreadedObject() : ?ParentThreadedObject
    {
        return $this->pto;
    }

    /**
     * Blocks child-thread and waits when parent-thread will join to child-thread
     */
    final public function WaitForParentAccess()
    {
        $__dm = __DataManager2::GetInstance();
        while (true)
        {
            $q = $__dm->__Read();
            while (true)
            {
                if (!isset($q["act"]))
                {
                    $q = $__dm->__Continue();
                }
                else
                {
                    $q = $__dm->__Fetch();
                    break;
                }
            }
            
            switch ($q["act"])
            {
                case "c":
                    $result = call_user_func_array(array($this, $q["method"]), $q["args"]);
                    break;
                    
                case "g":
                    $result = $this->{$q["prop"]};
                    break;
                
                case "s":
                    $this->{$q["prop"]} = $q["val"];
                    break;

                case "sy":
                    return;
                    break;
            }
            
            $type = strtolower(gettype($result));
            if ($type == "null")
            {
                $type = "void";
                $result = "";
            }
            else if ($type == "integer")
            {
                $type = "int";
            }
            else
            {
                if (!self::check($result))
                {
                    trigger_error("Method result can be only void, string, integer, array, boolean, float, double or long", E_USER_WARNING);
                    $type = "void";
                    $result = "";
                }
            }
            $query = array
            (
                "event" => $q["event"],
                "t" => $type,
                "r" => $result
            );
            $json = json_encode($query);
            if (!socket_send($this->__socket, self::LengthToString(strlen($json)), 16, 0))
            {
                //
            }
            if (!socket_send($this->__socket, $json, strlen($json), 0))
            {
                //
            }
        }
    }

    /**
     * Unjoins and unblocks parent-thread. ATTENTION! Until you call this method, the parent-thread will be frozen!
     */
    final public function FinishSychnorization() : void
    {
        $query = array
        (
            "act" => "sy"
        );
        $json = json_encode($query);
        if (!socket_send($this->__socket, self::LengthToString(strlen($json)), 16, 0))
        {
            if (!$this->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to access data from threaded class (1)", E_USER_WARNING);
            }
            return;
        }
        if (!socket_send($this->__socket, $json, strlen($json), 0))
        {
            if (!$this->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to access data from threaded class (2)", E_USER_WARNING);
            }
            return;
        }
    }

    /**
     * @param $a
     * @return bool
     * @ignore
     */
    private static function check($a) : bool
    {
        return is_string($a) || is_int($a) || is_array($a) || is_bool($a) || is_float($a) || is_double($a) || is_long($a);
    }

    /**
     * @param int $length
     * @return string
     * @ignore
     */
    private static function LengthToString(int $length) : string
    {
        return str_repeat("0", 16 - strlen($length . "")) . $length;
    }

    /**
     * Creates and starts a new thread of class
     *
     * @param array<int, string> $args Arguments which child-thread will get in `Threaded(array $args)` method
     * @param object $handler Any object that the child thread can access
     * @return Threaded Object which provides information and access to child-thread
     * @throws Exception
     */
    final public static function Run(array $args, object $handler) : ?Threaded
    {
        // For parent thread
        $result = null;
        try
        {
            $result = self::CommonRun($args, false, $handler);
        }
        catch (Exception $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * @param $sock
     * @param int $port
     * @param ParentThreadedObject $pto
     * @throws Exception
     * @ignore
     */
    final public function __setdata($sock, int $port, ParentThreadedObject $pto) : void
    {
        // For child thread
        if ($this->__socket != null)
        {
            throw new Exception("This method is unavailable for user call");
        }
        $this->__socket = $sock;
        $this->__parentsockport = $port;
        $this->pto = $pto;
    }

    /*final public static function FastRun(array $args) : void
    {
        try
        {
            $result = self::CommonRun($args, true);
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }*/

    /**
     * @param array $args
     * @param bool $fast
     * @param object $handler
     * @return Threaded|null
     * @throws Exception
     * @ignore
     */
    final private static function CommonRun(array $args, bool $fast, object $handler) : ?Threaded
    {
        $className = get_called_class();
        $phpCmd = str_replace("\"", "", self::GetPhpCommand());
        if (substr($className, 0, 1) != "\\")
        {
            $className = "\\" . $className;
        }
        if ($className == "\\Threading\\Thread")
        {
            throw new Exception("Cannot run thread '" . $className . "'");
        }
        if (!class_exists($className))
        {
            throw new Exception("Class '" . $className . "' not found");
        }

        $keyI = -1;
        $newArgs = [];
        foreach ($args as $key => $value)
        {
            $keyI++;
            if ($key != $keyI)
            {
                throw new Exception("Arguments must be a list, dictionary given");
            }
            if (!is_string($value))
            {
                throw new Exception("All arguments must have a string type");
            }
            $newArgs[] = $value;
        }

        $pathToPharContent = Application::GetExecutableFileName();
        if (basename($pathToPharContent) == "autoload.php")
        {
            $pathToPharContent = dirname($pathToPharContent) . DIRECTORY_SEPARATOR;
            $pathToPharContent = str_replace("\\", "\\\\", $pathToPharContent);
        }
        else
        {
            $pathToPharContent = str_replace("\\", "/", $pathToPharContent);
            $pathToPharContent .= "/";
            $pathToPharContent = "phar://" . $pathToPharContent;
        }

        $autoload = file_get_contents($pathToPharContent . "autoload.php");
        $noPharCheck = <<<EOT
\$phar_file = \Phar::running(false);
if (\$phar_file == "")
{
    \$file = basename(__FILE__);
    if (\$file != "autoload.php")
    {
        die("The name of autoload file must be 'autoload.php'");
    }
}
EOT;
        $parentPid = getmypid();
        $autoload = str_replace($noPharCheck, "", $autoload);
        $autoload = str_replace("\Application\Application::SetTitle(\$_APP[\"app_name\"]);", "", $autoload);
        $jsonNewArgs = json_encode($newArgs);

        $port = 0;
        $initCode = "";
        $__dm = __DataManager1::GetInstance();
        if (!$fast)
        {
            if ($__dm == null)
            {
                $port = $port = rand(10000, 60000);
                if( !($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
                {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);

                    throw new Exception("Failed to create socket");
                }
                while (true)
                {
                    if (!socket_bind($sock, "127.0.0.1", $port))
                    {
                        $errorcode = socket_last_error();
                        $errormsg = socket_strerror($errorcode);

                        $port = rand(10000, 60000);
                    }
                    else
                    {
                        socket_close($sock);
                        break;
                    }
                }
            }
            else
            {
                $port = $__dm->__GetPort();
            }

            $initCode .= "
if (!(\$sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    \$errorcode = socket_last_error();
    \$errormsg = socket_strerror(\$errorcode);
    exit;
}
\$mypid = getmypid() . '';
if (!socket_connect(\$sock, '127.0.0.1', $port))
{
    \$errorcode = socket_last_error();
    \$errormsg = socket_strerror(\$errorcode);
    if (\$errorcode != 0)
    {
        exit;
    }
}

\$data = array('receivedpid' => \$mypid);
\$json = json_encode(\$data);
\$length = strlen(\$json);
\$lenstr = str_repeat(\"0\", 16 - strlen(\$length . '')) . \$length;

if (!socket_send(\$sock, \$lenstr, 16, 0))
{
    \$errorcode = socket_last_error();
    \$errormsg = socket_strerror(\$errorcode);
         
    if (\$errorcode != 0)
    {
        exit;
    }
}

if (!socket_send(\$sock, \$json, \$length, 0))
{
    \$errorcode = socket_last_error();
    \$errormsg = socket_strerror(\$errorcode);
         
    if (\$errorcode != 0)
    {
        exit;
    }
}
new \\Threading\\__DataManager2(\$sock);
//socket_close(\$sock);
        ";
        }


        $initCode .= "
$className::__SetParentThreadPid($parentPid);
\$thread = new $className(); // " . md5(((rand(-100, 100) + time()) . "") . md5(rand(1, 100))) . "
\$thread->__setdata(\$sock, $port, new \\Threading\\ParentThreadedObject(\$sock, $port, \$thread));
\$thread->Threaded($jsonNewArgs);
";

        $autoload = str_replace("new \Program\Main(\$args);", $initCode, $autoload);
        
        if (strtolower(substr($autoload, 0, 5)) == "<" . "?" . "php")
        {
            $autoload = substr($autoload, 5, strlen($autoload) - 5);
        }
        else if (strtolower(substr($autoload, 0, 2)) == "<" . "?")
        {
            $autoload = substr($autoload, 2, strlen($autoload) - 2);
        }

        $autoload = str_replace("__DIR__", "\"" . $pathToPharContent . "\"", $autoload);
        $autoload = str_replace("\"" . $pathToPharContent . "\" . DIRECTORY_SEPARATOR", "\"" . $pathToPharContent . "\"", $autoload);
        
        $cmd = $phpCmd . " -r \"eval(base64_decode('" . base64_encode($autoload) . "'));\"";

        if (self::IsWindows())
        {
            $startbi = "start /B /I ";
            $redirect = " 1>&2";
            $clearcmd = $cmd;
            $cmd = $startbi . $cmd . $redirect;

            $proc = proc_open($cmd, [], $pipes);
            proc_close($proc);
        }
        else
        {
            //$cmd .= " 1> /proc/" . $parentPid . "/fd/1 & 2> /proc/" . $parentPid . "/fd/2 & 0> /proc/" . $parentPid . "/fd/0 &";
            $cmd .= " 1> /proc/" . $parentPid . "/fd/1 & 2> /proc/" . $parentPid . "/fd/2 &";
            exec($cmd);
        }
        $runtime = null;
        if (!$fast)
        {
            if ($__dm == null)
            {
                if ( !($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
                {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);

                    throw new Exception("Failed to create socket");
                }

                if (!socket_bind($sock, "127.0.0.1", $port))
                {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);

                    throw new Exception("Failed to bind port " . $port);
                }
                $__dm = new __DataManager1($sock, $port);
            }
            else
            {
                $sock = $__dm->__GetSock();
            }

            $thrinfo = $__dm->__Pid();
            $remote_port = $thrinfo[0];
            $childPid = $thrinfo[1];
            $runtime = new Threaded($childPid, $newArgs, $className, $sock, $remote_port, $handler);
        }
        return $runtime;
    }
}