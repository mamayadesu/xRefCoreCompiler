<?php

namespace Threading;

use Application\Application;
use \Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\SystemMethodCallException;
use \Threading\Exceptions\InvalidResultReceivedException;
use \Threading\Exceptions\BadDataAccessException;
use \Threading\Exceptions\AbstractClassThreadException;
use \Threading\Exceptions\NewThreadException;

/**
 * Allows you to initialize classes in another threads. At the same time, this class is using by the child thread to access the parent
 */

abstract class Thread
{
    /**
     * This method calls automatically in child-thread when it was created.
     *
     * @param array<int, string> $args Arguments passed by the parent thread in the static "Run(array $args, object $parentObject)" method
     */
    abstract public function Threaded(array $args) : void;

    /**
     * Returns all child threads
     *
     * @return array<Threaded>
     */
    final public static function GetAllChildThreads() : array
    {}

    /**
     * Returns a PID of parent thread
     *
     * @return int PID of parent thread
     */
    final function GetParentThreadPid() : int
    {}

    /**
     * Returns a port of parent thread
     *
     * @return int PID of parent thread
     */
    final function GetParentThreadPort() : int
    {}

    /**
     * Returns TRUE if parent thread still running
     *
     * @return bool
     */
    final function IsParentStillRunning() : bool
    {}

    /**
     * @return ParentThreadedObject|null
     */
    final function GetParentThreadedObject() : ?ParentThreadedObject
    {}

    /**
     * Blocks child-thread and waits when parent-thread will join to child-thread
     * @throws InvalidResultReceivedException
     */
    final public function WaitForParentAccess()
    {}

    /**
     * Unjoins and unblocks parent-thread. ATTENTION! Until you call this method, the parent-thread will be frozen!
     * @throws BadDataAccessException
     */
    final public function FinishSychnorization() : void
    {}

    /**
     * Initializes a parallel class
     *
     * @param array<mixed> $args Arguments which child-thread will get in `Threaded(array $args)` method
     * @param object $handler Any object that the child thread can access
     * @return Threaded Object which provides information and access to child-thread
     * @throws AbstractClassThreadException
     * @throws InvalidArgumentsPassedException
     * @throws NewThreadException|SystemMethodCallException
     */
    final public static function Run(array $args, object $handler) : ?Threaded
    {}
}