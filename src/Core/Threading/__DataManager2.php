<?php

namespace Threading;

/**
 * Class __DataManager2
 * @package Threading
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

    public function __construct($sock)
    {
        if (self::$instance != null)
        {
            throw new \Exception("Do not use this class");
        }
        $this->sock = $sock;
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
        if (!isset($this->unreadData[$this->currentI]))
        {
            socket_recvfrom($this->sock, $queryLength1, 16, 0, $remote_ip, $remote_port);
            $queryLength = intval($queryLength1);
            socket_recvfrom($this->sock, $query, $queryLength, 0, $remote_ip, $remote_port);
            $data = json_decode($query, true);
            $this->Add(array("data" => $data));

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
        $json = json_encode($query);
        $length = strlen($json);
        $len = str_repeat("0", 16 - strlen($length . "")) . $length;
        @socket_send($this->sock, $len, 16, 0);
        @socket_send($this->sock, $json, $length, 0);
        @socket_close($this->sock);
    }
}