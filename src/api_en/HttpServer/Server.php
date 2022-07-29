<?php

namespace HttpServer;

use HttpServer\Exceptions\ServerStartException;
use HttpServer\Exceptions\UnknownEventException;
use Scheduler\AsyncTask;
use Scheduler\NoAsyncTaskParameters;
use Throwable;

/**
 * Built-in, completely pure HTTP server. It can work both in synchronous and asynchronous mode. Request processing is entirely up to you.
 */

final class Server
{
    /**
     * @var int Timeout of data reading when connection accepted
     */
    public int $DataReadTimeout = 5;

    /**
     * Server constructor.
     * @param string $address Listening IP-address. Pass "0.0.0.0" to use all available interfaces
     * @param int $port Port
     */
    public function __construct(string $address = "0.0.0.0", int $port = 8080)
    {}

    /**
     * Subscribe to event
     *
     * Supported events:
     * * start - triggers when server was started. Callback has argument `\HttpServer\Server`
     * * shutdown - triggers when server was shutdown. Callback has argument `\HttpServer\Server`
     * * request - triggers when request was received. Callback has arguments `\HttpServer\Request` and `\HttpServer\Response`
     * * throwable - triggers on uncaught exception while proceeding request. Callback has arguments `\HttpServer\Request`, `\HttpServer\Response` and `\Throwable`
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
     * @param bool $async If you set TRUE, server will be started in background mode. It means this method won't block code execution after this method
     * @return void
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