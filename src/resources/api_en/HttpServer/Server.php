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
     * @var int The max length of request. If this length exceeded, the request will be closed immediately. Set -1 for no limit (on current version may work incorrectly).
     */
    public int $MaxRequestLength = 8192;

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
     * * start - triggers when server was started. Signature: `function(HttpServer\Server $server) : void`
     * * shutdown - triggers when server was shutdown. Signature: `function(HttpServer\Server $server) : void`
     * * request - triggers when request was received. Signature: `function(HttpServer\Request $request, HttpServer\Response $response, HttpServer\Server $server) : void`
     * * throwable - triggers on uncaught exception while proceeding request. Signature: function(HttpServer\Request $request, HttpServer\Response $response, Throwable $throwable, HttpServer\Server $server) : void`
     *
     * @param string $eventName
     * @param callable $callback
     * @throws UnknownEventException
     */
    public function On(string $eventName, callable $callback) : void
    {}
    
    /**
     * Returns the array of unclosed requests (to be more precise, unsent responses).
     *
     * @return array<Response>
     */
    public function GetUnsentResponses() : array
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

    /**
     * @return float Count of received bytes
     */
    public function GetBytesReceived() : float
    {}

    /**
     * @return float Count of sent bytes
     */
    public function GetBytesSent() : float
    {}
}