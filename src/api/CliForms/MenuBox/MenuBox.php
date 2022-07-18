<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\Exceptions\InvalidArgumentsPassed;
use CliForms\Exceptions\InvalidMenuBoxTypeException;
use CliForms\Exceptions\ItemIsUsingException;
use CliForms\Exceptions\MenuAlreadyOpenedException;
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
 * Creates customized menu
 */

class MenuBox extends ListBox
{
    protected string $titleForegroundColor = ForegroundColors::CYAN,
        $inputTitleForegroundColor = ForegroundColors::GRAY,
        $inputTitleDelimiterForegroundColor = ForegroundColors::DARK_GRAY,
        $wrongItemTitleForegroundColor = ForegroundColors::RED;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleDelimiterBackgroundColor = BackgroundColors::AUTO,
        $wrongItemTitleBackgroundColor = BackgroundColors::AUTO;

    /**
     * @var Closure|null Selected item changed event handler. Function have to accept `Events\SelectedItemChangedEvent`
     */
    public ?Closure $SelectedItemChangedEvent = null;

    /**
     * @var Closure|null Menu is opening event handler. Function have to accept `Events\MenuBoxOpenEvent`
     */
    public ?Closure $OpenEvent = null;

    /**
     * @var Closure|null Menu is closing event handler. Function have to accept `Events\MenuBoxCloseEvent`
     */
    public ?Closure $CloseEvent = null;

    /**
     * @var Closure|null Key pressed event handler. Works only with MenuBoxTypes::KeyPressType. Function have to accept `Events\KeyPressEvent`
     */
    public ?Closure $KeyPressEvent = null;

    /**
     * MenuBox constructor.
     *
     * @param string $title Title of menu
     * @param object $mythis These arguments are using to access to your class from callback functions
     * @param MenuBoxTypes $menuBoxType
     * @throws InvalidMenuBoxTypeException
     */
    public function __construct(string $title, object $mythis, int $menuBoxType = MenuBoxTypes::KeyPressType)
    {}

    /**
     * @return string Last pressed key on keyboard
     */
    public function GetLastPressedKey() : string
    {}

    /**
     * Adds item to collection
     *
     * @param MenuBoxControl $item
     * @return MenuBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem(ControlItem $item) : MenuBox
    {}

    /**
     * Sets zero item to your menu
     *
     * @param MenuBoxItem|null $item
     * @return MenuBox
     * @throws ItemIsUsingException
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
     * @return int|null Selected item number. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     */
    public function GetSelectedItemNumber() : ?int
    {}

    /**
     * Returns current selected item. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     *
     * @return MenuBoxItem|null
     */
    public function GetSelectedItem() : ?MenuBoxItem
    {}

    /**
     * Returns sorted item number by item. Returns -1 if MenuBox doesn't contain this item.
     *
     * @param MenuBoxControl $item
     * @return int
     */
    public function GetItemNumberByItem(MenuBoxControl $item) : int
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
     * @param bool $includeZeroItem Includes zero item. Attention! If you exclude zero item, the first index of array will be "1", not "0"
     * @return MenuBoxControl[] Numbered items. Element with 0 index is zero item (or null)
     */
    public function GetNumberedItems(bool $includeZeroItem = true) : array
    {}

    /**
     * @param bool $includeZeroItem Includes zero item. Attention! If you exclude zero item, the first index of array will be "1", not "0"
     * @return MenuBoxControl[] Sorted and numbered items. Element with 0 index is still zero item (or null). Please note that the indexes of this method are different from those of the GetNumberedItems method.
     */
    public function GetSortedItems(bool $includeZeroItem = true) : array
    {}

    /**
     * Finds item by its ID and returns it. Return NULL if item with specified ID was not found.
     *
     * @param string $id
     * @return MenuBoxControl|null
     */
    public function GetElementById(string $id) : ?MenuBoxControl
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
     * Renders menu again
     *
     * @return void
     */
    public function Refresh() : void
    {}

    /**
     * Builds and renders your menu and runs read-line to select menu item
     * @throws NoItemsAddedException
     * @throws MenuAlreadyOpenedException
     * @throws ItemIsUsingException
     */
    public function Render() : void
    {}
}