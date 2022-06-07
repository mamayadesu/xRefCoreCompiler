<?php

namespace Threading;

use \Threading\Exceptions\AccessToClosedThreadException;
use Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\InvalidResultReceivedException;
use \Threading\Exceptions\BadDataAccessException;

/**
 * Provides information and access to child thread
 * @package Threading
 */

final class Threaded
{
    /**
     * @var int
     * @ignore
     */
    private int $child, $port;

    /**
     * @var array
     * @ignore
     */
    private array $args;

    /**
     * @var string
     * @ignore
     */
    private string $className, $command;

    /**
     * @var ChildThreadedObject|null
     * @ignore
     */
    private ?ChildThreadedObject $cto;

    /**
     * @var null
     * @ignore
     */
    private $sock = null;

    /**
     * @var object
     * @ignore
     */
    private object $handler;

    /**
     * @var bool
     * @ignore
     */
    private bool $threadshutdown = false;

    /**
     * Threaded constructor.
     * @param int $childPid
     * @param array $args
     * @param string $className
     * @param $sock
     * @param int $port
     * @param object $handler
     * @ignore
     */
    public function __construct(int $childPid, array $args, string $className, $sock, int $port, object $handler)
    {
        $this->child = $childPid;
        $this->args = $args;
        $this->className = $className;
        $this->cto = new ChildThreadedObject($sock, $port, $this);
        $this->sock = $sock;
        $this->handler = $handler;
        $this->port = $port;
    }

    /**
     * Provides access to public methods and properties of threaded child class
     *
     * @return ChildThreadedObject|null
     */
    public function GetChildThreadedObject() : ?ChildThreadedObject
    {
        return $this->cto;
    }

    /**
     * Returns PID of child thread
     *
     * @return int PID of child thread
     */
    public function GetChildPid() : int
    {
        return $this->child;
    }

    /**
     * Returns port of child thread
     *
     * @return int Port of child thread
     */
    public function GetChildPort() : int
    {
        return $this->port;
    }

    /**
     * Returns list of arguments passed by the parent thread
     *
     * @return array<int, string> Arguments passed by the parent thread
     */
    public function GetArguments() : array
    {
        return $this->args;
    }

    /**
     * Returns name of threaded class
     *
     * @return string Full name of threaded class
     */
    public function GetClassName() : string
    {
        return $this->className;
    }

    /**
     * Returns TRUE if child thread still running
     *
     * @return bool TRUE if thread still running. FALSE thread is closed by any reason
     */
    public function IsRunning() : bool
    {
        if ($this->threadshutdown)
        {
            return false;
        }
        $result = false;
        if (Thread::IsWindows())
        {
            exec("tasklist /FI \"PID eq " . $this->child . "\" /FO csv | find /c /v \"\"", $output1);
            $output = rtrim(str_replace(" ", "", $output1[0]));
            $linesCount = intval($output);
            $result = $linesCount > 1;
        }
        else
        {
            $result = file_exists("/proc/" . $this->child);
        }
        if (!$result)
        {
            $this->threadshutdown = true;
        }
        return $result;
    }

    /**
     * Waits for the child thread to begin interacting with the parent thread. The parent thread will be frozen and wait for the child thread to finish synchronizing
     *
     * @return void
     * @throws AccessToClosedThreadException
     * @throws InvalidResultReceivedException
     */
    public function WaitForChildAccess()
    {
        if ($this->threadshutdown || !$this->IsRunning())
        {
            $e = new AccessToClosedThreadException("Cannot synchronize with thread, because thread is closed", E_USER_WARNING);
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $__dm = __DataManager1::GetInstance();
        while (true)
        {
            $q = $__dm->__Read($this->port);
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
            
            $result = null;
            switch ($q["act"])
            {
                case "c":
                    $result = call_user_func_array(array($this->handler, $q["method"]), $q["args"]);
                    break;
                    
                case "g":
                    $result = $this->handler->{$q["prop"]};
                    break;

                case "s":
                    $this->handler->{$q["prop"]} = $q["val"];
                    break;

                case "threadstop":
                    $this->threadshutdown = true;
                    return;

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
            Thread::SendLongQuery($this->sock, $json, Thread::ADDRESS, $this->port);
        }
    }

    /**
     * Stop synchronization with child thread
     * @throws BadDataAccessException
     */
    public function FinishSychnorization() : void
    {
        $query = array
        (
            "act" => "sy"
        );
        $json = serialize($query);
        if (!Thread::SendLongQuery($this->sock, $json, Thread::ADDRESS, $this->port))
        {
            if ($this->IsRunning())
            {
                $e = new BadDataAccessException("Failed to access data from threaded class");
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
     * Kills child thread
     */
    public function Kill() : void
    {
        if ($this->sock == null)
        {
            return;
        }
        if (Thread::IsWindows())
        {
            pclose(popen("taskkill /F /PID " . $this->child, "r"));
        }
        else
        {
            exec("kill -9 " . $this->child);
        }
    }
}