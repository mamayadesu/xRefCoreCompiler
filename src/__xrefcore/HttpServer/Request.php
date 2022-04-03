<?php

namespace HttpServer;

/**
 * Contains request data
 */

final class Request
{
    public string $RequestMethod, $RequestUri, $QueryString, $PathInfo, $ServerProtocol, $RemoteAddress;
    public int $RequestTime, $ServerPort, $RemotePort, $MasterTime;
    public float $RequestTimeFloat;

    /**
     * @var bool TRUE if any error was occured
     */
    public bool $RequestError = false;

    /**
     * @ignore
     */
    private string $rc;

    /**
     * @ignore
     */
    public function __construct(array $headers, string $rawcontent, string $name)
    {
        $name1 = explode(':', $name);
        $remote_addr = $name1[0];
        $remote_port = $name1[1];

        $s = array();
        if (!isset($headers[0]))
        {
            $this->RequestError = true;
            return;
        }
        $h0 = explode(' ', $headers[0]);
        $this->RequestMethod = $h0[0];

        $this->RequestUri = $h0[1];
        if (strpos("?", $h0[1]) !== false)
        {
            $h01 = explode('?', $h0[1]);
            $this->RequestUri = $h01[0];
            array_shift($h01);
            $this->QueryString = implode('?', $h01);
        }
        $this->PathInfo = $this->RequestUri;
        $this->RequestTime = $this->MasterTime = time();
        $this->RequestTimeFloat = microtime(true);
        $this->ServerProtocol = $h0[2];
        $this->ServerPort = 0;
        $this->RemotePort = $remote_port;
        $this->RemoteAddress = $remote_addr;

        $this->rc = $rawcontent;
    }

    /**
     * Returns body of request
     *
     * @return string
     */
    public function GetRawContent() : string
    {
        return $this->rc;
    }
}