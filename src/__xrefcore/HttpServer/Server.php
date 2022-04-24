<?php

namespace HttpServer;

use HttpServer\Exceptions\ContinueStatusNotSupportedYetException;
use HttpServer\Exceptions\ServerStartException;
use HttpServer\Exceptions\UnknownEventException;

/**
 * HTTP-server
 *
 * WARNING!!! This package IS EXPERIMENTAL! Use this AT OWN RISK!
 */

final class Server
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
    private array $responses = array();

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
        $this->On("start", function (Server $server) { });
        $this->On("shutdown", function (Server $server) { });
        $this->On("request", function (Request $request, Response $response) { });
    }

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
     */
    public function On(string $eventName, callable $callback) : void
    {
        $events = ["start", "shutdown", "request"];
        if (!in_array($eventName, $events))
        {
            $e = new UnknownEventException("Unknown event '" . $eventName . "'. Supported events: " . implode(", ", $events));
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->registeredEvents[$eventName] = $callback;
    }

    /**
     * Run server
     *
     * @throws ServerStartException
     */
    public function Start() : void
    {
        if (DEV_MODE) echo "[HttpServer] Starting\n";
        $this->socket = @stream_socket_server("tcp://" . $this->address . ":" . $this->port, $errno, $errstr);

        if ($errstr != "")
        {
            $e = new ServerStartException($errstr);
            $e->__xrefcoreexception = true;
            throw $e;
        }

        $this->registeredEvents["start"]($this);
        $headerName = $headerValue = $firstHeader = $buffer = $buffer1 = $requestDump = $responseDump = "";
        $headers = [];
        $parsedHeaders = array();
        $header1 = [];
        $headersI = -1;
        $firstHeader1 = [];
        $bufferBroken = false;
        if (DEV_MODE) echo "[HttpServer] Waiting for request\n";
        while ($this->socket != null && $connect = stream_socket_accept($this->socket, -1))
        {
            $requestDump = "";
            $responseDump = "";
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
                    if (DEV_MODE) echo "[HttpServer] Buffer is broken\n";
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
                        if (DEV_MODE) echo "[HttpServer] Not HTTP request or wrong HTTP version\n";
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
                if (DEV_MODE) echo "[HttpServer] No headers.\n";
                fclose($connect);
                continue;
            }
            $cookies = array();
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
                if (strtolower($headerName) == "cookie")
                {
                    $cookieArr = explode('=', $headerValue);
                    $cookies[rawurldecode($cookieArr[0])] = rawurldecode($cookieArr[1]);
                }
                else
                {
                    $parsedHeaders[$headerName] = $headerValue;
                }
                $requestDump .= $headerName . ": " . $headerValue . "\n";
            }
            $body = "";
            if (isset($parsedHeaders["Content-Length"]) && intval($parsedHeaders["Content-Length"]) > 0)
            {
                if (DEV_MODE) echo "[HttpServer] Reading content. Length " . intval($parsedHeaders["Content-Length"]) . "\n";
                stream_set_timeout($connect, 10);
                $contentLength = intval($parsedHeaders["Content-Length"]);
                $body = fread($connect, intval($parsedHeaders["Content-Length"]));
            }
            $meta = stream_get_meta_data($connect);
            if ($meta["timed_out"])
            {
                if (DEV_MODE) echo "[HttpServer] Read timeout\n";
                fclose($connect);
                continue;
            }
            $body = urldecode($body);
            $requestDump .= $body;
            $request = new Request($headers, $body, $name);
            $request->ServerPort = $this->port;
            $request->Headers = $parsedHeaders;
            $request->RequestUrl = "http://" . $parsedHeaders["Host"] . $request->RequestUri;
            $request->Cookie = $cookies;

            $parsedUrl = parse_url($request->RequestUrl);
            if (isset($parsedUrl["query"]))
            {
                $request->QueryString = $parsedUrl["query"];
            }
            if (isset($parsedUrl["path"]))
            {
                $request->PathInfo = $parsedUrl["path"];
            }
            parse_str($request->QueryString, $request->Get);

            if (!@json_decode($body))
            {
                parse_str($body, $request->Post);
            }

            $response = new Response($connect);
            $this->responses[] = $response;
            if ($request->RequestError)
            {
                if (DEV_MODE) echo "[HttpServer] Request Error\n";
                $response->End("");
                continue;
            }
            if (isset($parsedHeaders["Expect"]) && strtolower($parsedHeaders["Expect"]) == "100-continue" && strlen($body) < $parsedHeaders["Content-Length"])
            {
                if (DEV_MODE) echo "[HttpServer] Client expected 100, but 100 Continue not supported yet. Please wait for updates.\n";
                $response->Status(405);
                $response->End("<h1>405 Method Not Allowed</h1>");
                continue;
            }
            $response->Header("Content-Type", "text/html");
            $response->Header("Connection", "close");

            $this->registeredEvents["request"]($request, $response);
//            if (!$response->IsClosed())
//            {
//                if (DEV_MODE) echo "[HttpServer] Request could be closed, but something went wrong\n";
//                $response->Status(500);
//                $response->End("<h1>500 Internal Server Error</h1>");
//            }
            foreach ($this->responses as $key => $response)
            {if($response instanceof Response)continue;
                if ($response->IsClosed())
                {
                    unset($this->responses[$key]);
                }
            }
            $this->responses = array_values($this->responses);
            if ($this->shutdownWasCalled)
            {
                if (DEV_MODE) echo "[HttpServer] Shutting down\n";
                foreach ($this->responses as $response)
                {if($response instanceof Response)continue;
                    $response->End();
                }
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