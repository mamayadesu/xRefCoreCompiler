<?php
declare(ticks = 1);

namespace Scheduler\AsyncCurl;

use \Closure;
use Scheduler\AsyncCurl\Exceptions\AsyncCurlRequestException;
use Scheduler\AsyncTask;
use \Exception;
use Scheduler\IAsyncTaskParameters;

class Curl
{
    /**
     * Callback, который будет вызван в конце запроса. Ожидаемая сигнатура: `OnLoad(?string $body, resource $ch) : void`
     *
     * @var Closure|null string $body, resource $ch
     */

    public ?Closure $OnLoad = null;

    /**
     * Создаёт объект асинхронного cUrl
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {}

    /**
     * @return bool Выполняется ли запрос в данный момент
     */
    public function IsExecuting() : bool
    {}

    /**
     * Возвращает стандартный обработчик cUrl. Может быть использован для таких функций, как "curl_setopt()" и т.д.
     *
     * @return resource
     */
    public function GetCurlHandle()
    {}

    /**
     * Запускает запрос в асинхронной задаче. По окончанию вызывает `Curl->OnLoad(?string $body, resource $ch) : void`
     *
     * @return void
     */
    public function Execute() : void
    {}
}