<?php
declare(ticks = 1);

namespace Threading;

use CliForms\Exceptions\InvalidArgumentsPassed;
use Threading\Exceptions\BadDataAccessException;
use Threading\Exceptions\BadMethodCallException;
use Threading\Exceptions\InvalidArgumentsPassedException;
use Threading\Exceptions\InvalidResultReceivedException;

/**
 * Provides access to all methods and properties of parent threaded object
 *
 * The "parent threaded object" is the second argument of ThreadName::Run()
 *
 * Attention! If you call method or try to get access to property of class, the child thread will be "frozen" until the parent thread will call "WaitForChildAccess()"
 * @package Threading
 */

final class ParentThreadedObject
{

    /**
     * @var
     * @ignore
     */
    private $__0sock;

    /**
     * @var int
     * @ignore
     */
    private int $__0port;

    /**
     * @var Thread
     * @ignore
     */
    private Thread $__0me;

    /**
     * ParentThreadedObject constructor.
     * @param $sock
     * @param int $port
     * @param Thread $me
     * @ignore
     */
    public function __construct($sock, int $port, Thread $me)
    {
        $this->__0sock = $sock;
        $this->__0port = $port;
        $this->__0me = $me;
    }

    /**
     * @param string $method
     * @param array $args
     * @return bool
     * @throws InvalidArgumentsPassed
     * @throws BadMethodCallException
     * @ignore
     */
    public function __call(string $method, array $args)
    {
        $eventId = md5("" . time() . rand(0, 100) . rand(-100, 100) . rand(-1000, 1000) . rand(-1000, 1000));
        $query = array
        (
            "act" => "c",
            "method" => $method,
            "args" => $args,
            "event" => $eventId,
            "pid" => getmypid()
        );
        try
        {
            $json = serialize($query);
        }
        catch (\Exception $e)
        {
            $e = new InvalidArgumentsPassedException($e->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!Thread::SendLongQuery($this->__0sock, $json, Thread::ADDRESS, $this->__0me->GetParentThreadPort()))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                $e = new BadMethodCallException("Failed to call a function from parent threaded class");
                $e->__xrefcoreexception = true;
                throw $e;
            }
        }
        $__dm = __DataManager2::GetInstance();
        $r = $__dm->__Read();
        while (!isset($r["r"]) || $r["event"] != $eventId)
        {
            $r = $__dm->__Continue();
        }
        $__dm->__Fetch();
        $type = $r["t"];
        if ($type != "void")
        {
            $result = $r["r"];
            return $result;
        }
    }

    /**
     * @param string $property
     * @return bool
     * @throws BadDataAccessException
     * @ignore
     */
    public function __get(string $property)
    {
        $eventId = md5("" . time() . rand(0, 100) . rand(-100, 100) . rand(-1000, 1000) . rand(-1000, 1000));
        $query = array
        (
            "act" => "g",
            "prop" => $property,
            "event" => $eventId,
            "pid" => getmypid()
        );
        try
        {
            $json = serialize($query);
        }
        catch (\Exception $e)
        {
            $e = new InvalidArgumentsPassedException($e->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!Thread::SendLongQuery($this->__0sock, $json, Thread::ADDRESS, $this->__0me->GetParentThreadPort()))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                $e = new BadDataAccessException("Failed to access data from parent threaded class");
                $e->__xrefcoreexception = true;
                throw $e;
            }
        }
        $__dm = __DataManager2::GetInstance();
        $r = $__dm->__Read();
        while (!isset($r["r"]) || $r["event"] != $eventId)
        {
            $r = $__dm->__Continue();
        }
        $__dm->__Fetch();
        $type = $r["t"];
        if ($type != "void")
        {
            $result = $r["r"];
            return $result;
        }
    }

    /**
     * @param string $property
     * @param $value
     * @throws BadMethodCallException
     * @throws InvalidArgumentsPassed
     * @ignore
     */
    public function __set(string $property, $value)
    {
        $eventId = md5("" . time() . rand(0, 100) . rand(-100, 100) . rand(-1000, 1000) . rand(-1000, 1000));
        $query = array
        (
            "act" => "s",
            "prop" => $property,
            "val" => $value,
            "event" => $eventId,
            "pid" => getmypid()
        );
        try
        {
            $json = serialize($query);
        }
        catch (\Exception $e)
        {
            $e = new InvalidArgumentsPassedException($e->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!Thread::SendLongQuery($this->__0sock, $json, Thread::ADDRESS, $this->__0me->GetParentThreadPort()))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                $e = new BadMethodCallException("Failed to call method from threaded parent class");
                $e->__xrefcoreexception = true;
                throw $e;
            }
        }
        $__dm = __DataManager2::GetInstance();
        $r = $__dm->__Read();
        while (!isset($r["r"]) || $r["event"] != $eventId)
        {
            $r = $__dm->__Continue();
        }
        $__dm->__Fetch();
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
     * @param $a
     * @return bool
     * @ignore
     */
    private static function check($a) : bool
    {
        return is_string($a) || is_int($a) || is_array($a) || is_bool($a) || is_float($a) || is_double($a) || is_long($a);
    }
}