<?php
declare(ticks = 1);

namespace HttpServer;

use Application\Application;
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
     * @var int The max length of request. If this length exceeded, the request will be closed immediately. Set -1 for no limit (on current version may work incorrectly).
     */
    public int $MaxRequestLength = 8192;

    /**
     * Server constructor.
     * @param string $address Listening IP-address
     * @param int $port Port
     */
    public function __construct(string $address = "0.0.0.0", int $port = 8080)
    {
        $this->address = $address;
        $this->port = $port;
        $this->On("start", function (Server $server) : void { });
        $this->On("shutdown", function (Server $server) : void { });
        $this->On("request", [$this, "OnRequest"]);
        $this->On("throwable", [$this, "OnThrowable"]);
    }

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
    {
        $events = ["start", "shutdown", "request", "throwable"];
        if (!in_array($eventName, $events))
        {
            $e = new UnknownEventException("Unknown event '" . $eventName . "'. Supported events: " . implode(", ", $events));
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->registeredEvents[$eventName] = $callback;
    }

    /**
     * @ignore
     */
    private function OnThrowable(Request $request, Response $response, Throwable $throwable) : void
    {
        $message =  "  An error occurred while handling HTTP-server request.\n";
        $message .= "  Uncaught " . get_class($throwable) . " '" . $throwable->getMessage() . "' in " . $throwable->getFile() . " on line " . $throwable->getLine() . ".\n";
        fwrite(STDERR, $message);
        try
        {
            $response->Status(500);
            $response->End("500 Internal Server Error");
        }
        catch (Throwable $e)
        {
        }
    }

    private function OnRequest(Request $request, Response $response) : void
    {
        $appName = basename(Application::GetExecutableFileName());
        $result = <<<HTML
<html>
    <head>
        <title>xRefCore HTTP-server</title>
    </head>
    <body>
        <h1>It works!</h1>
        <p>Your application <b>$appName</b> is using xRefCore HTTP-server. This is a default page, so you should register a request event.</p>
    </body>
</html>
HTML;
        $response->End($result);
    }

    /**
     * Run server
     *
     * @param bool $async If you set TRUE, server will be started in background mode. It means this method won't block code execution after this method
     * @return void
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
        if (DEV_MODE) echo "[HttpServer] Waiting for request\n";
        $interval = 2;
        if ($async)
        {
            $this->asyncServer = new AsyncTask($this, $interval, false, function(AsyncTask $task, NoAsyncTaskParameters $params) : void { $this->Handle(); });
        }
        else while (true)
        {
            $this->Handle();
            time_nanosleep(0, $interval * 1000000);
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
        while ($this->socket != null && $connect = @stream_socket_accept($this->socket, (isset($GLOBALS["system.tick_functions"]["schedulermaster"]) ? 0 : -1)))
        {
            $requestDump = "";
            $responseDump = "";
            $headers = [];
            $parsedHeaders = array();
            $header1 = [];
            $headerName = $headerValue = "";
            $headersI = -1;
            $name = stream_socket_get_name($connect, true);
            $remote_ip_port = explode(':', $name);
            if (!isset($remote_ip_port[1]) || intval($remote_ip_port[1]) == 0)
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

                $parsedHeaders[$headerName] = $headerValue;

                if (strtolower($headerName) == "cookie")
                {
                    $cookieArr = explode(';', $headerValue);
                    foreach ($cookieArr as $unparsedCookie)
                    {
                        $unparsedCookieArr = explode('=', $unparsedCookie);
                        $cookieName = str_replace(" ", "", $unparsedCookieArr[0]);
                        $cookieValue = $unparsedCookieArr[1] ?? null;

                        $cookies[rawurldecode($cookieName)] = rawurldecode($cookieValue);
                    }
                }
                $requestDump .= $headerName . ": " . $headerValue . "\n";
            }
            $response = new Response($connect);
            $this->responses[] = $response;

            $response->Header("Content-Type", "text/html");
            $response->Header("Connection", "close");
            $contentLength = 0;
            if (isset($parsedHeaders["Content-Length"]))
            {
                $contentLength = intval($parsedHeaders["Content-Length"]);
            }

            $body = "";

            if ($this->MaxRequestLength > -1 && $contentLength > $this->MaxRequestLength)
            {
                $response->Status(413);
                $response->End("<h1>413 Payload Too Large</h1>");
                if (DEV_MODE) echo "[HttpServer] Content length is " . $contentLength . " but max length is " . $this->MaxRequestLength . "\n";
                continue;
            }

            $contentLengthLeft = $contentLength;
            if ($contentLength > 0)
            {
                if (DEV_MODE) echo "[HttpServer] Reading content. Length " . $contentLength . "\n";
                while (true)
                {
                    if ($contentLengthLeft > 8192)
                    {
                        $body .= fread($connect, 8192);
                        $contentLengthLeft -= 8192;
                    }
                    else
                    {
                        $body .= fread($connect, $contentLengthLeft);
                        break;
                    }
                }
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
            if (DEV_MODE)
            {
                $http_server_dumps_path = Application::GetExecutableDirectory() . "http_server_dumps" . DIRECTORY_SEPARATOR;
                @mkdir($http_server_dumps_path);
                $http_server_dump = "dump-" . date("Y-m-d=H-i-s", time()) . "-" . microtime(true) . ".txt";
                $f = fopen($http_server_dumps_path . $http_server_dump, "w+");
                fwrite($f, $requestDump);
                fclose($f);
                echo "[HttpServer] Dump saved as " . $http_server_dump . "\n";
            }
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

            try
            {
                $this->registeredEvents["request"]($request, $response);
            }
            catch (Throwable $e)
            {
                $this->registeredEvents["throwable"]($request, $response, $e);
            }
            $this->GetUnsentResponses();
            if ($this->shutdownWasCalled)
            {
                return;
            }
        }
    }

    /**
     * Returns the array of unclosed requests (to be more precise, unsent responses).
     *
     * @return array<Response>
     */
    public function GetUnsentResponses() : array
    {
        foreach ($this->responses as $key => $response)
        {if(!$response instanceof Response)continue;
            if ($response->IsClosed())
            {
                unset($this->responses[$key]);
            }
        }
        $this->responses = array_values($this->responses);
        return $this->responses;
    }

    /**
     * Shutdown server
     */
    public function Shutdown() : void
    {
        if ($this->shutdownWasCalled)
            return;
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