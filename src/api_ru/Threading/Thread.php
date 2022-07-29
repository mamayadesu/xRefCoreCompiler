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
 * Позволяет инициализировать классы в других потоках. В то же время этот класс используется дочерним потоком для доступа к родительскому
 */

abstract class Thread
{
    /**
     * Этот метод вызывается автоматически в дочернем потоке при его создании.
     *
     * @param array<int, string> $args Аргументы, переданные родительским потоком в статическом методе "Run(array $args, object $parentObject)"
     */
    abstract public function Threaded(array $args) : void;

    /**
     * Возвращает все дочерние потоки
     *
     * @return array<Threaded>
     */
    final public static function GetAllChildThreads() : array
    {}

    /**
     * Возвращает PID родительского потока
     *
     * @return int PID родительского потока
     */
    final function GetParentThreadPid() : int
    {}

    /**
     * Возвращает порт родительского потока
     *
     * @return int Порт родительского потока
     */
    final function GetParentThreadPort() : int
    {}

    /**
     * Возвращает TRUE, если родительский поток все еще работает
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
     * Блокирует дочерний поток и ждет, когда родительский поток присоединится к дочернему потоку
     *
     * @throws InvalidResultReceivedException
     */
    final public function WaitForParentAccess()
    {}

    /**
     * Отсоединяет и разблокирует родительский поток. ВНИМАНИЕ! Пока вы не вызовете этот метод, родительский поток будет заморожен!
     *
     * @throws BadDataAccessException
     */
    final public function FinishSychnorization() : void
    {}

    /**
     * Инициализирует параллельный класс
     *
     * @param array<mixed> $args Аргументы, которые дочерний поток получит в `Threaded(array $args)`
     * @param object $handler Любой объект, к которому дочерний поток может получить доступ
     * @return Threaded Объект, который предоставляет информацию и доступ к дочернему потоку
     * @throws AbstractClassThreadException
     * @throws InvalidArgumentsPassedException
     * @throws NewThreadException|SystemMethodCallException
     */
    final public static function Run(array $args, object $handler) : ?Threaded
    {}
}