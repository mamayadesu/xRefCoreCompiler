<?php

namespace HttpServer;

/**
 * Содержит данные запроса
 */

final class Request
{
    public string $RequestMethod, $RequestUri, $RequestUrl, $QueryString = "", $PathInfo, $ServerProtocol, $RemoteAddress;
    public int $RequestTime, $ServerPort, $RemotePort, $MasterTime;
    public float $RequestTimeFloat;

    /**
     * @var array<string, mixed>
     */
    public array $Headers = array();

    /**
     * @var array<string, string>
     */
    public array $Get = array();

    /**
     * @var array<string, string>
     */
    public array $Post = array();

    /**
     * @var array<string, string>
     */
    public array $Cookie = array();

    /**
     * @var bool TRUE, если произошла какая-либо ошибка
     */
    public bool $RequestError = false;

    /**
     * Тело запроса
     *
     * @return string
     */
    public function GetRawContent() : string
    {}
}