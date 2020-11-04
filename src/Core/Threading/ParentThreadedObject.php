<?php

namespace Threading;

/**
 * Provides access to all methods and properties in parent threaded object
 * @package Threading
 */

class ParentThreadedObject
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
     * @throws \Exception
     * @ignore
     */
    public function __call(string $method, array $args)
    {
        foreach ($args as $key => $value)
        {
            if (!self::check($key) || !self::check($value))
            {
                throw new \Exception("Arguments can be only string, integer, array, boolean, float, double or long");
            }
        }
        $eventId = md5("" . time() . rand(0, 100) . rand(-100, 100) . rand(-1000, 1000) . rand(-1000, 1000));
        $query = array
        (
            "act" => "c",
            "method" => $method,
            "args" => $args,
            "event" => $eventId,
            "pid" => getmypid()
        );
        $json = json_encode($query);
        if (!socket_send($this->__0sock, self::LengthToString(strlen($json)), 16, 0))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to call a function from threaded class (1)", E_USER_WARNING);
            }
            return false;
        }
        if (!socket_send($this->__0sock, $json, strlen($json), 0))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to call a function from threaded class (2)", E_USER_WARNING);
            }
            return false;
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
        $json = json_encode($query);
        if (!socket_send($this->__0sock, self::LengthToString(strlen($json)), 16, 0))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to access data from threaded class (1)", E_USER_WARNING);
            }
            return false;
        }
        if (!socket_send($this->__0sock, $json, strlen($json), 0))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to access data from threaded class (2)", E_USER_WARNING);
            }
            return false;
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
     * @throws \Exception
     * @ignore
     */
    public function __set(string $property, $value)
    {
        if (!self::check($value))
        {
            throw new \Exception("Value can be only string, integer, array, boolean, float, double or long");
        }
        $eventId = md5("" . time() . rand(0, 100) . rand(-100, 100) . rand(-1000, 1000) . rand(-1000, 1000));
        $query = array
        (
            "act" => "s",
            "prop" => $property,
            "val" => $value,
            "event" => $eventId,
            "pid" => getmypid()
        );
        $json = json_encode($query);
        if (!socket_send($this->__0sock, self::LengthToString(strlen($json)), 16, 0))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to access data from threaded class (1)", E_USER_WARNING);
            }
            return;
        }
        if (!socket_send($this->__0sock, $json, strlen($json), 0))
        {
            if (!$this->__0me->IsParentStillRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to access data from threaded class (2)", E_USER_WARNING);
            }
            return;
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