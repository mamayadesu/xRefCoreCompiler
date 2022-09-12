<?php

namespace Scheduler;

use HttpServer\Server;
use Scheduler\Exceptions\NotInitializableClassException;

/**
 * Менеджер асинхронных задач
 */
final class SchedulerMaster
{
    /**
     * @return array<AsyncTask> Все активные асинхронные задачи
     */
    public static function GetActiveTasks() : array
    {}
}