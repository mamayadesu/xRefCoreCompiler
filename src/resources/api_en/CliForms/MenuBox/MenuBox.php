<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\Exceptions\InvalidArgumentsPassed;
use CliForms\Exceptions\ItemIsUsingException;
use CliForms\Exceptions\MenuAlreadyOpenedException;
use CliForms\Exceptions\MenuBoxCannotBeDisposedException;
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
 * Creation of an interactive pseudo-GUI menu with elements such as buttons, radio buttons, checkboxes and some others
 */

class MenuBox extends ListBox
{
    /**
     * @var string This ID is using to find your MenuBox. Not for anything else.
     */
    public string $Id = "";

    protected string $titleForegroundColor = ForegroundColors::CYAN;

    protected string $titleBackgroundColor = BackgroundColors::AUTO;

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
     * @var Closure|null Key pressed event handler. Function have to accept `Events\KeyPressEvent`
     */
    public ?Closure $KeyPressEvent = null;
    
    /**
     * @var Closure|null Offset changed event handler. Function have to accept `Events\OffsetChangedEvent`
     */
    public ?Closure $OffsetChangedEvent = null;

    /**
     * MenuBox constructor.
     *
     * @param string $title Title of menu
     * @param object $mythis These arguments are using to access to your class from callback functions
     */
    public function __construct(string $title, object $mythis)
    {}
    
    /**
     * Returns a count of items which can be rendered. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     * @throws MenuBoxDisposedException
     */
    public function ItemsContainerHeight(?int $newValue = null) : int
    {}

    /**
     * Returns a scroll offset from top. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     * @throws MenuBoxDisposedException
     */
    public function ScrollOffset(?int $newValue = null) : int
    {}
    
    /**
     * Returns TRUE if this MenuBox contains this item
     *
     * @param MenuBoxControl $control
     * @return bool
     * @throws MenuBoxDisposedException
     */
    public function HasItem(MenuBoxControl $control) : bool
    {}

    /**
     * Returns a character which displays in top of items container if there are items above. If you pass new value, it will be changed
     *
     * @param string|null $newValue
     * @return string
     * @throws MenuBoxDisposedException
     */
    public function ScrollUpCharacter(?string $newValue = null) : string
    {}

    /**
     * Returns a character which displays in bottom of items container if there are items below. If you pass new value, it will be changed
     *
     * @param string|null $newValue
     * @return string
     * @throws MenuBoxDisposedException
     */
    public function ScrollDownCharacter(?string $newValue = null) : string
    {}

    /**
     * @return bool TRUE if current MenuBox is disposed
     */
    public function IsDisposed() : bool
    {}

    /**
     * Disposes current MenuBox and makes unavailable for any actions
     *
     * @return void
     * @throws MenuBoxDisposedException MenuBox is already disposed
     * @throws MenuBoxCannotBeDisposedException MenuBox is still opened
     */
    public function Dispose() : void
    {}

    /**
     * Finds and returns MenuBox with the same ID. Returns NULL if MenuBox not found.
     *
     * @param string $id
     * @return MenuBox|null
     */
    public static function GetMenuBoxById(string $id) : ?MenuBox
    {}

    /**
     * @return string Last pressed key on keyboard
     * @throws MenuBoxDisposedException
     */
    public function GetLastPressedKey() : string
    {}

    /**
     * Adds item to collection
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
     * Clears items
     * 
     * @param bool $removeZeroItem
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function ClearItems(bool $removeZeroItem = true) : MenuBox
    {}

    /**
     * Removes item from this MenuBox
     *
     * @param MenuBoxControl $control
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function RemoveItem(MenuBoxControl $control) : void
    {}

    /**
     * Sets zero item to your menu
     *
     * @param MenuBoxItem|null $item
     * @return MenuBox
     * @throws ItemIsUsingException
     * @throws MenuBoxDisposedException
     */
    public function SetZeroItem(?MenuBoxItem $item) : MenuBox
    {}

