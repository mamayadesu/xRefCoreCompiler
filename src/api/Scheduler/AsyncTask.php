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
     * @param object $MyThis Object context where task will be executed
     * @param int $Interval Execution interval in milliseconds
     * @param bool $RunOnce Execute task once
     * @param callable $TaskCallback Callback-function which will be executed in $MyThis context. Callback should accept two parameters: AsyncTask (your task) and IAsyncTaskParameters (your parameters)
     * @param IAsyncTaskParameters|null $Parameters Additional arguments
     * @throws InvalidIntervalException Interval cannot be less than 1 millisecond
     * @throws InvalidNewExecutionTimeException
     */
    public function __construct(object $MyThis, int $Interval, bool $RunOnce, callable $TaskCallback, ?IAsyncTaskParameters $Parameters = null)
    {}

    /**
     * Sets new "this"
     *
     * @param object $myThis
     * @return void
     */
    public function SetThis(object $myThis) : void
    {}

    /**
     * @return object Object's context for its execution
     */
    public function GetThis() : object
    {}

    /**
     * Execute task manually.
     *
     * @return void
     */
    public function Execute() : void
    {}

    /**
     * Cancel task. If you cancel task, it won't be executable anymore and will be removed from scheduler
     *
     * @return void
     */
    public function Cancel() : void
    {}

    /**
     * @return bool Returns true if task was cancelled
     */
    public function IsCancelled() : bool
    {}

    /**
     * @return IAsyncTaskParameters Additional arguments
     */
    public function GetParameters() : IAsyncTaskParameters
    {}

    /**
     * @return bool Will task be executed once
     */
    public function IsOnce() : bool
    {}

    /**
     * @return bool Was task executed at least once
     */
    public function WasExecuted() : bool
    {}

    /**
     * @return float Next task's execution time in Unixtime format in milliseconds
     */
    public function GetNextExecution() : float
    {}

    /**
     * Sets a new next task's time execution
     *
     * @param float $Time New execution time in Unixtime format in milliseconds. If you don't set time, then the next execution time will be "now + interval"
     * @return void
     * @throws InvalidNewExecutionTimeException
     */
    public function SetNextExecution(float $Time = 0) : void
    {}

    /**
     * @return int Task ID
     */
    public function GetTaskId() : int
    {}

    /**
     * @return int How many times task was executed
     */
    public function GetExecutedTimes() : int
    {}
}