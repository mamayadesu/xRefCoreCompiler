<?php

namespace CliForms\MenuBox;

use CliForms\Exceptions\InvalidArgumentsPassed;
use CliForms\Exceptions\InvalidMenuBoxTypeException;
use CliForms\Exceptions\MenuAlreadyOpenedException;
use CliForms\Exceptions\MenuIsNotOpenedException;
use CliForms\Exceptions\NoItemsAddedException;
use CliForms\ListBox\ListBox;
use CliForms\RowHeaderType;
use \Closure;
use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console;

/**
 * Creates customized menu
 */

class MenuBox extends ListBox
{
    protected string $titleForegroundColor = ForegroundColors::CYAN,
        $defaultItemForegroundColor = ForegroundColors::GREEN,
        $defaultItemHeaderForegroundColor = ForegroundColors::GRAY,
        $defaultRowHeaderItemDelimiterForegroundColor = ForegroundColors::DARK_GRAY,
        $inputTitleForegroundColor = ForegroundColors::GRAY,
        $inputTitleDelimiterForegroundColor = ForegroundColors::DARK_GRAY,
        $wrongItemTitleForegroundColor = ForegroundColors::RED,

        $selectedItemHeaderForegroundColor = ForegroundColors::DARK_BLUE,
        $selectedItemDelimiterForegroundColor = ForegroundColors::GRAY,
        $selectedItemRowForegroundColor = ForegroundColors::DARK_PURPLE;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $defaultItemBackgroundColor = BackgroundColors::AUTO,
        $defaultItemHeaderBackgroundColor = BackgroundColors::AUTO,
        $defaultRowHeaderItemDelimiterBackgroundColor = BackgroundColors::AUTO,
        $inputTitleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleDelimiterBackgroundColor = BackgroundColors::AUTO,
        $wrongItemTitleBackgroundColor = BackgroundColors::AUTO,

        $selectedItemHeaderBackgroundColor = BackgroundColors::YELLOW,
        $selectedItemDelimiterBackgroundColor = BackgroundColors::YELLOW,

    protected array/*<MenuBoxItem>*/ $items = [];

    /**
     * @var Closure|null Selected item changed event handler. Function have to accept MenuBox parameter (current MenuBox)
     */
    public ?Closure $SelectedItemChangedEvent = null;

    /**
     * @var Closure|null Selected item clicked event handler. Function have to accept MenuBox parameter (current MenuBox)
     */
    public ?Closure $ClickedEvent = null;

    /**
     * @var Closure|null Menu is opening event handler. Function have to accept MenuBox parameter (current MenuBox)
     */
    public ?Closure $OpenEvent = null;

    /**
     * @var Closure|null Menu is closing event handler. Function have to accept MenuBox parameter (current MenuBox)
     */
    public ?Closure $CloseEvent = null;

    /**
     * MenuBox constructor.
     *
     * @param string $title Title of menu
     * @param object $mythis These arguments are using to access to your class from callback functions
     * @param MenuBoxTypes $menuBoxType
     * @throws InvalidMenuBoxTypeException
     */
    public function __construct(string $title, object $mythis, int $menuBoxType = MenuBoxTypes::InputItemNumberType)
    {}

    /**
     * Add item to collection
     *
     * @param MenuBoxItem $item
     * @return MenuBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem($item) : MenuBox
    {}

    /**
     * Sets zero item to your menu
     *
     * @param MenuBoxItem $item
     * @return MenuBox
     */
    public function SetZeroItem(?MenuBoxItem $item) : MenuBox
    {}

    /**
     * Sets selected item number. If item with specified number doesn't exist, does nothing.
     *
     * @param int $itemNumber
     * @return void
     */
    public function SetSelectedItemNumber(int $itemNumber) : void
    {}

    /**
     * @return int Selected item number
     */
    public function GetSelectedItemNumber() : int
    {}

    /**
     * Closes menu
     * @throws MenuIsNotOpenedException
     */
    public function Close() : void
    {}

    /**
     * Returns TRUE if menu closed
     *
     * @return bool
     */
    public function IsClosed() : bool
    {}

    /**
     * @return MenuBoxItem[] Numbered items. Element with 0 index is zero item (or null)
     */
    public function GetNumberedItems() : array
    {}

    /**
     * Menu will be cleared after every render. Always TRUE if type of MenuBox is KeyPressType
     *
     * @param bool $clear
     * @return MenuBox
     */
    public function SetClearWindowOnRender(bool $clear = true) : MenuBox
    {}

    /**
     * Returns your object which you passed in constructor
     *
     * @return object|null
     */
    public function GetThis() : ?object
    {}

    /**
     * Prints callback output of selected and called MenuBoxItem. It's recommended to use instead of Console::Write, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     */
    public function ResultOutput(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Prints callback output of selected and called MenuBoxItem and moves caret to new line. It's recommended to use instead of Console::WriteLine, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     */
    public function ResultOutputLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * @return void Clears result output
     */
    public function ClearResultOutput() : void
    {}

    /**
     * @return string Result output
     */
    public function GetResultOutput() : string
    {}

    /**
     * Sets title for read line input
     *
     * @param string $inputTitle
     * @return MenuBox
     */
    public function SetInputTitle(string $inputTitle) : MenuBox
    {}

    /**
     * Sets style for read line title
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBox
     */
    public function SetInputTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Sets style for delimiter of read line
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBox
     */
    public function SetInputTitleDelimiterStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Sets description for your menu, which will be displayed between title and items
     *
     * @param string $description
     * @return MenuBox
     */
    public function SetDescription(string $description = "") : MenuBox
    {}

    /**
     * Sets style for description
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     */
    public function SetDescriptionStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Sets title which will be displayed if user selects a non-exists item
     *
     * @param string $title
     * @return MenuBox
     */
    public function SetWrongItemTitle(string $title) : MenuBox
    {}

    /**
     * Sets style for a non-exists item title
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     */
    public function SetWrongItemTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}

    /**
     * Sets style for selected item
     *
     * @param ForegroundColors $headerForegroundColor
     * @param BackgroundColors $headerBackgroundColor
     * @param ForegroundColors $delimiterForegroundColor
     * @param BackgroundColors $delimiterBackgroundColor
     * @param ForegroundColors $itemForegroundColor
     * @param BackgroundColors $itemBackgroundColor
     * @return MenuBox
     */
    public function SetSelectedItemStyle(string $headerForegroundColor, string $headerBackgroundColor, string $delimiterForegroundColor, string $delimiterBackgroundColor, string $itemForegroundColor, string $itemBackgroundColor) : MenuBox
    {}
    
    /**
     * Builds and renders your menu and runs read-line to select menu item
     * @throws NoItemsAddedException
     * @throws MenuAlreadyOpenedException
     */
    public function Render() : void
    {}

    /**
     * Builds and renders your menu and runs read-line to select menu item and returns callback
     *
     * Method returns an empty callback which does nothing if wrong item selected
     *
     * Use this method in do-while and use (!$menu->IsClosed()) as expression to prevent potential memory leak
     *
     * @return callable|null
     * @throws NoItemsAddedException
     */
    public function Render2() : ?callable
    {}
}