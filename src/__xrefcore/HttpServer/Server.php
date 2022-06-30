<?php
declare(ticks = 1);

namespace HttpServer;

use HttpServer\Exceptions\ServerStartException;
use HttpServer\Exceptions\UnknownEventException;
use Scheduler\AsyncTask;
use Scheduler\NoAsyncTaskParameters;

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
    private ?AsyncTask $asyncServer = null;

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
     * @var int Timeout of data reading when connection accepted
     */
    public int $DataReadTimeout = 5;

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
     * @throws UnknownEventException
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
    public function Start(bool $async = false) : void
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
        if (DEV_MODE) echo "[HttpServer] Waiting for request\n";
        if ($async)
        {
            $this->asyncServer = new AsyncTask($this, 5, false, function(AsyncTask $task, NoAsyncTaskParameters $params) : void { $this->Handle(); });
        }
        else while (true)
        {
            $this->Handle();
            time_nanosleep(0, 5 * 1000000);
            if ($this->shutdownWasCalled)
            {
                return;
            }
        }
    }

    /**
     * @ignore
     */
    private function Handle() : void
    {
        while ($this->socket != null && $connect = @stream_socket_accept($this->socket, 0))
        {
            $requestDump = "";
            $responseDump = "";
            $headers = [];
            $parsedHeaders = array();
            $header1 = [];
            $headerName = "";
            $headerValue = "";
            $headersI = -1;
            $name = stream_socket_get_name($connect, true);
            if (explode(':', $name)[1] == null)
            {
                fclose($connect);
                continue;
            }
            if ($this->DataReadTimeout < 0)
            {
                $this->DataReadTimeout = 0;
            }
            if ($this->DataReadTimeout > 0)
            {
                stream_set_timeout($connect, $this->DataReadTimeout);
            }
            while ($buffer = rtrim(fgets($connect))) // reading headers
            {
                if ($buffer == false)
                {
                    if (DEV_MODE) echo "[HttpServer] Buffer is broken\n";
                    fclose($connect);
                    continue 2;
                }
                $headersI++;
                $headers[$headersI] = $buffer;
                if ($headersI == 0) // if it's the first header (must be "*TYPE* HTTP/1.x")
                {
                    $firstHeader = $headers[0];
                    $firstHeader1 = explode(' ', $firstHeader);
                    if ($firstHeader1[count($firstHeader1) - 1] != "HTTP/1.0" && $firstHeader1[count($firstHeader1) - 1] != "HTTP/1.1")
                    {
                        if (DEV_MODE) echo "[HttpServer] Not HTTP request or wrong HTTP version\n";
                        fclose($connect);
                        continue 2;
                    }
                }
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
            if (!isset($parsedHeaders["Host"]))
            {
                $parsedHeaders["Host"] = "";
            }
            //$body = urldecode($body);
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

            try
            {
                $this->registeredEvents["request"]($request, $response);
            }
            catch (\Throwable $e)
            {
                $message =  " Failed to handle HTTP-server request.\n";
                $message .= " Uncaught " . get_class($e) . " '" . $e->getMessage() . "' in " . $e->getFile() . " on line " . $e->getLine() . ".\n";
                $message .= " HTTP-server is shutting down.";
                fwrite(STDERR, $message);
                $this->Shutdown();
            }
            foreach ($this->responses as $key => $response)
            {if(!$response instanceof Response)continue;
                if ($response->IsClosed())
                {
                    unset($this->responses[$key]);
                }
            }
            $this->responses = array_values($this->responses);
            if ($this->shutdownWasCalled)
            {
                return;
            }
        }
    }

    /**
     * Shutdown server
     */
    public function Shutdown() : void
    {
        $this->shutdownWasCalled = true;
        foreach ($this->responses as $response)
        {if($response instanceof Response)continue;
            $response->End();
        }
        @fclose($this->socket);
        $this->socket = null;
        $this->registeredEvents["shutdown"]($this);
        if ($this->asyncServer != null)
        {
            $this->asyncServer->Cancel();
        }
    }
}