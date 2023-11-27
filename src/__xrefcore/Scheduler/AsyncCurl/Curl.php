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
     * @ignore
     */
    private bool $Executed = false;

    /**
     * Callback which is being called at the end of request. Expects the next signature: `OnLoad(?string $body, resource $ch) : void`
     *
     * @var Closure|null string $body, resource $ch
     */

    public ?Closure $OnLoad = null;

    /**
     * @ignore
     */
    private ?AsyncTask $task = null;

    /**
     * @ignore
     */
    private ?int $mrc, $active;

    /**
     * @ignore
     */
    private $mh, $ch;

    /**
     * @ignore
     */
    private int $_current_stage;

    /**
     * Creates an object of asynchronous cUrl
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        if ($url === null)
        {
            $this->ch = curl_init();
        }
        else
        {
            $this->ch = curl_init($url);
        }
    }

    /**
     * @return bool Is request executing right now
     */
    public function IsExecuting() : bool
    {
        if ($this->task === null || $this->task->IsFinished())
        {
            return false;
        }
        return true;
    }

    /**
     * Returns a cUrl handler. Can be used for such functions as "curl_setopt()", etc.
     *
     * @return resource
     */
    public function GetCurlHandle()
    {
        return $this->ch;
    }

    /**
     * Runs request in the asynchronous task. At the finish calls `Curl->OnLoad(?string $body, resource $ch) : void`
     *
     * @return void
     */
    public function Execute() : void
    {
        if ($this->Executed)
        {
            $e = new AsyncCurlRequestException("Request was finished or is already executing");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->Executed = true;
        $this->mh = curl_multi_init();
        curl_multi_add_handle($this->mh, $this->ch);
        
        $this->_current_stage = 1;
        $this->active = null;
        $this->task = new AsyncTask($this, 1, false, function(AsyncTask $task, IAsyncTaskParameters $task_params) : void
        {
            if ($this->_current_stage == 1)
            {
                $this->mrc = curl_multi_exec($this->mh, $this->active);
                if (!($this->mrc == CURLM_CALL_MULTI_PERFORM))
                    $this->_current_stage++;
            }
            else if ($this->_current_stage == 2)
            {
                if ($this->active && $this->mrc == CURLM_OK)
                {
                    if (curl_multi_select($this->mh, 0) != -1)
                    {
                        do
                        {
                            $this->mrc = curl_multi_exec($this->mh, $this->active);
                        }
                        while ($this->mrc == CURLM_CALL_MULTI_PERFORM);
                    }
                }
                else
                    $this->_current_stage++;
            }
            else
            {
                curl_multi_remove_handle($this->mh, $this->ch);
                curl_multi_close($this->mh);

                $html = curl_multi_getcontent($this->ch);

                if ($this->OnLoad !== null)
                {
                    call_user_func($this->OnLoad, $html, $this->ch);
                }

                $task->Cancel();
                $this->task = null;
            }
        });
    }
}