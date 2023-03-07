<?php

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
     * @var int Sets a size of packet fragmentation. Set 0 for disable fragmentation
     */
    public static int $PacketFragmentationSize = 65536;

    /**
     * Adds header for response
     *
     * @param string $header
     * @param string $value
     * @throws HeadersSentException
     */
    public function Header(string $header, string $value) : void
    {}

    /**
     * Sets HTTP-status
     *
     * @param int $status
     * @throws HeadersSentException
     */
    public function Status(int $status) : void
    {}

    /**
     * Returns TRUE if client aborted connection
     *
     * @return bool
     */
    public function IsConnectionAborted() : bool
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
     * @return bool `TRUE` if success, `FALSE` on any error. Pay attention, if server fails to send message to client, request will be automatically closed
     * @throws HeadersSentException
     * @throws ConnectionLostException
     */
    public function PrintHeaders() : bool
    {}

    /**
     * Adds plain text to response body
     *
     * @param string $plainText
     * @return bool `TRUE` if success, `FALSE` on any error. Pay attention, if server fails to send message to client, request will be automatically closed
     * @throws ClosedRequestException
     * @throws ConnectionLostException
     */
    public function PrintBody(string $plainText) : bool
    {}

    /**
     * Adds response for client and closes connection
     *
     * @param string $message
     * @return bool `TRUE` if success, `FALSE` on any error.
     * @throws ConnectionLostException
     */
    public function End(string $message = "") : bool
    {}
}