    /**
     * Sets selected item number. If item with specified number doesn't exist, does nothing.
     *
     * @param int $itemNumber
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function SetSelectedItemNumber(int $itemNumber) : void
    {}

    /**
     * @return int|null Selected item number. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     * @throws MenuBoxDisposedException
     */
    public function GetSelectedItemNumber() : ?int
    {}

    /**
     * Returns current selected item. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     *
     * @return MenuBoxItem|null
     * @throws MenuBoxDisposedException
     */
    public function GetSelectedItem() : ?MenuBoxItem
    {}

    /**
     * Returns sorted item number by item. Returns -1 if MenuBox doesn't contain this item.
     *
     * @param MenuBoxControl $item
     * @return int
     * @throws MenuBoxDisposedException
     */
    public function GetItemNumberByItem(MenuBoxControl $item) : int
    {}

    /**
     * Closes menu
     * @throws MenuIsNotOpenedException
     * @throws MenuBoxDisposedException
     */
    public function Close() : void
    {}

    /**
     * Returns TRUE if menu closed
     *
     * @return bool
     * @throws MenuBoxDisposedException
     */
    public function IsClosed() : bool
    {}

    /**
     * @param bool $includeZeroItem Includes zero item. Attention! If you exclude zero item, the first index of array will be "1", not "0"
     * @return MenuBoxControl[] Numbered items. Element with 0 index is zero item (or null)
     * @throws MenuBoxDisposedException
     */
    public function GetNumberedItems(bool $includeZeroItem = true) : array
    {}

    /**
     * @param bool $includeZeroItem Includes zero item. Attention! If you exclude zero item, the first index of array will be "1", not "0"
     * @return MenuBoxControl[] Sorted and numbered items. Element with 0 index is still zero item (or null). Please note that the indexes of this method are different from those of the GetNumberedItems method.
     * @throws MenuBoxDisposedException
     */
    public function GetSortedItems(bool $includeZeroItem = true) : array
    {}

    /**
     * Finds item by its ID and returns it. Return NULL if item with specified ID was not found.
     *
     * @param string $id
     * @return MenuBoxControl|null
     * @throws MenuBoxDisposedException
     */
    public function GetElementById(string $id) : ?MenuBoxControl
    {}
    
    /**
     * Returns a list of element of specified type
     *
     * @param string $className Full path to class. To simplify, you can pass `Checkbox::class` or `Label::class`
     * @return array<MenuBoxControl>
     * @throws MenuBoxDisposedException
     */
    public function GetElementsByType(string $className) : array
    {}

    /**
     * Returns your object which you passed in constructor
     *
     * @return object|null
     * @throws MenuBoxDisposedException
     */
    public function GetThis() : ?object
    {}

    /**
     * Prints callback output of selected and called MenuBoxItem. It's recommended to use instead of Console::Write, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @throws MenuBoxDisposedException
     */
    public function ResultOutput(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Prints callback output of selected and called MenuBoxItem and moves caret to new line. It's recommended to use instead of Console::WriteLine, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @throws MenuBoxDisposedException
     */
    public function ResultOutputLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * @return void Clears result output
     * @throws MenuBoxDisposedException
     */
    public function ClearResultOutput() : void
    {}

    /**
     * @return string Result output
     * @throws MenuBoxDisposedException
     */
    public function GetResultOutput() : string
    {}

    /**
     * Sets description for your menu, which will be displayed between title and items
     *
     * @param string $description
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetDescription(string $description = "") : MenuBox
    {}

    /**
     * Sets style for description
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetDescriptionStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {}
    
    /**
     * Prevents next container's refresh
     * @return void
     */
    public function PreventNextRefresh() : void
    {}

    /**
     * Renders menu again
     *
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function Refresh() : void
    {}

    /**
     * Builds and renders your menu and runs read-line to select menu item
     * @throws NoItemsAddedException
     * @throws MenuAlreadyOpenedException
     * @throws ItemIsUsingException
     * @throws MenuBoxDisposedException
     */
    public function Render() : void
    {}
}