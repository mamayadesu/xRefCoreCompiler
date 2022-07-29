<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\Exceptions\InvalidArgumentsPassed;
use CliForms\Exceptions\InvalidMenuBoxTypeException;
use CliForms\Exceptions\ItemIsUsingException;
use CliForms\Exceptions\MenuAlreadyOpenedException;
use CliForms\Exceptions\MenuBoxCannotBeDesposedException;
use CliForms\Exceptions\MenuBoxDisposedException;
use CliForms\Exceptions\MenuIsNotOpenedException;
use CliForms\Exceptions\NoItemsAddedException;
use CliForms\ListBox\ListBox;
use CliForms\Common\RowHeaderType;
use CliForms\MenuBox\Events\KeyPressEvent;
use CliForms\MenuBox\Events\MenuBoxCloseEvent;
use CliForms\MenuBox\Events\MenuBoxOpenEvent;
use CliForms\MenuBox\Events\SelectedItemChangedEvent;
use \Closure;
use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console;

/**
 * Создание псевдо-GUI с такими элементами как кнопки, радио-кнопки, чекбоксы и некоторые другие
 */

class MenuBox extends ListBox
{
    /**
     * @var string ID нужен лишь для поиска вашего MenuBox и нигде более.
     */
    public string $Id = "";

    protected string $titleForegroundColor = ForegroundColors::CYAN,
        $inputTitleForegroundColor = ForegroundColors::GRAY,
        $inputTitleDelimiterForegroundColor = ForegroundColors::DARK_GRAY,
        $wrongItemTitleForegroundColor = ForegroundColors::RED;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleDelimiterBackgroundColor = BackgroundColors::AUTO,
        $wrongItemTitleBackgroundColor = BackgroundColors::AUTO;

    /**
     * @var Closure|null Обработчик события смены выбранного элемента меню. Callback должен принимать `Events\SelectedItemChangedEvent`
     */
    public ?Closure $SelectedItemChangedEvent = null;

    /**
     * @var Closure|null Обработчик открытия меню. Callback должен принимать `Events\MenuBoxOpenEvent`
     */
    public ?Closure $OpenEvent = null;

    /**
     * @var Closure|null Обработчик закрытия меню. Callback должен принимать `Events\MenuBoxCloseEvent`
     */
    public ?Closure $CloseEvent = null;

    /**
     * @var Closure|null Обработчик нажатия клавиш в открытом MenuBox. Работает только с MenuBoxTypes::KeyPressType. Callback должен принимать `Events\KeyPressEvent`
     */
    public ?Closure $KeyPressEvent = null;

    /**
     * Конструктор MenuBox.
     *
     * @param string $title Название меню
     * @param object $mythis В контексте данного объекта будут выполняться ваши callback-функции
     * @param MenuBoxTypes $menuBoxType
     * @throws InvalidMenuBoxTypeException
     */
    public function __construct(string $title, object $mythis, int $menuBoxType = MenuBoxTypes::KeyPressType)
    {}

    /**
     * @return bool TRUE, если данный MenuBox очищен
     */
    public function IsDisposed() : bool
    {}

    /**
     * Очищает данный MenuBox и делает недоступным для любых дальнейших действий с ним
     *
     * @return void
     * @throws MenuBoxDisposedException MenuBox уже очищен
     * @throws MenuBoxCannotBeDesposedException MenuBox всё ещё открыт
     */
    public function Dispose() : void
    {}

    /**
     * Ищет и возвращает MenuBox с указанным ID. Возвращает NULL, если MenuBox не найден
     *
     * @param string $id
     * @return MenuBox|null
     */
    public static function GetMenuBoxById(string $id) : ?MenuBox
    {}

    /**
     * @return string Последняя нажатая клавиша
     * @throws MenuBoxDisposedException
     */
    public function GetLastPressedKey() : string
    {}

    /**
     * Добавляет элемент в список
     *
     * @param MenuBoxControl $item
     * @return MenuBox
     * @throws InvalidArgumentsPassed
     * @throws ItemIsUsingException
     * @throws MenuBoxDisposedException
     */
    public function AddItem(ControlItem $item) : MenuBox
    {}

    /**
     * Устанавливает нулевой элемент списка
     *
     * @param MenuBoxItem|null $item
     * @return MenuBox
     * @throws ItemIsUsingException
     * @throws MenuBoxDisposedException
     */
    public function SetZeroItem(?MenuBoxItem $item) : MenuBox
    {}

    /**
     * Устанавливает текущий выбранный элемент по его номеру. Если элемента с таким номером в контейнере нет - ничего не делает
     *
     * @param int $itemNumber
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function SetSelectedItemNumber(int $itemNumber) : void
    {}

    /**
     * @return int|null Номер выбранного элемента. Если по какой-то причине текущий элемент не выбран, метод автоматически выберет ближайший из доступных. Если таких элементов нет, он вернёт NULL
     * @throws MenuBoxDisposedException
     */
    public function GetSelectedItemNumber() : ?int
    {}

