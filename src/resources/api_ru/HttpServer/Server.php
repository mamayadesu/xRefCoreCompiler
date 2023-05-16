<?php

namespace HttpServer;

use HttpServer\Exceptions\ServerStartException;
use HttpServer\Exceptions\UnknownEventException;
use Scheduler\AsyncTask;
use Scheduler\NoAsyncTaskParameters;
use Throwable;

/**
 * Встроенный, полностью чистый HTTP-сервер. Может работать как в синхронном, так и асинхронном режиме. Обработка запросов клиентов полностью зависит от вас
 */

final class Server
{
    /**
     * @var int Время ожидания получения данных клиента, когда соединение установлено
     */
    public int $DataReadTimeout = 5;

    /**
     * @var int Максимальный размер тела запроса. Если лимит превышен, запрос будет немедленно отклонён. Установите -1 для снятия лимита (на текущей версии может работать некорректно).
     */
    public int $MaxRequestLength = 8192;

    /**
     * Конструктор Server
     * @param string $address Прослушиваемый IP-адрес. Задайте "0.0.0.0" для прослушивания по всем доступным адресам
     * @param int $port Порт
     */
    public function __construct(string $address = "0.0.0.0", int $port = 8080)
    {}

    /**
     * Подписаться на событие
     *
     * Поддерживаемые события:
     * * start - срабатывает при запуске сервера. Сигнатура: `function(HttpServer\Server $server) : void`
     * * shutdown - срабатывает при выключении сервера. Сигнатура: `function(HttpServer\Server $server) : void`
     * * request - срабатывает при получении нового запроса. Сигнатура: `function(HttpServer\Request $request, HttpServer\Response $response, HttpServer\Server $server) : void`
     * * throwable - срабатывает при необработанном исключении при обработке запроса. Сигнатура: function(HttpServer\Request $request, HttpServer\Response $response, Throwable $throwable, HttpServer\Server $server) : void`
     *
     * @param ServerEvents $event
     * @param callable $callback
     * @throws UnknownEventException
     */
    public function On(string $event, callable $callback) : void
    {}
    
    /**
     * Возвращает массив незакрытых запросов (если быть точнее, неотправленных ответов).
     *
     * @return array<Response>
     */
    public function GetUnsentResponses() : array
    {}

    /**
     * Запускает сервер
     *
     * @param bool $async Если задать TRUE, веб-сервер будет запущен в фоновом режиме. Это значит, что метод не будет блокировать дальнейшее исполнение кода
     * @return void
     * @throws ServerStartException
     */
    public function Start(bool $async = false) : void
    {}

    /**
     * Выключает веб-сервер
     */
    public function Shutdown() : void
    {}

    /**
     * @return float Количество принятых байт
     */
    public function GetBytesReceived() : float
    {}

    /**
     *
     * @return float Количество отправленных байт
     */
    public function GetBytesSent() : float
    {}
}