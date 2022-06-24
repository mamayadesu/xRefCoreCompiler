<?php
declare(ticks = 1);

namespace Threading;

use Threading\Exceptions\SystemMethodCallException;

/**
 * Class __DataManager2
 * @ignore
 */

final class __DataManager2
{
    private $sock = null;

    /*
     * {
     *     "data": array
     * }
     */
    private array $unreadData = array();
    private int $currentI = -1;
    private static ?__DataManager2 $instance = null;
    private int $parentPort;

    public function __construct($sock, int $parentPort)
    {
        if (self::$instance != null)
        {
            $e = new SystemMethodCallException("System class is not allowed for initializing");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->sock = $sock;
        $this->parentPort = $parentPort;
        self::$instance = $this;
    }

    public static function GetInstance() : ?__DataManager2
    {
        return self::$instance;
    }

    public function __GetSock()
    {
        return $this->sock;
    }

    public function __Read(bool $fromStart = true) : array
    {
        if ($fromStart)
        {
            $this->currentI = -1;
        }
        $this->currentI++;
        $p = 0;
        $data = array();
        $info = array();
        $query = "";
        $remote_ip = "";
        $remote_port = 0;
        if (!isset($this->unreadData[$this->currentI]))
        {
            Thread::ReadLongQuery($this->sock, $query, $remote_ip, $remote_port);
            $data = unserialize($query);
            $this->Add(array("data" => $data));
            if ($data == null)
            {
                exit;
            }
            return $data;
        }
        else
        {
            $info = $this->unreadData[$this->currentI];
            $data = $info["data"];

            return $data;
        }
    }

    public function __Continue() : array
    {
        return $this->__Read(false);
    }

    private function Add(array $el, int $idx = -1) : void
    {
        if ($idx < 0)
        {
            $this->unreadData[] = $el;
        }
        else
        {
            $newArr = array();
            $i = -1;
            foreach ($this->unreadData as $row)
            {
                $i++;
                if ($i == $idx)
                {
                    $newArr[$i] = $el;
                }
                else
                {
                    $newArr[$i] = $row;
                }
            }
        }
    }

    public function __Fetch() : array
    {
        $result = $this->unreadData[$this->currentI];
        $newArr = array();
        $this->unreadData = array();
        $i = -1;
        foreach ($this->unreadData as $row)
        {
            if ($i != $this->currentI)
            {
                $i++;
                $newArr[$i] = $row;
            }
            else
            {
                $this->currentI = -1;
            }
        }
        $this->unreadData = $newArr;
        return $result["data"];
    }

    public function __destruct()
    {
        $query = array
        (
            "act" => "threadstop",
            "pid" => getmypid()
        );
        $json = serialize($query);
        $length = strlen($json);
        $len = str_repeat("0", 16 - strlen($length . "")) . $length;
        @Thread::SendLongQuery($this->sock, $json, Thread::ADDRESS, $this->parentPort);
        @socket_close($this->sock);
    }
}