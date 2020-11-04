<?php

namespace Threading;

/**
 * Provides access to all methods and properties in child threaded class
 * @package Threading
 */

class ChildThreadedObject
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
     * @var Threaded
     * @ignore
     */
    private Threaded $__0thread;

    /**
     * ChildThreadedObject constructor.
     * @param $sock
     * @param int $port
     * @param Threaded $thread
     * @ignore
     */
    public function __construct($sock, int $port, Threaded $thread)
    {
        $this->__0sock = $sock;
        $this->__0port = $port;
        $this->__0thread = $thread;
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
        $eventId = md5("" . time() . rand(0, 100) . rand(-100, 100));
        $query = array
        (
            "act" => "c",
            "method" => $method,
            "args" => $args,
            "event" => $eventId,
            "pid" => getmypid()
        );
        $json = json_encode($query);
        if (!socket_sendto($this->__0sock, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $this->__0port))
        {
            if (!$this->__0thread->IsRunning())
            {
                trigger_error("Unable to call a function from threaded class, because thread stopped (1)", E_USER_WARNING);
            }
            else
            {
                trigger_error("Failed to call a function from threaded class (1)", E_USER_WARNING);
            }
            return false;
        }
        if (!socket_sendto($this->__0sock, $json, strlen($json), 0, "127.0.0.1", $this->__0port))
        {
            if (!$this->__0thread->IsRunning())
            {
                trigger_error("Unable to call a function from threaded class, because thread stopped (1)", E_USER_WARNING);
            }
            else
            {
                trigger_error("Failed to call a function from threaded class (1)", E_USER_WARNING);
            }
            return false;
        }
        $__dm = __DataManager1::GetInstance();
        $r = $__dm->__Read($this->__0port);
        while ((!isset($r["r"]) || $r["event"] != $eventId) && (!isset($r["act"]) || $r["act"] != "threadstop"))
        {
            $r = $__dm->__Continue();
        }
        $__dm->__Fetch();
        if (isset($r["act"]) && $r["act"] == "threadstop")
        {
            trigger_error("Unable to call a function in threaded class, because thread stopped (3)", E_USER_WARNING);
            return false;
        }
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
        if (!socket_sendto($this->__0sock, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $this->__0port))
        {
            if (!$this->__0thread->IsRunning())
            {
                trigger_error("Unable to access data from threaded class, because thread stopped (1)", E_USER_WARNING);
            }
            else
            {
                trigger_error("Failed to access data from threaded class (1)", E_USER_WARNING);
            }
            return false;
        }
        if (!socket_sendto($this->__0sock, $json, strlen($json), 0, "127.0.0.1", $this->__0port))
        {
            if (!$this->__0thread->IsRunning())
            {
                trigger_error("Unable to access data from threaded class, because thread stopped (2)", E_USER_WARNING);
            }
            else
            {
                trigger_error("Failed to access data from threaded class (2)", E_USER_WARNING);
            }
            return false;
        }
        $__dm = __DataManager1::GetInstance();
        $r = $__dm->__Read($this->__0port);
        while ((!isset($r["r"]) || $r["event"] != $eventId) && (!isset($r["act"]) || $r["act"] != "threadstop"))
        {
            $r = $__dm->__Continue();
        }
        $__dm->__Fetch();
        if (isset($r["act"]) && $r["act"] == "threadstop")
        {
            trigger_error("Unable to get property from threaded class, because thread stopped (3)", E_USER_WARNING);
            return false;
        }
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
        if (!socket_sendto($this->__0sock, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $this->__0port))
        {
            if (!$this->__0thread->IsRunning())
            {
                trigger_error("Unable to access data from threaded class, because thread stopped (1)", E_USER_WARNING);
            }
            else
            {
                trigger_error("Failed to access data from threaded class (1)", E_USER_WARNING);
            }
            return;
        }
        if (!socket_sendto($this->__0sock, $json, strlen($json), 0, "127.0.0.1", $this->__0port))
        {
            if (!$this->__0thread->IsRunning())
            {
                trigger_error("Unable to access data from threaded class, because thread stopped (2)", E_USER_WARNING);
            }
            else
            {
                trigger_error("Failed to access data from threaded class (2)", E_USER_WARNING);
            }
            return;
        }
        $__dm = __DataManager1::GetInstance();
        $r = $__dm->__Read($this->__0port);
        while ((!isset($r["r"]) || $r["event"] != $eventId) && (!isset($r["act"]) || $r["act"] != "threadstop"))
        {
            $r = $__dm->__Continue();
        }
        $__dm->__Fetch();
        if (isset($r["act"]) && $r["act"] == "threadstop")
        {
            trigger_error("Unable to set property in threaded class, because thread stopped (3)", E_USER_WARNING);
            return;
        }
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