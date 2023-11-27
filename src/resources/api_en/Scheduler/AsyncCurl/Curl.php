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
     * Callback which is being called at the end of request. Expects the next signature: `OnLoad(?string $body, resource $ch) : void`
     *
     * @var Closure|null string $body, resource $ch
     */

    public ?Closure $OnLoad = null;

    /**
     * Creates an object of asynchronous cUrl
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {}

    /**
     * @return bool Is request executing right now
     */
    public function IsExecuting() : bool
    {}

    /**
     * Returns a cUrl handler. Can be used for such functions as "curl_setopt()", etc.
     *
     * @return resource
     */
    public function GetCurlHandle()
    {}

    /**
     * Runs request in the asynchronous task. At the finish calls `Curl->OnLoad(?string $body, resource $ch) : void`
     *
     * @return void
     */
    public function Execute() : void
    {}
}