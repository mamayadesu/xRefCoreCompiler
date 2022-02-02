<?php

namespace Threading;

use Application\Application;
use \Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\SystemMethodCallException;
use \Threading\Exceptions\InvalidResultReceivedException;
use \Threading\Exceptions\BadDataAccessException;
use \Threading\Exceptions\AbstractClassThreadException;
use \Threading\Exceptions\ClassNotFoundException;
use \Threading\Exceptions\NewThreadException;

/**
 * Allows you to create new threads. At the same time, this class is used by the child thread to access the parent
 * @package Threading
 */

class Thread
{
    /**
     * @ignore
     */
    private static array $childThreads = [];

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
     * @throws SystemMethodCallException
     * @ignore
     */
    final function __construct()
    {
        if (self::$parentThreadPid == -1)
        {
            throw new SystemMethodCallException("Unable to initialize thread class. Use static method Run(array \$args) to create thread.");
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
     * Returns all child threads
     *
     * @return array<Threaded>
     */
    public static function GetAllChildThreads() : array
    {
        return self::$childThreads;
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
     * @throws SystemMethodCallException
     * @ignore
     */
    final public static function __SetParentThreadPid(int $parentThread) : void
    {
        // For child thread
        if (self::$parentThreadPid != -1)
        {
            throw new SystemMethodCallException("This method is unavailable for user call");
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
     * Returns a port of parent thread
     *
     * @return int PID of parent thread
     */
    final function GetParentThreadPort() : int
    {
        // For child thread
        return $this->__parentsockport;
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

    final public static function IsWindows() : bool
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
     * @throws InvalidResultReceivedException
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
                    throw new InvalidResultReceivedException("Method result can be only void, string, integer, array, boolean, float, double or long");
                }
            }
            $query = array
            (
                "event" => $q["event"],
                "t" => $type,
                "r" => $result
            );
            $json = json_encode($query);

            if (get_class($this) == "Threading\\__SuperGlobalArrayThread")
            {
                socket_sendto($this->__socket, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $q["port"]);
                socket_sendto($this->__socket, $json, strlen($json), 0, "127.0.0.1", $q["port"]);
            }
            else
            {
                socket_sendto($this->__socket, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $q["port"]);
                socket_sendto($this->__socket, $json, strlen($json), 0, "127.0.0.1", $q["port"]);
            }
        }
    }

    /**
     * Unjoins and unblocks parent-thread. ATTENTION! Until you call this method, the parent-thread will be frozen!
     * @throws BadDataAccessException
     */
    final public function FinishSychnorization() : void
    {
        $query = array
        (
            "act" => "sy"
        );
        $json = json_encode($query);
        if (!socket_sendto($this->__socket, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $this->__parentsockport))
        {
            if (!$this->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                throw new BadDataAccessException("Failed to access data from threaded class");
            }
        }
        if (!socket_sendto($this->__socket, $json, strlen($json), 0, "127.0.0.1", $this->__parentsockport))
        {
            if (!$this->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                throw new BadDataAccessException("Failed to access data from threaded class", E_USER_WARNING);
            }
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
     * @throws AbstractClassThreadException
     * @throws ClassNotFoundException
     * @throws InvalidArgumentsPassedException
     * @throws NewThreadException
     */
    final public static function Run(array $args, object $handler) : ?Threaded
    {
        // For parent thread
        $result = null;
        try
        {
            $result = self::CommonRun($args, false, $handler);
        }
        catch (AbstractClassThreadException $e)
        {
            throw $e;
        }
        catch (ClassNotFoundException $e)
        {
            throw $e;
        }
        catch (InvalidArgumentsPassedException $e)
        {
            throw $e;
        }
        catch (NewThreadException $e)
        {
            throw $e;
        }
        return $result;
    }

    /**
     * @param $sock
     * @param int $port
     * @param ParentThreadedObject $pto
     * @throws SystemMethodCallException
     * @ignore
     */
    final public function __setdata($sock, int $port, ParentThreadedObject $pto) : void
    {
        // For child thread
        if ($this->__socket != null)
        {
            throw new SystemMethodCallException("This method is unavailable for user call");
        }
        $this->__socket = $sock;
        $this->__parentsockport = $port;
        $this->pto = $pto;
    }

    /**
     * @param array $args
     * @param bool $fast
     * @param object $handler
     * @return Threaded|null
     * @throws AbstractClassThreadException
     * @throws ClassNotFoundException
     * @throws InvalidArgumentsPassedException
     * @throws NewThreadException
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
            throw new AbstractClassThreadException("Cannot run thread '" . $className . "'");
        }
        if (!class_exists($className))
        {
            throw new ClassNotFoundException("Class '" . $className . "' not found");
        }

        $newArgs = [];
        foreach ($args as $key => $value)
        {
            if (!self::check($value) || !self::check($key))
            {
                throw new InvalidArgumentsPassedException("Arguments can be only void, string, integer, array, boolean, float, double or long");
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
        $autoload = file_get_contents($pathToPharContent . "thread.php");
        $parentPid = getmypid();
        $jsonNewArgs = json_encode($newArgs);

        if ($className != "\\Threading\\__SuperGlobalArrayThread")
        {
            $sga = SuperGlobalArray::GetInstance();

            $autoload = str_replace("\$gaport = 0x0000", "\$gaport = " . $sga->GetPort(), $autoload);
            $autoload = str_replace("\$gapid = 0x0000", "\$gapid = " . $sga->GetPid(), $autoload);
        }
        $autoload = str_replace("\$__PARENTPID = 0x0000", "\$__PARENTPID = " . $parentPid, $autoload);
        $autoload = str_replace("\$__JSONNEWARGS = []", "\$__JSONNEWARGS = " . $jsonNewArgs, $autoload);
        $autoload = str_replace("\$__CLASSNAME = \"\"", "\$__CLASSNAME = \"" . $className . "\"", $autoload);
        $autoload = str_replace("{RANDOMKEY}", md5(microtime(true) . "" . rand(-100, 100)), $autoload);

        $port = 0;
        $initCode = "";
        $__dm = __DataManager1::GetInstance();
        if (!$fast)
        {
            if ($__dm == null)
            {
                $port = rand(10000, 60000);
                if( !($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
                {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);

                    throw new NewThreadException("Failed to create socket");
                }
                while (true)
                {
                    if (!@socket_bind($sock, "127.0.0.1", $port))
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
        }
        if (strtolower(substr($autoload, 0, 5)) == "<" . "?" . "php")
        {
            $autoload = substr($autoload, 5, strlen($autoload) - 5);
        }
        else if (strtolower(substr($autoload, 0, 2)) == "<" . "?")
        {
            $autoload = substr($autoload, 2, strlen($autoload) - 2);
        }

        $autoload = str_replace("\$port = 0x0000", "\$port = " . $port, $autoload);
        $autoload = str_replace("__DIR__", "\"" . $pathToPharContent . "\"", $autoload);
        $autoload = str_replace("\"" . $pathToPharContent . "\" . DIRECTORY_SEPARATOR", "\"" . $pathToPharContent . "\"", $autoload);
        $autoload = str_replace(["\n", "\r", "    "], ["", "", ""], $autoload);
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

                    throw new NewThreadException("Failed to create socket");
                }

                if (!socket_bind($sock, "127.0.0.1", $port))
                {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);

                    throw new NewThreadException("Failed to bind port " . $port);
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
        self::$childThreads[] = $runtime;
        return $runtime;
    }
}