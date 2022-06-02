<?php

namespace Threading;

use Threading\Exceptions\SystemMethodCallException;

/**
 * Class __DataManager1
 * @package Threading
 * @ignore
 */

final class __DataManager1
{
    private int $port;
    private $sock = null;

    /*
     * {
     *     "port": ...
     *     "data": array
     * }
     */
    private array $unreadData = array();
    private int $currentI = -1;
    private int $currentPort = 0;
    private static ?__DataManager1 $instance = null;

    public function __construct($sock, int $port)
    {
        if (self::$instance != null)
        {
            $e = new SystemMethodCallException("System class is not allowed for initializing");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->sock = $sock;
        $this->port = $port;
        self::$instance = $this;
    }

    public static function GetInstance() : ?__DataManager1
    {
        return self::$instance;
    }

    public function __GetSock()
    {
        return $this->sock;
    }

    public function __GetPort() : int
    {
        return $this->port;
    }

    public function __Read(int $port, bool $fromStart = true) : array
    {
        if ($fromStart)
        {
            $this->currentI = -1;
        }
        $this->currentPort = $port;
        $this->currentI++;
        $p = 0;
        $data = array();
        $info = array();
        if (!isset($this->unreadData[$this->currentI]))
        {
            do
            {
                Thread::ReadLongQuery($this->sock, $query, $remote_ip, $remote_port);
                $p = $remote_port;
                $data = unserialize($query);
                $this->Add(array("port" => $p, "data" => $data));
            }
            while ($p != $port);

            return $data;
        }
        else
        {
            do
            {
                $info = $this->unreadData[$this->currentI];
                $p = $info["port"];
                $data = $info["data"];
            }
            while ($p != $port);

            return $data;
        }
    }

    public function __Continue() : array
    {
        return $this->__Read($this->currentPort, false);
    }

    /*
     * 0 - port
     * 1 - pid
     */
    public function __Pid() : array/*<int>*/
    {
        $this->currentI = -1;
        $data = array();
        $info = array();
        foreach ($this->unreadData as $row)
        {
            $this->currentI++;
            if (isset($row["data"]["receivedpid"]))
            {
                return $this->__Fetch();
            }
        }
        $queryLength1 = $remote_ip = "";
        $queryLength = $remote_port = 0;
        $result = [];
        while (true)
        {
            $this->currentI++;
            Thread::ReadLongQuery($this->sock, $query, $remote_ip, $remote_port);
            $p = $remote_port;
            $data = unserialize($query);
            $this->Add(array("port" => $p, "data" => $data));

            if (isset($data["receivedpid"]))
            {
                $this->__Fetch();
                $result = [$p, $data["receivedpid"]];
                break;
            }
        }
        return $result;
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
        @socket_close($this->sock);
    }
}