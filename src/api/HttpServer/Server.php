<?php

namespace HttpServer;

use HttpServer\Exceptions\ServerStartException;
use HttpServer\Exceptions\UnknownEventException;
use Scheduler\AsyncTask;

/**
 * HTTP-server
 *
 * WARNING!!! This package IS EXPERIMENTAL! Use this AT OWN RISK!
 */

final class Server
{

    /**
     * @var int Timeout of data reading when connection accepted
     */
    public int $DataReadTimeout = 5;
    
    /**
     * @var bool Enables client non-block mode. It means that server won't wait when client will receive data. WARNING!!! USE THIS PARAMETER CAREFULLY! IT CAN MAKE SERVER BEHAVIOR NON-OBVIOUS OR UNPREDICTABLE! IF YOU WANT TO SEND A BIG DATA, SEND EVERY ~64KB
     */
    public bool $ClientNonBlockMode = false;

    /**
     * Server constructor.
     * @param string $addr HTTP-server IP-address
     * @param int $port Port
     */
    public function __construct(string $addr, int $port = 8080)
    {}

    /**
     * Add new event
     *
     * Supported events:
     * * start - calls when server was started. Callback has argument `\HttpServer\Server`
     * * shutdown - calls when server was shutdown. Callback has argument `\HttpServer\Server`
     * * request - calls when request was received. Callback has arguments `\HttpServer\Request` and `\HttpServer\Response`
     *
     * @param string $eventName
     * @param callable $callback
     * @throws UnknownEventException
     */
    public function On(string $eventName, callable $callback) : void
    {}

    /**
     * Run server
     *
     * @throws ServerStartException
     */
    public function Start(bool $async = false) : void
    {}

    /**
     * Shutdown server
     */
    public function Shutdown() : void
    {}
}