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
        $this->response = $plainText;
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
}