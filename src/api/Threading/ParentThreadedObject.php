<?php

namespace Threading;

use CliForms\Exceptions\InvalidArgumentsPassed;
use Threading\Exceptions\BadDataAccessException;
use Threading\Exceptions\BadMethodCallException;
use Threading\Exceptions\InvalidArgumentsPassedException;
use Threading\Exceptions\InvalidResultReceivedException;

/**
 * Provides access to all methods and properties of parent threaded object
 *
 * The "parent threaded object" is the second argument of ThreadName::Run()
 *
 * Attention! If you call method or try to get access to property of class, the child thread will be "frozen" until the parent thread will call "WaitForChildAccess()"
 */

final class ParentThreadedObject
{
}