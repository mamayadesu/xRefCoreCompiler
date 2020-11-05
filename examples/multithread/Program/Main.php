<?php

namespace Program;

use IO\Console;
use Threading\Threaded;

class Main
{
    private Threaded $myThread;
    public int $thinkUpToVarName = 0;

    public function __construct(array $args)
    {
        Console::WriteLine("[parent] Starting thread");
        $this->myThread = MyClass::Run(["foo", "bar", "hello", "world"], $this);
        Console::WriteLine("[parent] Thread started");

        $child = $this->myThread->GetChildThreadedObject(); // \Threading\ChildThreadedObject

        while (true)
        {
            $this->thinkUpToVarName++;
            Console::WriteLine("[parent] Encounter " . $this->thinkUpToVarName);
            if ($this->thinkUpToVarName == 15)
            {
                $this->myThread->WaitForChildAccess(); // now parent thread is frozen until child thread will do something with parent
            }
            if ($this->thinkUpToVarName == 20)
            {
                $this->ChildRunning();
                $this->myThread->Kill();
                $this->ChildRunning();
                exit;
            }
            sleep(1);
        }
    }

    public function printSomething() : void
    {
        Console::WriteLine("[parent method] I was called in parent thread!");
    }

    public function Sqr(int $a) : int
    {
        return $a * $a;
    }

    public function ChildRunning() : void
    {
        if ($this->myThread->IsRunning())
        {
            Console::WriteLine("[parent method] Child thread is running");
        }
        else
        {
            Console::WriteLine("[parent method] Child thread is stopped");
        }
    }
}