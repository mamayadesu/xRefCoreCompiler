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
     * @ignore
     */
    private static ?SchedulerMaster $instance = null;

    /**
     * @ignore
     */
    private array/*<int, ?AsyncTask>*/ $Queue = array();

    /**
     * @ignore
     */
    private bool $HasAtLeastOneTask = false, $GlobalFunctionRegistered = false;

    /**
     * @ignore
     */
    public function __construct()
    {
        if (self::$instance != null)
        {
            $e = new NotInitializableClassException("You're unable to initialize this class");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        self::$instance = $this;
    }

    /**
     * @ignore
     */
    public function AddTaskToQueue(AsyncTask $task) : void
    {
        $this->Queue[$task->GetTaskId()] = $task;
        if (!$this->HasAtLeastOneTask)
        {
            $this->HasAtLeastOneTask = true;
            $GLOBALS["system.tick_functions"]["schedulermaster"] = [$this, "Handle"];
            if (!$this->GlobalFunctionRegistered)
            {
                $this->GlobalFunctionRegistered = true;
                register_tick_function("__tick_function");
            }
        }
    }

    /**
     * @ignore
     */
    public static function GetInstance() : ?SchedulerMaster
    {
        return self::$instance;
    }

    /**
     * @return array<AsyncTask> All active asynchronous tasks
     */
    public static function GetActiveTasks() : array
    {
        return self::$instance->__getasynctasks();
    }

    /**
     * @ignore
     *
     * @return array<AsyncTask> All active asynchronous tasks
     */
    public function __getasynctasks(bool $removeSystemClasses = true) : array
    {
        /** @var array<AsyncTask> $result */$result = [];
        foreach (self::GetInstance()->Queue as $Task)
        {if(!$Task instanceof AsyncTask)continue;
            if (($removeSystemClasses && ($Task->GetThis() instanceof Server)) || ($Task->IsCancelled() || ($Task->IsOnce() && $Task->WasExecuted())))
                continue;

            $result[] = $Task;
        }
        return $result;
    }

    /**
     * @ignore
     */
    public function __unregister() : void
    {
        $this->HasAtLeastOneTask = false;
        unset($GLOBALS["system.tick_functions"]["schedulermaster"]);
    }

    /**
     * @ignore
     */
    public function Handle() : void
    {
        if (!$this->HasAtLeastOneTask || count($this->Queue) == 0)
        {
            $this->HasAtLeastOneTask = false;
            return;
        }
        $time = intval(microtime(true) * 1000);
        foreach ($this->Queue as $TaskId => $Task)
        {if(!$Task instanceof AsyncTask)continue;
            if ($Task->IsCancelled() || ($Task->IsOnce() && $Task->WasExecuted()))
            {
                unset($this->Queue[$TaskId]);
                unset($Task);
                continue;
            }

            if ($Task->GetNextExecution() <= $time)
            {
                $Task->Execute();
            }
        }
    }
}