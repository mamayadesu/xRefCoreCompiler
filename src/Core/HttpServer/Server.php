<?php

namespace HttpServer;

use HttpServer\Exceptions\ServerStartException;

/**
 * HTTP-server
 *
 * WARNING!!! This package IS EXPERIMENTAL! Use this AT OWN RISK!
 */

class Server
{
    /**
     * @ignore
     */
    private string $address;

    /**
     * @ignore
     */
    private int $port;

    /**
     * @ignore
     */
    private array $registeredEvents = array();

    /**
     * @ignore
     */
    private bool $shutdownWasCalled = false;

    /**
     * @ignore
     */
    private $socket = null;

    /**
     * @ignore
     */

    /**
     * Server constructor.
     * @param string $addr HTTP-server IP-address
     * @param int $port Port
     */
    public function __construct(string $addr, int $port = 8080)
    {
        $this->address = $addr;
        $this->port = $port;
        $this->on("start", function (Server $server) { });
        $this->on("shutdown", function (Server $server) { });
        $this->on("request", function (Request $req, Response $resp) { });
    }

    /**
     * Add new event
     *
     * Supported events:
     * * start - calls when server was started
     * * shutdown - calls when server was shutdown
     * * request - calls when request was received
     *
     * @param string $eventName
     * @param callable $callback
     */
    public function On(string $eventName, callable $callback) : void
    {
        $this->registeredEvents[$eventName] = $callback;
    }

    /**
     * Run server
     *
     * @throws ServerStartException
     */
    public function Start() : void
    {
        $this->socket = @stream_socket_server("tcp://" . $this->address . ":" . $this->port, $errno, $errstr);

        if ($errstr)
        {
            throw new ServerStartException($errstr);
        }

        $this->registeredEvents["start"]($this);
        $headerName = $headerValue = $firstHeader = $buffer = $buffer1 = "";
        $headers = [];
        $parsedHeaders = array();
        $header1 = [];
        $headersI = -1;
        $firstHeader1 = [];
        $bufferBroken = false;
        while ($this->socket != null && $connect = stream_socket_accept($this->socket, -1))
        {
            $bufferBroken = false;
            $headers = [];
            $parsedHeaders = array();
            $header1 = [];
            $headerName = "";
            $headerValue = "";
            $headersI = -1;
            $name = stream_socket_get_name($connect, true);
            while ($buffer = rtrim(fgets($connect)))
            {
                if ($buffer == false)
                {
                    $bufferBroken = true;
                    break;
                }
                $headersI++;
                $headers[$headersI] = $buffer;
                if ($headersI == 0)
                {
                    $firstHeader = $headers[0];
                    $firstHeader1 = explode(' ', $firstHeader);
                    if ($firstHeader1[count($firstHeader1) - 1] != "HTTP/1.0" && $firstHeader1[count($firstHeader1) - 1] != "HTTP/1.1")
                    {
                        fclose($connect);
                        $bufferBroken = true;
                        break;
                    }
                }
            }
            if ($bufferBroken)
            {
                fclose($connect);
                continue;
            }
            if (count($headers) == 0)
            {
                fclose($connect);
                continue;
            }
            foreach ($headers as $header)
            {
                $header1 = explode(": ", $header);
                if (count($header1) < 2)
                {
                    continue;
                }
                $headerName = $header1[0];
                array_shift($header1);
                $headerValue = implode(' ', $header1);
                $parsedHeaders[$headerName] = $headerValue;
            }
            $body = "";
            if (isset($parsedHeaders["Content-Length"]) && intval($parsedHeaders["Content-Length"]) > 0)
            {
                stream_set_timeout($connect, 10);
                $contentLength = intval($parsedHeaders["Content-Length"]);
                $body = fread($connect, intval($parsedHeaders["Content-Length"]));
            }
            $meta = stream_get_meta_data($connect);
            if ($meta["timed_out"])
            {
                fclose($connect);
                continue;
            }
            $body = urldecode($body);
            $request = new Request($headers, $body, $name);
            $request->Server["server_port"] = $this->port;

            $response = new Response($connect);
            if ($request->RequestError)
            {
                $response->end("");
                continue;
            }
            if (isset($parsedHeaders["Expect"]) && strtolower($parsedHeaders["Expect"]) == "100-continue" && strlen($body) < $parsedHeaders["Content-Length"])
            {
                $response->status(100);
                $response->end("");
                continue;
            }
            $response->header("Content-Type", "text/html");
            $response->header("Connection", "close");

            $this->registeredEvents["request"]($request, $response);
            if (!$response->IsClosed())
            {
                $response->status(500);
                $response->end("");
            }
            if ($this->shutdownWasCalled)
            {
                fclose($this->socket);
                $this->socket = null;
                $this->registeredEvents["shutdown"]($this);
            }
        }
    }

    /**
     * Shutdown server
     */
    public function Shutdown() : void
    {
        $this->shutdownWasCalled = true;
    }
}