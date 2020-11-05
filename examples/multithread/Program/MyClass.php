<?php

namespace Program;

use IO\Console;
use Threading\Thread;

class MyClass extends Thread
{
    public function Threaded(array $args) : void
    {
        Console::WriteLine("[child] arguments");
        var_dump($args);

        $i = 0;
        $parent = $this->GetParentThreadedObject(); // \Threading\ParentThreadedObject
        $a = 0;
        while (true)
        {
            $i++;
            Console::WriteLine("[child] MyClass " . $i);
            if ($i == 10)
            {
                $parent->thinkUpToVarName = 0; // now child thread is frozen until parent thread call WaitForChildAccess()
                $parent->printSomething();
                $this->FinishSychnorization(); // unblocks parent thread and stop sync
            }
            if ($i == 15)
            {
                $a = $parent->Sqr(5);
                $this->FinishSychnorization();
                Console::WriteLine("[child] Result: " . $a);
            }
            sleep(2);
        }
    }
}