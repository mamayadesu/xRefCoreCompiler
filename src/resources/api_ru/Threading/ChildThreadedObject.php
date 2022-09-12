<?php

namespace Threading;

use Threading\Exceptions\BadDataAccessException;
use \Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\AccessToClosedThreadException;
use \Threading\Exceptions\BadMethodCallException;
use Threading\Exceptions\InvalidResultReceivedException;

/**
 * Предоставляет доступ ко всем методам и свойствам класса дочернего потока.
 *
 * Внимание! Если вы вызываете метод или пытаетесь получить доступ к свойству класса, основной поток будет "заморожен" до тех пор, пока дочерний поток не вызовет "WaitForParentAccess()"
 */

final class ChildThreadedObject
{}