    /**
     * Возвращает текущий выбранный элемент. Если по какой-то причине текущий элемент не выбран, метод автоматически выберет ближайший из доступных. Если таких элементов нет, он вернет NULL
     *
     * @return MenuBoxItem|null
     * @throws MenuBoxDisposedException
     */
    public function GetSelectedItem() : ?MenuBoxItem
    {}

    /**
     * Возвращает отсортированный номер элемента по указанному элементу. Возвращает -1, если MenuBox не содержит данный элемент.
     *
     * @param MenuBoxControl $item
     * @return int
     * @throws MenuBoxDisposedException
     */
    public function GetItemNumberByItem(MenuBoxControl $item) : int
    {}

    /**
     * Закрывает меню
     *
     * @throws MenuIsNotOpenedException
     * @throws MenuBoxDisposedException
     */
    public function Close() : void
    {}

    /**
     * Возвращает TRUE, если меню закрыто
     *
     * @return bool
     * @throws MenuBoxDisposedException
     */
    public function IsClosed() : bool
    {}

    /**
     * @param bool $includeZeroItem Включает нулевой элемент. Внимание! Если вы исключите нулевой элемент, первый индекс массива будет «1», а не «0».
     * @return MenuBoxControl[] Нумерованные элементы. Элемент с индексом 0 является нулевым элементом (или NULL)
     * @throws MenuBoxDisposedException
     */
    public function GetNumberedItems(bool $includeZeroItem = true) : array
    {}

    /**
     * @param bool $includeZeroItem Включает нулевой элемент. Внимание! Если вы исключите нулевой элемент, первый индекс массива будет «1», а не «0».
     * @return MenuBoxControl[] Отсортированные и пронумерованные предметы. Элемент с индексом 0 по-прежнему является нулевым элементом (или NULL). Обратите внимание, что индексы этого метода отличаются от индексов метода GetNumberedItems.
     * @throws MenuBoxDisposedException
     */
    public function GetSortedItems(bool $includeZeroItem = true) : array
    {}

    /**
     * Находит элемент контейнера по его ID и возвращает его. Возвращает NULL, если элемент с указанным идентификатором не найден в этом контейнере.
     *
     * @param string $id
     * @return MenuBoxControl|null
     * @throws MenuBoxDisposedException
     */
    public function GetElementById(string $id) : ?MenuBoxControl
    {}

    /**
     * Меню будет очищаться после каждого рендера. Всегда TRUE, если тип MenuBox — KeyPressType
     *
     * @param bool $clear
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetClearWindowOnRender(bool $clear = true) : MenuBox
    {}

    /**
     * Возвращает ваш $this, который вы передали в конструкторе
     *
     * @return object|null
     * @throws MenuBoxDisposedException
     */
    public function GetThis() : ?object
    {}

    /**
     * Выводит текст в шапку MenuBox. Рекомендуется использовать этот метод вместо Console::Write, так как этот метод сохраняет вывод после выбора другого элемента.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @throws MenuBoxDisposedException
     */
    public function ResultOutput(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Выводит текст в шапку MenuBox и помещает каретку на новую строку. Рекомендуется использовать этот метод вместо Console::WriteLine, так как этот метод сохраняет вывод после выбора другого элемента.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @throws MenuBoxDisposedException
     */
    public function ResultOutputLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Удаляет весь текст, ранее напечатанный через `ResultOutput` и `ResultOutputLine`
     *
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function ClearResultOutput() : void
    {}

    /**
     * @return string Текст, ранее напечатанный через `ResultOutput` и `ResultOutputLine`
     * @throws MenuBoxDisposedException
     */
    public function GetResultOutput() : string
    {}

    /**
     * Устанавливает заголовок для поля выбора элемента
     *
     * @param string $inputTitle
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetInputTitle(string $inputTitle) : MenuBox
    {}

    /**
     * Устанавливает стиль заголовка для поля выбора элемента
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetInputTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Устанавливает стиль разделителя между заголовком для поля выбора элемента и самим полем
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetInputTitleDelimiterStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Устанавливает описание для вашего меню, которое будет отображаться между заголовком и элементами
     *
     * @param string $description
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetDescription(string $description = "") : MenuBox
    {}

    /**
     * Устанавливает стиль для описания
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetDescriptionStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Устанавливает заголовок, который будет отображаться, если пользователь выберет несуществующий элемент
     *
     * @param string $title
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetWrongItemTitle(string $title) : MenuBox
    {}

    /**
     * Устанавливает стиль заголовка для ошибки о несуществующем элементе
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetWrongItemTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Повторно рендерит меню
     *
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function Refresh() : void
    {}

    /**
     * Генерирует и открывает меню
     * @throws NoItemsAddedException
     * @throws MenuAlreadyOpenedException
     * @throws ItemIsUsingException
     * @throws MenuBoxDisposedException
     */
    public function Render() : void
    {}
}