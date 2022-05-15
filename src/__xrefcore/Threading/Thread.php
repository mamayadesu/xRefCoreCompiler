<?php

namespace Threading;

use Application\Application;
use \Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\SystemMethodCallException;
use \Threading\Exceptions\InvalidResultReceivedException;
use \Threading\Exceptions\BadDataAccessException;
use \Threading\Exceptions\AbstractClassThreadException;
use \Threading\Exceptions\NewThreadException;

/**
 * Allows you to create new threads. At the same time, this class is used by the child thread to access the parent
 * @package Threading
 */

abstract class Thread
{
    /**
     * @ignore
     */
    private static array $childThreads = [];

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
     * @ignore
     */
    const BYTES_IN_PACKET = 65535;

    /**
     * @ignore
     */
    const QUERY_CONTINUES = "!";

    /**
     * @ignore
     */
    const QUERY_END = "$";

    /**
     * @ignore
     */
    const ADDRESS = "127.0.2.2";

    /**
     * Thread constructor.
     * @throws SystemMethodCallException
     * @ignore
     */
    final function __construct()
    {
        if (self::$parentThreadPid == -1)
        {
            $e = new SystemMethodCallException("Unable to initialize thread class. Use static method Run(array \$args) to create thread.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
    }

    /**
     * This method calls automatically in child-thread when it was created
     *
     * @param array<int, string> $args Arguments passed by the parent thread
     */
    abstract public function Threaded(array $args) : void;

    /**
     * Returns all child threads
     *
     * @return array<Threaded>
     */
    final public static function GetAllChildThreads() : array
    {
        return self::$childThreads;
    }

    /**
     * @return string
     * @ignore
     */
    private static function GetPhpCommand() : string
    {
        return PHP_BINARY;
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
            $e = new SystemMethodCallException("This method is unavailable for user call");
            $e->__xrefcoreexception = true;
            throw $e;
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
                    $e = new InvalidResultReceivedException("Method result can be only void, string, integer, array, boolean, float, double or long");
                    $e->__xrefcoreexception = true;
                    throw $e;
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
                Thread::SendLongQuery($this->__socket, $json, Thread::ADDRESS, $q["port"]);
            }
            else
            {
                Thread::SendLongQuery($this->__socket, $json, Thread::ADDRESS, $q["port"]);
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
        if (!Thread::SendLongQuery($this->__socket, $json, Thread::ADDRESS, $this->__parentsockport))
        {
            if (!$this->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                $e = new BadDataAccessException("Failed to access data from threaded class", E_USER_WARNING);
                $e->__xrefcoreexception = true;
                throw $e;
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
            $e = new SystemMethodCallException("This method is unavailable for user call");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->__socket = $sock;
        $this->__parentsockport = $port;
        $this->pto = $pto;
    }

    /**
     * Creates and starts a new thread of class
     *
     * @param array<int, string> $args Arguments which child-thread will get in `Threaded(array $args)` method
     * @param object $handler Any object that the child thread can access
     * @return Threaded Object which provides information and access to child-thread
     * @throws AbstractClassThreadException
     * @throws InvalidArgumentsPassedException
     * @throws NewThreadException|SystemMethodCallException
     */
    final public static function Run(array $args, object $handler) : ?Threaded
    {
        if (!MAIN_THREAD)
        {
            $e = new NewThreadException("You can't run new child thread from another child thread");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $microtime = microtime(true);
        if (DEV_MODE) echo "[THREAD] Run called [" . round(microtime(true) - $microtime, 6) . "]\n";
        $className = get_called_class();
        $phpCmd = str_replace("\"", "", self::GetPhpCommand());
        if (substr($className, 0, 1) != "\\")
        {
            $className = "\\" . $className;
        }
        if ($className == "\\Threading\\Thread")
        {
            $e = new AbstractClassThreadException("Your class have to inherit this class (Threading\Thread). Cannot initialize this class in separated thread.");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        $newArgs = [];
        if (DEV_MODE) echo "[THREAD] Reading args [" . round(microtime(true) - $microtime, 6) . "]\n";
        foreach ($args as $key => $value)
        {
            if (!self::check($value) || !self::check($key))
            {
                $e = new InvalidArgumentsPassedException("Arguments can be only void, string, integer, array, boolean, float, double or long");
                $e->__xrefcoreexception = true;
                throw $e;
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
        if (DEV_MODE) echo "[THREAD] Getting autoload [" . round(microtime(true) - $microtime, 6) . "]\n";
        $autoload = file_get_contents($pathToPharContent . "thread.php");
        $parentPid = getmypid();
        $jsonNewArgs = json_encode($newArgs);

        if (DEV_MODE) echo "[THREAD] Parsing autoload [" . round(microtime(true) - $microtime, 6) . "]\n";
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
        if (DEV_MODE) echo "[THREAD] Parsed [" . round(microtime(true) - $microtime, 6) . "]\n";
        $port = 0;
        $__dm = __DataManager1::GetInstance();
        if ($__dm == null)
        {
            $port = rand(10000, 60000);
            if (DEV_MODE) echo "[THREAD] Creating socket for check port [" . round(microtime(true) - $microtime, 6) . "]\n";
            if( !($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
            {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                $e = new NewThreadException("Failed to create socket");
                $e->__xrefcoreexception = true;
                throw $e;
            }
            while (true)
            {
                if (!@socket_bind($sock, Thread::ADDRESS, $port))
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

        if (strtolower(substr($autoload, 0, 5)) == "<" . "?" . "php")
        {
            $autoload = substr($autoload, 5, strlen($autoload) - 5);
        }
        else if (strtolower(substr($autoload, 0, 2)) == "<" . "?")
        {
            $autoload = substr($autoload, 2, strlen($autoload) - 2);
        }
        if (DEV_MODE) echo "[THREAD] Continuing parsing [" . round(microtime(true) - $microtime, 6) . "]\n";
        $autoload = str_replace("\$port = 0x0000", "\$port = " . $port, $autoload);
        $autoload = str_replace("__DIR__", "\"" . $pathToPharContent . "\"", $autoload);
        $autoload = str_replace("\"" . $pathToPharContent . "\" . DIRECTORY_SEPARATOR", "\"" . $pathToPharContent . "\"", $autoload);
        $autoload = str_replace(["\n", "\r", "    "], ["", "", ""], $autoload);
        if (DEV_MODE) echo "[THREAD] Parsed again [" . round(microtime(true) - $microtime, 6) . "]\n";
        $cmd = $phpCmd . " -r \"eval(base64_decode('" . base64_encode($autoload) . "'));\"";
        if (self::IsWindows())
        {
            $startbi = "start /B /I ";
            $redirect = " 1>&2";
            $clearcmd = $cmd;
            $cmd = $startbi . $cmd . $redirect;
            if (DEV_MODE) echo "[THREAD] Starting thread [" . round(microtime(true) - $microtime, 6) . "]\n";
            $proc = proc_open($cmd, [], $pipes);
            proc_close($proc);
            if (DEV_MODE) echo "[THREAD] Started [" . round(microtime(true) - $microtime, 6) . "]\n";
        }
        else
        {
            if (DEV_MODE) echo "[THREAD] Starting thread [" . round(microtime(true) - $microtime, 6) . "]\n";
            $cmd .= " 1> /proc/" . $parentPid . "/fd/1 & 2> /proc/" . $parentPid . "/fd/2 &";
            exec($cmd);
            if (DEV_MODE) echo "[THREAD] Started [" . round(microtime(true) - $microtime, 6) . "]\n";
        }

        $runtime = null;
        if ($__dm == null)
        {
            if (DEV_MODE) echo "[THREAD] Creating socket [" . round(microtime(true) - $microtime, 6) . "]\n";
            if ( !($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
            {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                $e = new NewThreadException("Failed to create socket");
                $e->__xrefcoreexception = true;
                throw $e;
            }
            if (DEV_MODE) echo "[THREAD] Binding port [" . round(microtime(true) - $microtime, 6) . "]\n";
            if (!socket_bind($sock, Thread::ADDRESS, $port))
            {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);

                $e = new NewThreadException("Failed to bind port " . $port);
                $e->__xrefcoreexception = true;
                throw $e;
            }
            $__dm = new __DataManager1($sock, $port);
            if (DEV_MODE) echo "[THREAD] OK [" . round(microtime(true) - $microtime, 6) . "]\n";
        }
        else
        {
            $sock = $__dm->__GetSock();
        }
        if (DEV_MODE) echo "[THREAD] Getting thrinfo [" . round(microtime(true) - $microtime, 6) . "]\n";
        $thrinfo = $__dm->__Pid();
        $remote_port = $thrinfo[0];
        $childPid = $thrinfo[1];
        if (DEV_MODE) echo "[THREAD] Creating object [" . round(microtime(true) - $microtime, 6) . "]\n";
        $runtime = new Threaded($childPid, $newArgs, $className, $sock, $remote_port, $handler);

        self::$childThreads[] = $runtime;
        if (DEV_MODE) echo "[THREAD] Everything done! [" . round(microtime(true) - $microtime, 6) . "]\n";
        return $runtime;
    }

    /**
     * @ignore
     */
    public static function SeparateStringToPackets(string $data) : array
    {
        $b = self::BYTES_IN_PACKET;
        $result = [];
        $packet = "";
        while (true)
        {
            if (strlen($data) <= $b)
            {
                $result[] = $data . self::QUERY_END;
                break;
            }
            else
            {
                $packet = substr($data, 0, $b);
                $data = substr($data, $b);
                $result[] = $packet . self::QUERY_CONTINUES;
            }
        }
        return $result;
    }

    /**
     * @ignore
     */
    public static function ReadLongQuery($sock, &$query, &$remote_ip, &$remote_port) : void
    {
        $query = "";
        $descriptor = "";
        do
        {
            @socket_recvfrom($sock, $buffer, self::BYTES_IN_PACKET + 1, 0, $remote_ip, $remote_port);
            $query .= substr($buffer, 0, -1);
            $descriptor = substr($buffer, -1);
        }
        while ($descriptor != Thread::QUERY_END);
    }

    /**
     * @ignore
     */
    public static function SendLongQuery($sock, string $data, string $address, int $port) : bool
    {
        $packets = self::SeparateStringToPackets($data);
        foreach ($packets as $packet)
        {
            if (!socket_sendto($sock, $packet, strlen($packet), 0, $address, $port))
                return false;
        }
        return true;
    }
}