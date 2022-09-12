<?php

namespace Threading;

use \Threading\Exceptions\AccessToClosedThreadException;
use Threading\Exceptions\InvalidArgumentsPassedException;
use \Threading\Exceptions\InvalidResultReceivedException;
use \Threading\Exceptions\BadDataAccessException;

/**
 * Предоставляет информацию и доступ к дочернему потоку
 */

final class Threaded
{
    /**
     * Предоставляет доступ к публичным методам и свойствам параллельного класса
     *
     * @return ChildThreadedObject|null
     */
    public function GetChildThreadedObject() : ?ChildThreadedObject
    {}

    /**
     * Возвращает PID дочернего потока
     *
     * @return int PID дочернего потока
     */
    public function GetChildPid() : int
    {}

    /**
     * Возвращает порт дочернего потока
     *
     * @return int Порт дочернего потока
     */
    public function GetChildPort() : int
    {}

    /**
     * Возвращает список аргументов, переданных родительским потоком
     *
     * @return array<int, string> Список аргументов, переданных родительским потоком
     */
    public function GetArguments() : array
    {}

    /**
     * Название параллельного класса
     *
     * @return string Путь в пространстве имён и название класса
     */
    public function GetClassName() : string
    {}

    /**
     * Возвращает TRUE, если дочерний поток все еще работает
     *
     * @return bool TRUE, если поток все еще выполняется. FALSE - поток закрыт по любой причине
     */
    public function IsRunning() : bool
    {}

    /**
     * Ожидает, пока дочерний поток начнет взаимодействовать с родительским потоком. Родительский поток будет заморожен и будет ждать, пока дочерний поток завершит синхронизацию.
     *
     * @return void
     * @throws AccessToClosedThreadException
     * @throws InvalidResultReceivedException
     */
    public function WaitForChildAccess()
    {}

    /**
     * Останавливает синхронизацию с дочерним потоком
     *
     * @throws BadDataAccessException
     */
    public function FinishSychnorization() : void
    {}

    /**
     * "Убивает" дочерний поток
     */
    public function Kill() : void
    {}
}