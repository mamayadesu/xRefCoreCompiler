<?php

namespace HttpServer;

use HttpServer\Exceptions\ServerStartException;
use HttpServer\Exceptions\UnknownEventException;
use Scheduler\AsyncTask;
use Scheduler\NoAsyncTaskParameters;
use Throwable;

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
     * * throwable - calls on uncaught exception while proceeding request. Callback has arguments `\HttpServer\Request`, `\HttpServer\Response` and `\Throwable`
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