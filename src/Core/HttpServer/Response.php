<?php

namespace HttpServer;

/**
 * Package contains method for response build
 * @package HttpServer
 */

class Response
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
    private bool $closed = false;

    /**
     * @ignore
     */
    private int $status = 200;

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
        510 => "Not Extended"
    );

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
        $this->headers[$header] = $value;
    }

    /**
     * Sets HTTP-status
     *
     * @param int $status
     */
    public function Status(int $status) : void
    {
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
     * Sets response for client and closes connection
     *
     * @param string $message
     */
    public function End(string $message)
    {
        $connect = $this->connect;
        $data = "";
        $data .= "HTTP/1.1 " . $this->status . " " . $this->http_codes[$this->status] . "\r\n";
        foreach ($this->headers as $header => $value)
        {
            $data .= $header . ": " . $value . "\r\n";
        }
        $data .= "\r\n" . $message;
        fwrite($connect, $data);
        fclose($connect);
        $this->closed = true;
    }
}