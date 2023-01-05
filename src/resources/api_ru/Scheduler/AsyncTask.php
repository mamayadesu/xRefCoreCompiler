<?php

namespace Scheduler;

use \Closure;
use \Exception;
use Scheduler\Exceptions\InvalidIntervalException;
use Scheduler\Exceptions\InvalidNewExecutionTimeException;
use Throwable;

/**
 * Создаёт асинхронную задачу и добавляет её в очередь.
 * Асинхронные задачи в xRefCore построены на основе тиков и выполняются в одном потоке со всем остальным приложением.
 * Данная реализация асинхронных задач предназначена для выполнения какого-либо лёгкого кода с указанным интервалом.
 *
 * Замечание! Если асинхронная задача работает некорректно или работает не во всех случаях, попробуйте добавить "declare(ticks = 1);" сразу после тега "<?php"
 */

final class AsyncTask
{
    /**
     * @param object $MyThis В контексте этого объекта callback задачи будет выполняться
     * @param int $Interval Интервал выполнения в миллисекундах
     * @param bool $RunOnce Выполнить задачу один раз
     * @param callable $TaskCallback Callback будет выполняться в контексте $MyThis. Callback должен принять два параметра: `AsyncTask` (ваша задача) и IAsyncTaskParameters (ваши переданные параметры)
     * @param IAsyncTaskParameters|null $Parameters Дополнительные параметры
     * @throws InvalidIntervalException Интервал не может быть меньше 1 миллисекунды
     * @throws InvalidNewExecutionTimeException
     */
    public function __construct(object $MyThis, int $Interval, bool $RunOnce, callable $TaskCallback, ?IAsyncTaskParameters $Parameters = null)
    {}

    /**
     * Установить новый "this"
     *
     * @param object $myThis
     * @return void
     */
    public function SetThis(object $myThis) : void
    {}

    /**
     * @return object Объект, в контексте которого выполняется callback
     */
    public function GetThis() : object
    {}

    /**
     * Выполнить задачу вручную
     *
     * @return void
     */
    public function Execute() : void
    {}

    /**
     * Отменяет задачу. Если вы отмените задачу, она больше не будет выполняться.
     *
     * @return void
     */
    public function Cancel() : void
    {}

    /**
     * @return bool Задача отменена
     */
    public function IsCancelled() : bool
    {}

    /**
     * @return IAsyncTaskParameters Дополнительные параметры
     */
    public function GetParameters() : IAsyncTaskParameters
    {}

    /**
     * @return bool Должна ли задача выполняться единожды
     */
    public function IsOnce() : bool
    {}

    /**
     * @return bool Был ли callback выполнен хотя бы один раз
     */
    public function WasExecuted() : bool
    {}

    /**
     * @return float Время следующей итерации задачи в формате Unixtime в миллисекундах
     */
    public function GetNextExecution() : float
    {}

    /**
     * Устанавливает время следующей итерации задачи в миллисекундах
     *
     * @param float $Time Новое время выполнении callback в формате Unixtime в миллисекундах. Если вы не укажете время, новое время будет "текущее время + интервал"
     * @return void
     * @throws InvalidNewExecutionTimeException
     */
    public function SetNextExecution(float $Time = 0) : void
    {}

    /**
     * @return int ID задачи
     */
    public function GetTaskId() : int
    {}

    /**
     * @return int Как много раз callback был вызван
     */
    public function GetExecutedTimes() : int
    {}
}