<?php
declare(ticks = 1);

namespace HttpServer;

use HttpServer\Exceptions\ClosedRequestException;
use HttpServer\Exceptions\HeadersSentException;

/**
 * Class contains methods to send response to client and close connection
 */

final class Response
{
    /**
     * @ignore
     */
    private string $response = "";

    /**
     * @ignore
     */
    private array $headers = array();

    /**
     * @ignore
     */
    private $connect;

    /**
     * @ignore
     */
    private bool $closed = false;

    /**
     * @ignore
     */

    private bool $headersPrinted = false;

    /**
     * @ignore
     */
    private int $status = 200;

    /**
     * @ignore
     */
    private array $cookies = array();

    /**
     * @ignore
     */
    private array $http_codes = array
    (
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing",
        103 => "Checkpoint",

        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        207 => "Multi-Status",

        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        306 => "Switch Proxy",
        307 => "Temporary Redirect",
        308 => "Permanent Redirect",

        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",
        422 => "Unprocessable Entity",
        423 => "Locked",
        424 => "Failed Dependency",
        425 => "Unordered Collection",
        426 => "Upgrade Required",
        449 => "Retry With",
        450 => "Blocked by Windows Parental Controls",

        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates",
        507 => "Insufficient Storage",
        509 => "Bandwidth Limit Exceeded",
        510 => "Not Extended",
        511 => "Network Authentication Required",
        520 => "Unknown Error",
        521 => "Web Server Is Down",
        522 => "Connection Timed Out",
        523 => "Origin Is Unreachable",
        524 => "A Timeout Occurred",
        525 => "SSL Handshake Failed",
        526 => "Invalid SSL Certificate"
    );

    /**
     * @var bool Enables client non-block mode. It means that server won't wait when client will receive data. WARNING!!! USE THIS PARAMETER CAREFULLY! IT CAN MAKE SERVER BEHAVIOR NON-OBVIOUS OR UNPREDICTABLE! IF YOU WANT TO SEND A BIG DATA, SEND EVERY ~8KB
     */
    public bool $ClientNonBlockMode = false;

    /**
     * @var int Maximum waiting time in milliseconds for data to be sent to the client
     */
    public int $DataSendTimeout = 0;

    /**
     * @ignore
     */
    public function __construct($connect)
    {
        $this->connect = $connect;
    }

    /**
     * Adds header for response
     *
     * @param string $header
     * @param string $value
     */
    public function Header(string $header, string $value) : void
    {
        if ($this->headersPrinted)
        {
            $e = new HeadersSentException("Headers already sent to client");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->headers[$header] = $value;
    }

    /**
     * Sets HTTP-status
     *
     * @param int $status
     */
    public function Status(int $status) : void
    {
        if ($this->headersPrinted)
        {
            $e = new HeadersSentException("Headers already sent to client");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!isset($this->http_codes[$status]))
        {
            return;
        }
        $this->status = $status;
    }

    /**
     * Returns TRUE if connection closed
     *
     * @return bool
     */
    public function IsClosed() : bool
    {
        return $this->closed;
    }

    /**
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $expires In unixtime
     * @param string $domain
     * @param string $path
     * @param bool $secure
     * @return void
     * @throws HeadersSentException
     */
    public function SetCookie(string $name, string $value, int $expires = 0, string $domain = "", string $path = "", bool $secure = false)
    {
        if ($this->headersPrinted)
        {
            $e = new HeadersSentException("Headers already sent to client");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $name = str_replace(["\n", "\r"], ["", ""], $name);
        $value = str_replace(["\n", "\r"], [""], $value);
        $domain = str_replace(["\n", "\r"], ["", ""], $domain);
        $path = str_replace(["\n", "\r"], ["", ""], $path);

        $name = rawurlencode($name);
        $value = rawurlencode($value);
        $domain = rawurlencode($domain);
        $path = rawurlencode($path);

        $dt = "";
        if ($expires > 0)
        {
            $dt = gmdate("D, d M Y H:i:s", $expires) . " GMT";
        }
        $cookieString = $name . "=" . $value;

        if ($dt != "")
        {
            $cookieString .= "; Expires=" . $dt;
        }

        if ($domain != "")
        {
            $cookieString .= "; Domain=" . $domain;
        }

        if ($path != "")
        {
            $cookieString .= "; Path=" . $path;
        }

        if ($secure)
        {
            $cookieString .= "; Secure";
        }
        $this->cookies[$name] = $cookieString;
    }

    /**
     * Sends all set headers to client
     *
     * @return void
     * @throws HeadersSentException
     */
    public function PrintHeaders() : void
    {
        if ($this->headersPrinted)
        {
            $e = new HeadersSentException("Headers already sent to client");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $data = "";
        $data .= "HTTP/1.1 " . $this->status . " " . $this->http_codes[$this->status] . "\r\n";
        foreach ($this->headers as $header => $value)
        {
            $data .= $header . ": " . $value . "\r\n";
        }
        foreach ($this->cookies as $name => $cookieString)
        {
            $data .= "Set-Cookie: " . $cookieString . "\r\n";
        }
        $data .= "\r\n";
        $this->response = $data;
        if ($this->DataSendTimeout > 0)
        {
            $timeout = $this->getTimeout();
            stream_set_timeout($this->connect, $timeout[0], $timeout[1]);
        }
        stream_set_blocking($this->connect, !$this->ClientNonBlockMode);
        @fwrite($this->connect, $data);
        $this->headersPrinted = true;
    }

    /**
     * Adds plain text to response body
     *
     * @param string $plainText
     * @return void
     * @throws ClosedRequestException
     * @throws HeadersSentException
     */
    public function PrintBody(string $plainText) : void
    {
        if ($this->closed)
        {
            $e = new ClosedRequestException("The request is already closed. Cannot print body.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!$this->headersPrinted)
        {
            $this->PrintHeaders();
        }
        $this->response .= $plainText;
        if ($this->DataSendTimeout > 0)
        {
            $timeout = $this->getTimeout();
            stream_set_timeout($this->connect, $timeout[0], $timeout[1]);
        }
        stream_set_blocking($this->connect, !$this->ClientNonBlockMode);
        @fwrite($this->connect, $plainText);
    }

    /**
     * Adds response for client and closes connection
     *
     * @param string $message
     */
    public function End(string $message = "") : void
    {
        if ($this->closed)
        {
            $e = new ClosedRequestException("The request is already closed. Cannot finish request.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!$this->headersPrinted)
        {
            $this->PrintHeaders();
        }
        if ($this->DataSendTimeout > 0)
        {
            $timeout = $this->getTimeout();
            stream_set_timeout($this->connect, $timeout[0], $timeout[1]);
        }
        stream_set_blocking($this->connect, !$this->ClientNonBlockMode);
        @fwrite($this->connect, $message);
        @fclose($this->connect);
        $this->closed = true;
        $this->response .= $message;
    }

    /**
     * Returns full response with all headers
     *
     * @return string
     */
    public function GetFullResponse() : string
    {
        return $this->response;
    }

    /**
     * @ignore
     */
    private function getTimeout() : array
    {
        $seconds = intval($this->DataSendTimeout / 1000);
        $microseconds = ($this->DataSendTimeout - $seconds * 1000) * 1000;
        return [$seconds, $microseconds];
    }
}