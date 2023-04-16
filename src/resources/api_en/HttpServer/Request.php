<?php

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
     * Returns body of request
     *
     * @return string
     */
    public function GetRawContent() : string
    {}

    /**
     * Returns an HTTP-server object
     *
     * @return Server
     */
    public function GetServer() : Server
    {}
}