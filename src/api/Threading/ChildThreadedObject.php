<?php

namespace Threading;

use Threading\Exceptions\BadDataAccessException;
use \Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\AccessToClosedThreadException;
use \Threading\Exceptions\BadMethodCallException;
use Threading\Exceptions\InvalidResultReceivedException;

/**
 * Provides access to all methods and properties of child threaded class
 *
 * Attention! If you call method or try to get access to property of class, the main thread will be "frozen" until the child thread will call "WaitForParentAccess()"
 */

final class ChildThreadedObject
{}