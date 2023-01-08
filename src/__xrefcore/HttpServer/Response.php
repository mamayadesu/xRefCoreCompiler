<?php
declare(ticks = 1);

namespace HttpServer;

use HttpServer\Exceptions\ClosedRequestException;
use HttpServer\Exceptions\ConnectionLostException;
use HttpServer\Exceptions\HeadersSentException;

/**
 * Class contains methods to send response to client and close connection
 */

final class Response
{
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
    private bool $closed = false, $aborted = false, $headersPrinted = false;

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
     * @var bool If `true`, methods `End`, `PrintBody` and `PrintHeaders` won't throw an exception on failure, but will return `false`
     */
    public static bool $IgnoreConnectionLost = true;

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
     * @throws HeadersSentException
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
     * @throws HeadersSentException
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
     * Returns TRUE if client aborted connection
     *
     * @return bool
     */
    public function IsConnectionAborted() : bool
    {
        if ($this->closed)
        {
            return $this->aborted;
        }
        $aborted = feof($this->connect);
        if ($aborted)
        {
            @fclose($this->connect);
            $this->closed = true;
            $this->aborted = true;
        }
        return $this->aborted;
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
        $domain = str_replace("%2F", "/", rawurlencode($domain));
        $path = str_replace("%2F", "/", rawurlencode($path));

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
     * @return bool `TRUE` if success, `FALSE` on any error. Pay attention, if server fails to send message to client, request will be automatically closed
     * @throws HeadersSentException
     * @throws ConnectionLostException
     */
    public function PrintHeaders() : bool
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
        if ($this->DataSendTimeout > 0)
        {
            $timeout = $this->getTimeout();
            stream_set_timeout($this->connect, $timeout[0], $timeout[1]);
        }
        stream_set_blocking($this->connect, !$this->ClientNonBlockMode);
        $this->headersPrinted = true;
        $result = $this->send($data);
        if ($result !== null && !self::$IgnoreConnectionLost)
        {
            $e = new ConnectionLostException($result->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $result === null;
    }

    /**
     * Adds plain text to response body
     *
     * @param string $plainText
     * @return bool `TRUE` if success, `FALSE` on any error. Pay attention, if server fails to send message to client, request will be automatically closed
     * @throws ClosedRequestException
     * @throws ConnectionLostException
     */
    public function PrintBody(string $plainText) : bool
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
        if ($this->DataSendTimeout > 0)
        {
            $timeout = $this->getTimeout();
            stream_set_timeout($this->connect, $timeout[0], $timeout[1]);
        }
        stream_set_blocking($this->connect, !$this->ClientNonBlockMode);
        $result = $this->send($plainText);
        if ($result !== null && !self::$IgnoreConnectionLost)
        {
            $e = new ConnectionLostException($result->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $result === null;
    }

    /**
     * Adds response for client and closes connection
     *
     * @param string $message
     * @return bool `TRUE` if success, `FALSE` on any error.
     * @throws ConnectionLostException
     */
    public function End(string $message = "") : bool
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
        $result = $this->send($message);
        @fclose($this->connect);
        $this->closed = true;
        if ($result !== null && !self::$IgnoreConnectionLost)
        {
            $e = new ConnectionLostException($result->getMessage());
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $result === null;
    }

    /**
     * @ignore
     */
    private function send(string $message) : ?\ErrorException
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });
        try
        {
            fwrite($this->connect, $message);
        }
        catch (\ErrorException $e)
        {
            if ($e->getCode() == 8)
            {
                $this->closed = true;
            }
            restore_error_handler();
            return $e;
        }
        restore_error_handler();
        return null;
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