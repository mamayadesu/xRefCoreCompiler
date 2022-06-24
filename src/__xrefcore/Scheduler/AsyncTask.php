<?php

namespace Scheduler;

use \Closure;
use \Exception;
use Scheduler\Exceptions\InvalidIntervalException;
use Scheduler\Exceptions\InvalidNewExecutionTimeException;
use Throwable;

/**
 * Creates an asynchronous task
 *
 * Important note! There is maybe a small bug in PHP. If your asynchronous task doesn't work, try to add "declare(ticks = 1);" right after "<?php"
 */

final class AsyncTask
{
    /**
     * @ignore
     */
    private static int $TaskCounter = 0;

    /**
     * @ignore
     */
    private ?Closure $TaskCallback = null;

    /**
     * @ignore
     */
    private ?object $TaskCallbackObject = null;

    /**
     * @ignore
     */
    private string $TaskCallbackMethodName = "";

    /**
     * @ignore
     */
    private ?object $MyThis = null;

    /**
     * @ignore
     */
    private int $Interval, $TaskId, $ExecutedTimes = 0;

    /**
     * @ignore
     */
    private bool $RunOnce, $Cancelled = false, $Executed = false;

    /**
     * @ignore
     */
    private IAsyncTaskParameters $Parameters;

    /**
     * @ignore
     */
    private float $NextExecution;

    /**
     * @ignore
     */
    private bool $ExecutingRightNow = false;

    /**
     * @param object $MyThis Object context where task will be executed
     * @param int $Interval Execution interval in milliseconds
     * @param bool $RunOnce Execute task once
     * @param callable $TaskCallback Callback-function which will be executed in $MyThis context. Callback should accept two parameters: AsyncTask (your task) and IAsyncTaskParameters (your parameters)
     * @param IAsyncTaskParameters|null $Parameters Additional arguments
     * @throws InvalidIntervalException Interval cannot be less than 1 millisecond
     * @throws InvalidNewExecutionTimeException
     */
    public function __construct(object $MyThis, int $Interval, bool $RunOnce, callable $TaskCallback, ?IAsyncTaskParameters $Parameters = null)
    {
        if ($Parameters == null)
        {
            $Parameters = new NoAsyncTaskParameters();
        }
        if ($Interval == 0)
        {
            $e = new InvalidIntervalException("Interval cannot be less than 1 millisecond");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (is_array($TaskCallback))
        {
            $this->TaskCallbackObject = $TaskCallback[0];
            $this->TaskCallbackMethodName = $TaskCallback[1];
        }
        else
        {
            $this->TaskCallback = $TaskCallback;
        }
        $this->RunOnce = $RunOnce;
        $this->Interval = $Interval;
        $this->TaskId = self::GetNext();
        $this->Parameters = $Parameters;
        $this->MyThis = $MyThis;
        $this->SetNextExecution();
        SchedulerMaster::GetInstance()->AddTaskToQueue($this);
    }

    /**
     * Sets new "this"
     *
     * @param object $myThis
     * @return void
     */
    public function SetThis(object $myThis) : void
    {
        $this->MyThis = $myThis;
    }

    /**
     * @return object Object's context for its execution
     */
    public function GetThis() : object
    {
        return $this->MyThis;
    }

    /**
     * Execute task manually.
     *
     * @return void
     */
    public function Execute() : void
    {
        if (($this->Executed && $this->RunOnce) || $this->Cancelled || $this->ExecutingRightNow)
        {
            return;
        }

        // prevent recursive execution
        $this->ExecutingRightNow = true;

        if ($this->TaskCallback != null)
            $this->TaskCallback->call($this->MyThis, $this, $this->Parameters);
        else
            call_user_func_array([$this->TaskCallbackObject, $this->TaskCallbackMethodName], [$this, $this->Parameters]);

        $this->ExecutingRightNow = false;
        $this->Executed = true;
        $this->NextExecution = floor(microtime(true) * 1000 + $this->Interval);
        if ($this->RunOnce)
        {
            $this->NextExecution = 0;
        }
        $this->ExecutedTimes++;
    }

    /**
     * Cancel task. If you cancel task, it won't be executable anymore and will be removed from scheduler
     *
     * @return void
     */
    public function Cancel() : void
    {
        $this->Cancelled = true;
        $this->NextExecution = 0;

        unset($this->Owner);
        $sm = SchedulerMaster::GetInstance();
        if (count($sm->__getasynctasks(false)) == 0)
            $sm->__unregister();
    }

    /**
     * @return bool Returns true if task was cancelled
     */
    public function IsCancelled() : bool
    {
        return $this->Cancelled;
    }

    /**
     * @return IAsyncTaskParameters Additional arguments
     */
    public function GetParameters() : IAsyncTaskParameters
    {
        return $this->Parameters;
    }

    /**
     * @return bool Will task be executed once
     */
    public function IsOnce() : bool
    {
        return $this->RunOnce;
    }

    /**
     * @return bool Was task executed at least once
     */
    public function WasExecuted() : bool
    {
        return $this->Executed;
    }

    /**
     * @return float Next task's execution time in Unixtime format in milliseconds
     */
    public function GetNextExecution() : float
    {
        return $this->NextExecution;
    }

    /**
     * Sets a new next task's time execution
     *
     * @param float $Time New execution time in Unixtime format in milliseconds. If you don't set time, then the next execution time will be "now + interval"
     * @return void
     * @throws InvalidNewExecutionTimeException
     */
    public function SetNextExecution(float $Time = 0) : void
    {
        $Time = floor($Time);
        $now = floor(microtime(true) * 1000);
        if ($Time == 0)
        {
            $this->NextExecution = $this->Interval + $now;
            return;
        }
        if ($Time <= $now)
        {
            $e = new InvalidNewExecutionTimeException("Cannot set this execution time because current value must be higher than now");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->NextExecution = $Time;
    }

    /**
     * @return int Task ID
     */
    public function GetTaskId() : int
    {
        return $this->TaskId;
    }

    /**
     * @return int How many times task was executed
     */
    public function GetExecutedTimes() : int
    {
        return $this->ExecutedTimes;
    }

    /**
     * @ignore
     */
    private static function GetNext() : int
    {
        return ++self::$TaskCounter;
    }
}