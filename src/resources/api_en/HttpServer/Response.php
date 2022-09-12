<?php

namespace HttpServer;

use HttpServer\Exceptions\ClosedRequestException;
use HttpServer\Exceptions\HeadersSentException;

/**
 * Class contains methods to send response to client and close connection
 */

final class Response
{
    /**
     * @var bool Enables client non-block mode. It means that server won't wait when client will receive data. WARNING!!! USE THIS PARAMETER CAREFULLY! IT CAN MAKE SERVER BEHAVIOR NON-OBVIOUS OR UNPREDICTABLE! IF YOU WANT TO SEND A BIG DATA, SEND EVERY ~8KB
     */
    public bool $ClientNonBlockMode = false;

    /**
     * @var int Maximum waiting time in milliseconds for data to be sent to the client
     */
    public int $DataSendTimeout = 0;

    /**
     * Adds header for response
     *
     * @param string $header
     * @param string $value
     */
    public function Header(string $header, string $value) : void
    {}

    /**
     * Sets HTTP-status
     *
     * @param int $status
     */
    public function Status(int $status) : void
    {}

    /**
     * Returns TRUE if connection closed
     *
     * @return bool
     */
    public function IsClosed() : bool
    {}

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
    {}

    /**
     * Sends all set headers to client
     *
     * @return void
     * @throws HeadersSentException
     */
    public function PrintHeaders() : void
    {}

    /**
     * Adds plain text to response body
     *
     * @param string $plainText
     * @return void
     * @throws ClosedRequestException
     * @throws HeadersSentException
     */
    public function PrintBody(string $plainText) : void
    {}

    /**
     * Adds response for client and closes connection
     *
     * @param string $message
     */
    public function End(string $message = "") : void
    {}

    /**
     * Returns full response with all headers
     *
     * @return string
     */
    public function GetFullResponse() : string
    {}
}