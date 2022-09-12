<?php

namespace HttpServer;

use HttpServer\Exceptions\ClosedRequestException;
use HttpServer\Exceptions\HeadersSentException;

/**
 * Класс содержит методы для ответа клиенту на его запрос
 */

final class Response
{
    /**
     * @var bool Включает неблокирующий режим. Это значит, сервер не будет ждать, когда до клиента дойдут данные. ОСТОРОЖНО!!! ИСПОЛЬЗУЙТЕ ЭТОТ ПАРАМЕТР АККУРАТНО! ЭТО МОЖЕТ СДЕЛАТЬ ПОВЕДЕНИЕ СЕРВЕРА НЕПРЕДСКАЗУЕМЫМ! ЕСЛИ ВЫ ХОТИТЕ ОТПРАВЛЯТЬ БОЛЬШИЕ ДАННЫЕ, ОТПРАВЛЯЙТЕ ИХ ПО ~8KB
     */
    public bool $ClientNonBlockMode = false;

    /**
     * @var int Максимальное время ожидания, когда до клиента дойдут данные
     */
    public int $DataSendTimeout = 0;

    /**
     * Добавляет заголовок ответа
     *
     * @param string $header
     * @param string $value
     */
    public function Header(string $header, string $value) : void
    {}

    /**
     * Устанавливает код состояния HTTP
     *
     * @param int $status
     */
    public function Status(int $status) : void
    {}

    /**
     * Возвращает TRUE, если соединение закрыто сервером
     *
     * @return bool
     */
    public function IsClosed() : bool
    {}

    /**
     * @param string $name Название Cookie
     * @param string $value Значение Cookie
     * @param int $expires Время срока жизни в Unixtime
     * @param string $domain
     * @param string $path
     * @param bool $secure
     * @return void
     * @throws HeadersSentException
     */
    public function SetCookie(string $name, string $value, int $expires = 0, string $domain = "", string $path = "", bool $secure = false)
    {}

    /**
     * Отправляет все установленные заголовки клиенту
     *
     * @return void
     * @throws HeadersSentException
     */
    public function PrintHeaders() : void
    {}

    /**
     * Отправляет тело ответа клиенту
     *
     * @param string $plainText
     * @return void
     * @throws ClosedRequestException
     * @throws HeadersSentException
     */
    public function PrintBody(string $plainText) : void
    {}

    /**
     * Отправляет тело ответа клиенту и закрывает соединение
     *
     * @param string $message
     */
    public function End(string $message = "") : void
    {}

    /**
     * Возвращает полный ответ клиенту, включая заголовки
     *
     * @return string
     */
    public function GetFullResponse() : string
    {}
}