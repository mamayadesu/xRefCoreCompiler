<?php

namespace Scheduler;

use HttpServer\Server;
use Scheduler\Exceptions\NotInitializableClassException;

/**
 * Asynchronous tasks manager
 */
final class SchedulerMaster
{

    /**
     * @return array<AsyncTask> All active asynchronous tasks
     */
    public static function GetActiveTasks() : array
    {}
}