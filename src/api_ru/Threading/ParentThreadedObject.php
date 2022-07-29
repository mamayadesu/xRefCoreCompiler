<?php

namespace Threading;

use CliForms\Exceptions\InvalidArgumentsPassed;
use Threading\Exceptions\BadDataAccessException;
use Threading\Exceptions\BadMethodCallException;
use Threading\Exceptions\InvalidArgumentsPassedException;
use Threading\Exceptions\InvalidResultReceivedException;

/**
 * Предоставляет доступ ко всем методам и свойствам класса родительского потока.
 *
 * Класс родительского потока - это второй параметр, который вы передаёте в методе ИмяКласса::Run()
 *
 * Внимание! Если вы вызываете метод или пытаетесь получить доступ к свойству класса, дочерний поток будет "заморожен" до тех пор, пока родительский поток не вызовет "WaitForChildAccess()"
 */

final class ParentThreadedObject
{}