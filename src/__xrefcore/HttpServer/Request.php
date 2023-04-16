<?php
declare(ticks = 1);

namespace HttpServer;

/**
 * Contains request data
 */

final class Request
{
    public string $RequestMethod, $RequestUri, $RequestUrl, $QueryString = "", $PathInfo, $ServerProtocol, $RemoteAddress, $ServerAddress;
    public int $RequestTime, $ServerPort, $RemotePort, $MasterTime;
    public float $RequestTimeFloat;

    /**
     * @var array<string, mixed>
     */
    public array $Headers = array();

    /**
     * @var array<string, string>
     */
    public array $Get = array();

    /**
     * @var array<string, string>
     */
    public array $Post = array();

    /**
     * @var array<string, string>
     */
    public array $Cookie = array();

    /**
     * @var bool TRUE if any error was occured
     */
    public bool $RequestError = false;

    /**
     * @ignore
     */
    private string $rc;

    /**
     * @ignore
     */
    private Server $server;

    /**
     * @ignore
     */
    public function __construct(array $headers, string $rawcontent, string $name, Server $server, $connect)
    {
        $this->server = $server;
        $name1 = explode(':', $name);
        $remote_addr = $name1[0];
        $remote_port = $name1[1];

        $s = array();
        if (!isset($headers[0]))
        {
            $this->RequestError = true;
            return;
        }
        $h0 = explode(' ', $headers[0]);
        $this->RequestMethod = $h0[0];

        $this->RequestUri = $h0[1];
        if (strpos("?", $h0[1]) !== false)
        {
            $h01 = explode('?', $h0[1]);
            $this->RequestUri = $h01[0];
            array_shift($h01);
            $this->QueryString = implode('?', $h01);
        }
        $this->PathInfo = $this->RequestUri;
        $this->RequestTime = $this->MasterTime = time();
        $this->RequestTimeFloat = microtime(true);
        $this->ServerProtocol = $h0[2];
        $full_address = explode(':', stream_socket_get_name($connect, false));
        $this->ServerPort = intval($full_address[1]);

        $this->RemotePort = $remote_port;
        $this->RemoteAddress = $remote_addr;
        $this->ServerAddress = $full_address[0];
        $this->rc = $rawcontent;
    }

    /**
     * Returns body of request
     *
     * @return string
     */
    public function GetRawContent() : string
    {
        return $this->rc;
    }

    /**
     * Returns an HTTP-server object
     *
     * @return Server
     */
    public function GetServer() : Server
    {
        return $this->server;
    }
}