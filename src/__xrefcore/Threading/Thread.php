<?php
declare(ticks = 1);

namespace Threading;

use Application\Application;
use \Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\SystemMethodCallException;
use \Threading\Exceptions\InvalidResultReceivedException;
use \Threading\Exceptions\BadDataAccessException;
use \Threading\Exceptions\AbstractClassThreadException;
use \Threading\Exceptions\NewThreadException;

/**
 * Allows you to initialize classes in another threads. At the same time, this class is using by the child thread to access the parent
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
     * This method calls automatically in child-thread when it was created.
     *
     * @param array<int, string> $args Arguments passed by the parent thread in the static "Run(array $args, object $parentObject)" method
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
     * @return int Port of parent thread
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

    /**
     * @ignore
     */
    final public static function IsWindows() : bool
    {
        return (strtolower(substr(php_uname(), 0, 7)) == "windows");
    }

    /**
     * @return ParentThreadedObject|null
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
        if (!$this->IsParentStillRunning())
            exit;
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
            $query = array
            (
                "event" => $q["event"],
                "t" => $type,
                "r" => $result
            );
            try
            {
                $json = serialize($query);
            }
            catch (\Exception $e)
            {
                $e = new InvalidResultReceivedException($e->getMessage());
                $e->__xrefcoreexception = true;
                throw $e;
            }

            Thread::SendLongQuery($this->__socket, $json, Thread::ADDRESS, $q["port"]);
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
        $json = serialize($query);
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
     * Initializes a parallel class
     *
     * @param array<mixed> $args Arguments which child-thread will get in `Threaded(array $args)` method
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

        $pathToPharContent = Application::GetExecutableFileName();
        $pathToPharContent = str_replace("\\", "/", $pathToPharContent);
        $pathToPharContent .= "/";
        $pathToPharContent = "phar://" . $pathToPharContent;

        $executable = $pathToPharContent . "thread.php";

        $parentPid = getmypid();

        $bigArg = array(
            "__PARENTPID" => $parentPid,
            "__ARGS" => $args,
            "__CLASSNAME" => $className,
            "randomkey" => md5(microtime(true) . "" . rand(-100, 100))
        );
        if ($className != "\\Threading\\__SuperGlobalArrayThread")
        {
            $sga = SuperGlobalArray::GetInstance();

            $bigArg["gaport"] = $sga->GetPort();
            $bigArg["gapid"] = $sga->GetPid();
        }
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

        $bigArg["port"] = $port;
        $bigArg["pathToPharContent"] = $pathToPharContent;

        $cmd = $phpCmd . " -r \"include '" . str_replace("'", "\\'", $executable) . "';\" " . base64_encode(serialize($bigArg));
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


            exec("printf '' > /proc/" . $parentPid . "/fd/1", $o, $t1);
            if ($t1)
            {
                // It seems that parent process doesn't have an own stdout stream. Redirecting output to NULL
                $cmd .= " 1> /dev/null";
            }
            else
            {
                $cmd .= " 1> /proc/" . $parentPid . "/fd/1";
            }

            exec("printf '' > /proc/" . $parentPid . "/fd/2", $o, $t2);
            if ($t2)
            {
                // It seems that parent process doesn't have an own stderr stream. Redirecting output to NULL
                $cmd .= " & 2> /dev/null &";
            }
            else
            {
                $cmd .= " & 2> /proc/" . $parentPid . "/fd/2 &";
            }

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
        $runtime = new Threaded($childPid, $args, $className, $sock, $remote_port, $handler);

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
    public static function ReadLongQuery($sock, string &$query, string &$remote_ip, int &$remote_port, bool $wait = true) : bool
    {
        $query = "";
        $descriptor = "";
        $query = $remote_ip = "";
        $remote_port = -1;
        do
        {
            if (!$wait)
                socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 1000));
            $result = @socket_recvfrom($sock, $buffer, self::BYTES_IN_PACKET + 1, 0, $remote_ip, $remote_port);
            if (!$result)
                return false;
            $query .= substr($buffer, 0, -1);
            $descriptor = substr($buffer, -1);
            $wait = true;
        }
        while ($descriptor != Thread::QUERY_END);
        return true;
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