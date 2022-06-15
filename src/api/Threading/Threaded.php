<?php

namespace Threading;

use \Threading\Exceptions\AccessToClosedThreadException;
use Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\InvalidResultReceivedException;
use \Threading\Exceptions\BadDataAccessException;

/**
 * Provides information and access to child thread
 */

final class Threaded
{
    /**
     * Provides access to public methods and properties of threaded child class
     *
     * @return ChildThreadedObject|null
     */
    public function GetChildThreadedObject() : ?ChildThreadedObject
    {}

    /**
     * Returns PID of child thread
     *
     * @return int PID of child thread
     */
    public function GetChildPid() : int
    {}

    /**
     * Returns port of child thread
     *
     * @return int Port of child thread
     */
    public function GetChildPort() : int
    {}

    /**
     * Returns list of arguments passed by the parent thread
     *
     * @return array<int, string> Arguments passed by the parent thread
     */
    public function GetArguments() : array
    {}

    /**
     * Returns name of threaded class
     *
     * @return string Full name of threaded class
     */
    public function GetClassName() : string
    {}

    /**
     * Returns TRUE if child thread still running
     *
     * @return bool TRUE if thread still running. FALSE thread is closed by any reason
     */
    public function IsRunning() : bool
    {}

    /**
     * Waits for the child thread to begin interacting with the parent thread. The parent thread will be frozen and wait for the child thread to finish synchronizing
     *
     * @return void
     * @throws AccessToClosedThreadException
     * @throws InvalidResultReceivedException
     */
    public function WaitForChildAccess()
    {}

    /**
     * Stop synchronization with child thread
     * @throws BadDataAccessException
     */
    public function FinishSychnorization() : void
    {}

    /**
     * Kills child thread
     */
    public function Kill() : void
    {}
}