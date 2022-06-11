<?php

namespace Scheduler;

use Scheduler\Exceptions\NotInitializableClassException;

/**
 * @ignore
 */
final class SchedulerMaster
{
    private static ?SchedulerMaster $instance = null;

    private array/*<int, ?AsyncTask>*/ $Queue = array();

    private bool $HasAtLeastOneTask = false;

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

    public function AddTaskToQueue(AsyncTask $task) : void
    {
        $this->Queue[$task->GetTaskId()] = $task;
        if (!$this->HasAtLeastOneTask)
        {
            $this->HasAtLeastOneTask = true;
            register_tick_function([$this, "Handle"]);
        }
    }

    public static function GetInstance() : ?SchedulerMaster
    {
        return self::$instance;
    }

    public function Handle() : void
    {
        if (!$this->HasAtLeastOneTask || count($this->Queue) == 0)
        {
            unregister_tick_function([$this, "Handle"]);
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