<?php
declare(ticks = 1);

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
     * @ignore
     */
    private string $descriptionForegroundColor = ForegroundColors::BROWN;

    /**
     * @ignore
     */
    private string $lastPressedKey = "";

    /**
     * @ignore
     */
    private int $menuBoxType;

    /**
     * @ignore
     */
    private string $descriptionBackgroundColor = BackgroundColors::AUTO;

    /**
     * @ignore
     */
    private string $resultOutput = "";

    /**
     * @ignore
     */
    private string $inputTitle = "Input number of item which you want",
        $wrongItemTitle = "Wrong item selected. Please select the correct item.",
        $description = "";

    /**
     * @ignore
     */
    private ?object $mythis = null;

    protected array/*<MenuBoxItem>*/ $items = [];

    /**
     * @ignore
     */
    private bool $clearOnRender = false, $closeMenu = true, $preventCheckingItems = false, $refreshCalled = false, $callbackExecuting = false;

    /**
     * @ignore
     */
    private ?MenuBoxItem $zeroItem = null;

    /**
     * @ignore
     */
    private ?int $SelectedItemNumber = 1;

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
    {
        if (!MenuBoxTypes::HasItem($menuBoxType))
        {
            $e = new InvalidMenuBoxTypeException("Invalid menu box type given");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        parent::__construct($title);
        $this->mythis = $mythis;
        $this->menuBoxType = $menuBoxType;
        if ($menuBoxType == MenuBoxTypes::KeyPressType)
        {
            $this->clearOnRender = true;
        }
    }
    
    /**
     * @return string Last pressed key on keyboard
     */
    public function GetLastPressedKey() : string
    {
        return $this->lastPressedKey;
    }

    /**
     * Adds item to collection
     *
     * @param MenuBoxControl $item
     * @return MenuBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem(ControlItem $item) : MenuBox
    {
        if (!$item instanceof MenuBoxControl)
        {
            $e = new InvalidArgumentsPassed("Passed item is not a MenuBoxControl item.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->items[] = $item;
        return $this;
    }

    /**
     * Sets zero item to your menu
     *
     * @param MenuBoxItem|null $item
     * @return MenuBox
     * @throws ItemIsUsingException
     */
    public function SetZeroItem(?MenuBoxItem $item) : MenuBox
    {
        if ($item === null)
        {
            if ($this->zeroItem !== null)
            {
                $this->zeroItem->__setattached(null);
            }
            $this->zeroItem = null;
        }
        else
        {
            if ($this->zeroItem !== null)
            {
                $this->zeroItem->__setattached(null);
            }
            if ($item->GetMenuBox() !== $this && $item->GetMenuBox() !== null)
            {
                $e = new ItemIsUsingException("Passed zero item is already using by another running MenuBox");
                $e->Control = $item;
                $e->__xrefcoreexception = true;
                throw $e;
            }
            $this->zeroItem = $item;

            if (!$this->closeMenu)
                $this->zeroItem->__setattached($this);
        }
        return $this;
    }

    /**
     * Sets selected item number. If item with specified number doesn't exist, does nothing.
     *
     * @param int|null $itemNumber
     * @return void
     */
    public function SetSelectedItemNumber(?int $itemNumber) : void
    {
        if ($this->closeMenu)
        {
            $this->SelectedItemNumber = $itemNumber;
            return;
        }
        if ($itemNumber === null || ($this->getnextalloweditemnumber(true) === null && $this->getnextalloweditemnumber(false) === null))
        {
            $this->SelectedItemNumber = null;
            if ($this->SelectedItemChangedEvent != null)
            {
                $event = new SelectedItemChangedEvent();
                $event->MenuBox = $this;
                $event->Item = null;
                $event->ItemNumber = $itemNumber;
                $this->SelectedItemChangedEvent->call($this->mythis, $event);
            }
            return;
        }
        if ($itemNumber > count($this->items) || ($itemNumber == 0 && $this->zeroItem == null))
        {
            return;
        }
        $items = $this->GetSortedItems();
        if (!isset($items[$itemNumber]) || !$items[$itemNumber]->Selectable())
            return;
        $this->SelectedItemNumber = $itemNumber;
        if ($this->SelectedItemChangedEvent != null)
        {
            $event = new SelectedItemChangedEvent();
            $event->MenuBox = $this;
            $event->Item = $items[$itemNumber];
            $event->ItemNumber = $itemNumber;
            $this->SelectedItemChangedEvent->call($this->mythis, $event);
        }
    }

    /**
     * @return int|null Selected item number. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     */
    public function GetSelectedItemNumber() : ?int
    {
        $this->__checkitems();
        return $this->SelectedItemNumber;
    }

    /**
     * Returns current selected item. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     *
     * @return MenuBoxItem|null
     */
    public function GetSelectedItem() : ?MenuBoxItem
    {
        $items = $this->GetSortedItems();
        $itemNumber = $this->GetSelectedItemNumber();
        if ($itemNumber === null)
        {
            return null;
        }
        $item = $items[$itemNumber];
        if (!$item instanceof MenuBoxItem)
        {
            return null;
        }
        return $item;
    }

    /**
     * Returns sorted item number by item. Returns -1 if MenuBox doesn't contain this item.
     *
     * @param MenuBoxControl $item
     * @return int
     */
    public function GetItemNumberByItem(MenuBoxControl $item) : int
    {
        foreach ($this->GetSortedItems() as $key => $i)
        {
            if ($item === $i)
                return $key;
        }
        return -1;
    }

    /**
     * @ignore
     */
    private function checkcurrentitem(bool $changeIndex) : void
    {
        $this->preventCheckingItems = true;
        $items = $this->GetSortedItems();
        if (!isset($items[$this->SelectedItemNumber]) || !$items[$this->SelectedItemNumber] instanceof MenuBoxItem || $items[$this->SelectedItemNumber]->GetMenuBox() !== $this || !$items[$this->SelectedItemNumber]->Selectable())
        {
            $itemNumber = $this->getnextalloweditemnumber(true);
            if ($itemNumber === null || !$items[$itemNumber]->Selectable())
            {
                $itemNumber = $this->getnextalloweditemnumber(false);
                if ($itemNumber === null || !$items[$itemNumber]->Selectable())
                {
                    $this->SetSelectedItemNumber(null);
                }
                else
                {
                    $this->SetSelectedItemNumber($itemNumber);
                }
            }
            else
            {
                $this->SetSelectedItemNumber($itemNumber);
            }
        }
        if ($changeIndex)
        {
            $this->SetSelectedItemNumber($this->SelectedItemNumber);
        }
        $item = $items[$this->SelectedItemNumber];
        if (!$item instanceof MenuBoxItem)
        {
            $this->SetSelectedItemNumber(null);
        }
    }

    /**
     * Closes menu
     * @throws MenuIsNotOpenedException
     */
    public function Close() : void
    {
        if ($this->closeMenu)
        {
            $e = new MenuIsNotOpenedException("Attempt to close not opened menu.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($this->zeroItem != null)
        {
            $this->zeroItem->__setattached(null);
        }
        foreach ($this->items as $item)
        {if(!$item instanceof MenuBoxControl)continue;
            $item->__setattached(null);
        }
        $this->closeMenu = true;
        if ($this->CloseEvent != null)
        {
            $event = new MenuBoxCloseEvent();
            $event->MenuBox = $this;
            $this->CloseEvent->call($this->mythis, $event);
        }
    }

    /**
     * Returns TRUE if menu closed
     *
     * @return bool
     */
    public function IsClosed() : bool
    {
        return $this->closeMenu;
    }

    /**
     * @param bool $includeZeroItem Includes zero item. Attention! If you exclude zero item, the first index of array will be "1", not "0"
     * @return MenuBoxControl[] Numbered items. Element with 0 index is zero item (or null)
     */
    public function GetNumberedItems(bool $includeZeroItem = true) : array
    {
        $this->__checkitems();
        /** @var array<int, ?MenuBoxControl> $result */$result = array();
        $k = 1;
        if ($includeZeroItem)
        {
            if ($this->zeroItem !== null && $this->zeroItem->GetMenuBox() !== $this)
            {
                $this->zeroItem = null;
            }
            $result = array($this->zeroItem);
        }

        foreach ($this->items as $item)
        {if(!$item instanceof MenuBoxControl)continue;
            if ($item->GetMenuBox() !== $this)
                continue;
            $result[$k] = $item;
            $k++;
        }
        return $result;
    }

    /**
     * @param bool $includeZeroItem Includes zero item. Attention! If you exclude zero item, the first index of array will be "1", not "0"
     * @return MenuBoxControl[] Sorted and numbered items. Element with 0 index is still zero item (or null). Please note that the indexes of this method are different from those of the GetNumberedItems method.
     */
    public function GetSortedItems(bool $includeZeroItem = true) : array
    {
        $this->__checkitems();
        /** @var array<int, ?MenuBoxControl> $result */$result = array();
        $k = 1;
        if ($includeZeroItem)
        {
            $result = array($this->zeroItem);
        }

        $copiedArray = $this->items;

        foreach ($this->items as $item)
        {if(!$item instanceof MenuBoxControl)continue;
            /** @var ?MenuBoxControl $maxItem */$maxItem = null;
            $maxItemIndex = -1;
            foreach ($copiedArray as $index => $copiedItem)
            {if(!$copiedItem instanceof MenuBoxControl)continue;
                if ($maxItem == null || $copiedItem->Ordering() < $maxItem->Ordering())
                {
                    $maxItem = $copiedItem;
                    $maxItemIndex = $index;
                }
            }
            array_splice($copiedArray, $maxItemIndex, 1);
            $result[$k] = $maxItem;
            $k++;
        }
        return $result;
    }

    /**
     * @ignore
     */
    public function __checkitems(bool $updateIndex = false) : void
    {
        if ($this->zeroItem !== null && $this->zeroItem->GetMenuBox() !== $this)
        {
            $this->zeroItem = null;
        }
        $toRemove = [];
        foreach ($this->items as $key => $item)
        {if(!$item instanceof MenuBoxControl)continue;
            if ($item->GetMenuBox() !== $this)
            {
                $toRemove[] = $key;
            }
        }

        foreach ($toRemove as $index)
        {
            unset($this->items[$index]);
        }

        $this->items = array_values($this->items);

        if (count($toRemove) > 0)
            $this->checkcurrentitem($updateIndex);
    }

    /**
     * Finds item by its ID and returns it. Return NULL if item with specified ID was not found.
     *
     * @param string $id
     * @return MenuBoxControl|null
     */
    public function GetElementById(string $id) : ?MenuBoxControl
    {
        foreach ($this->GetSortedItems() as $item)
        {if(!$item instanceof MenuBoxControl)continue;
            if ($item->Id == $id)
            {
                return $item;
            }
        }
        return null;
    }

    /**
     * Menu will be cleared after every render. Always TRUE if type of MenuBox is KeyPressType
     *
     * @param bool $clear
     * @return MenuBox
     */
    public function SetClearWindowOnRender(bool $clear = true) : MenuBox
    {
        if ($this->menuBoxType == MenuBoxTypes::KeyPressType)
        {
            return $this;
        }
        $this->clearOnRender = $clear;
        return $this;
    }

    /**
     * Returns your object which you passed in constructor
     *
     * @return object|null
     */
    public function GetThis() : ?object
    {
        return $this->mythis;
    }

    /**
     * Prints callback output of selected and called MenuBoxItem. It's recommended to use instead of Console::Write, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     */
    public function ResultOutput(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {
        $this->resultOutput .= ColoredString::Get($text, $foregroundColor, $backgroundColor);
    }

    /**
     * Prints callback output of selected and called MenuBoxItem and moves caret to new line. It's recommended to use instead of Console::WriteLine, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     */
    public function ResultOutputLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {
        $this->ResultOutput($text . "\n", $foregroundColor, $backgroundColor);
    }

    /**
     * @return void Clears result output
     */
    public function ClearResultOutput() : void
    {
        $this->resultOutput = "";
    }

    /**
     * @return string Result output
     */
    public function GetResultOutput() : string
    {
        return $this->resultOutput;
    }

    /**
     * Sets title for read line input
     *
     * @param string $inputTitle
     * @return MenuBox
     */
    public function SetInputTitle(string $inputTitle) : MenuBox
    {
        $this->inputTitle = $inputTitle;
        return $this;
    }

    /**
     * Sets style for read line title
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBox
     */
    public function SetInputTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->inputTitleForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->inputTitleBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    /**
     * Sets style for delimiter of read line
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBox
     */
    public function SetInputTitleDelimiterStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->inputTitleDelimiterForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->inputTitleDelimiterBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    /**
     * Sets description for your menu, which will be displayed between title and items
     *
     * @param string $description
     * @return MenuBox
     */
    public function SetDescription(string $description = "") : MenuBox
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Sets style for description
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     */
    public function SetDescriptionStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->descriptionForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->descriptionBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    /**
     * Sets title which will be displayed if user selects a non-exists item
     *
     * @param string $title
     * @return MenuBox
     */
    public function SetWrongItemTitle(string $title) : MenuBox
    {
        $this->wrongItemTitle = $title;
        return $this;
    }

    /**
     * Sets style for a non-exists item title
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     */
    public function SetWrongItemTitleStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        $this->wrongItemTitleForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->wrongItemTitleBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    /**
     * @ignore
     */
    private function formatItem(MenuBoxItem $item, int $k, int $a) : string
    {
        $header = "";
        switch ($this->rowsHeaderType)
        {
            case RowHeaderType::NUMERIC:
                $header = $k . "";
                break;

            case RowHeaderType::STARS:
                $header = "*";
                break;

            case RowHeaderType::DOT1:
                $header = "•";
                break;

            case RowHeaderType::DOT2:
                $header = "○";
                break;

            case RowHeaderType::ARROW1:
                $header = ">";
                break;

            case RowHeaderType::ARROW2:
                $header = "->";
                break;

            case RowHeaderType::ARROW3:
                $header = "→";
                break;
        }

        $selected = $this->GetSelectedItem() !== null && $this->SelectedItemNumber == $a && $this->menuBoxType == MenuBoxTypes::KeyPressType;

        $headerFg = $item->HeaderForegroundColor;
        $headerBg = $item->HeaderBackgroundColor;

        $delimiterFg = $item->DelimiterForegroundColor;
        $delimiterBg = $item->DelimiterBackgroundColor;
        if ($selected)
        {
            $headerFg = $item->HeaderSelectedForegroundColor;
            $headerBg = $item->HeaderSelectedBackgroundColor;

            $delimiterFg = $item->DelimiterSelectedForegroundColor;
            $delimiterBg = $item->DelimiterSelectedBackgroundColor;
        }

        if ($item->Disabled())
        {
            $headerFg = $item->HeaderDisabledForegroundColor;
            $headerBg = $item->HeaderDisabledBackgroundColor;

            $delimiterFg = $item->DelimiterDisabledForegroundColor;
            $delimiterBg = $item->DelimiterDisabledBackgroundColor;

        }

        if ($selected && $item->Disabled())
        {
            $headerFg = $item->HeaderSelectedDisabledForegroundColor;
            $headerBg = $item->HeaderSelectedDisabledBackgroundColor;

            $delimiterFg = $item->DelimiterSelectedDisabledForegroundColor;
            $delimiterBg = $item->DelimiterSelectedDisabledBackgroundColor;
        }
        $header = ColoredString::Get($header, $headerFg, $headerBg);
        $header .= ColoredString::Get($this->rowHeaderItemDelimiter, $delimiterFg, $delimiterBg);
        if (($this->menuBoxType == MenuBoxTypes::KeyPressType && $this->rowsHeaderType == RowHeaderType::NUMERIC && $a == 0) || $item instanceof Checkbox)
            $header = "";
        $itemName = $item->Render($selected);
        if ($selected)
            $itemName .= "  " . ColoredString::Get($item->Hint(), $item->HintForegroundColor, $item->HintBackgroundColor);
        return $header . $itemName . "\n";
    }

    /**
     * @ignore
     */
    protected function _renderBody(string &$output): void
    {
        $k = 1;
        $a = 0;
        $itemName = "";
        $header = "";
        foreach ($this->GetSortedItems(false) as $item)
        {if (!$item instanceof MenuBoxControl) continue;
            $a++;
            if (!$item->Visible())
                continue;

            $itemName = $item->Render();

            if ($item instanceof MenuBoxDelimiter || $item instanceof Label)
            {
                $output .= $itemName . "\n";
                continue;
            }

            if ($item instanceof MenuBoxItem) $output .= $this->formatItem($item, $k, $a);
            $k++;
        }

        if ($this->zeroItem == null)
        {
            return;
        }
        $output .= "\n";

        $output .= $this->formatItem($this->zeroItem, 0, 0);
    }

    /**
     * @ignore
     */
    private function GetEmptyFunction() : Closure
    {
        return function(MenuBox $menu)
        {
            return function(MenuBox $menu) {};
        };
    }

    /**
     * Renders menu again
     *
     * @return void
     */
    public function Refresh() : void
    {
        $this->__checkitems();
        if ($this->closeMenu || $this->callbackExecuting)
            return;

        Console::ClearWindow();
        Console::WriteLine($this->resultOutput);
        $output = "";
        $this->_renderTitle($output);
        if ($this->description != "")
        {
            $output .= ColoredString::Get($this->description, $this->descriptionForegroundColor, $this->descriptionBackgroundColor) . "\n";
        }
        $this->_renderBody($output);
        if ($this->menuBoxType == MenuBoxTypes::InputItemNumberType)
        {
            $output .= ColoredString::Get($this->inputTitle, $this->inputTitleForegroundColor, $this->inputTitleBackgroundColor);
            $output .= ColoredString::Get(":", $this->inputTitleDelimiterForegroundColor, $this->inputTitleDelimiterBackgroundColor) . " ";
        }
        Console::Write($output);
        $this->refreshCalled = true;
    }

    /**
     * @ignore
     */
    private function getselitem(int $selectedItemId) : ?MenuBoxControl
    {
        if ($selectedItemId == 0)
        {
            return $this->zeroItem;
        }
        if (count($this->items) >= $selectedItemId)
        {
            return $this->GetSortedItems(false)[$selectedItemId];
        }
        return null;
    }

    /**
     * @ignore
     */
    private function getalloweditems() : array
    {
        $result = array();
        foreach ($this->GetSortedItems() as $key => $item)
        {if(!$item instanceof MenuBoxControl)continue;
            if ($item->Selectable())
                $result[$key] = $item;
        }
        return $result;
    }

    /**
     * @ignore
     */
    private function getnextalloweditemnumber(bool $directionForward = true) : ?int
    {
        $items = $this->getalloweditems();
        $hasZeroItem = isset($items[0]);
        if (!$directionForward)
        {
            $items = array_reverse($items, true);
        }
        $first = null;
        $returnNext = false;
        foreach ($items as $key => $item)
        {if(!$item instanceof MenuBoxControl)continue;
            $first = $key;
            if ($returnNext)
            {
                if ($key != 0)
                    return $key;

                return null;
            }
            if ($directionForward)
            {
                if ($this->SelectedItemNumber == 0)
                    return null;

                if ($this->SelectedItemNumber > 0)
                {
                    if ($this->SelectedItemNumber >= $key)
                        continue;
                    else
                        return $key;
                }
            }
            else
            {
                if ($this->SelectedItemNumber == 0)
                {
                    return $first;
                }
                if ($this->SelectedItemNumber == $key)
                    $returnNext = true;
                if ($this->SelectedItemNumber > $key)
                    return $key;
            }
        }
        if ($directionForward && $hasZeroItem)
        {
            return 0;
        }
        return null;
    }

    /**
     * Builds and renders your menu and runs read-line to select menu item
     * @throws NoItemsAddedException
     * @throws MenuAlreadyOpenedException
     * @throws ItemIsUsingException
     */
    public function Render() : void
    {
        if (count($this->items) == 0)
        {
            $e = new NoItemsAddedException("No items added to items collection. Nothing to render.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!$this->closeMenu)
        {
            $e = new MenuAlreadyOpenedException("Cannot render menu because it's already opened");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        // Checking items
        if ($this->zeroItem !== null)
        {
            if ($this->zeroItem->GetMenuBox() !== null && $this->zeroItem->GetMenuBox() !== $this)
            {
                $e = new ItemIsUsingException("Passed zero item is already using by another running MenuBox");
                $e->Control = $this->zeroItem;
                $e->__xrefcoreexception = true;
                throw $e;
            }

            $this->zeroItem->__setattached($this);
        }
        foreach ($this->items as $item)
        {if(!$item instanceof MenuBoxControl)continue;
            if ($item->GetMenuBox() != null && $item->GetMenuBox() !== $this)
            {
                $e = new ItemIsUsingException("Item is already using by another running MenuBox");
                $e->Control = $item;
                $e->__xrefcoreexception = true;
                throw $e;
            }
            $item->__setattached($this);
        }

        // Cleaning garbage
        $this->__checkitems();

        // Checking if selected item correct
        $this->checkcurrentitem(false);
        $this->closeMenu = false; // Open menu again automatically
        if ($this->OpenEvent != null)
        {
            $event = new MenuBoxOpenEvent();
            $event->MenuBox = $this;
            $this->OpenEvent->call($this->mythis, $event);

            // Can be closed from event handler
            if ($this->closeMenu)
            {
                return;
            }
        }
        $output = "";
        $cleared = false;
        $selectedItemId = 0;
        $selectedItemIdStr = "0";
        /** @var MenuBoxItem $selectedItem */$selectedItem = null;
        $wrongItemSelected = false;
        $callbackCalled = false;
        while (!$this->closeMenu)
        {
            $output = "";
            $selectedItem = null;
            $this->_renderTitle($output);
            if ($this->description != "")
            {
                $output .= ColoredString::Get($this->description, $this->descriptionForegroundColor, $this->descriptionBackgroundColor) . "\n";
            }
            $this->_renderBody($output);
            if ($this->menuBoxType == MenuBoxTypes::InputItemNumberType)
            {
                $output .= ColoredString::Get($this->inputTitle, $this->inputTitleForegroundColor, $this->inputTitleBackgroundColor);
                $output .= ColoredString::Get(":", $this->inputTitleDelimiterForegroundColor, $this->inputTitleDelimiterBackgroundColor) . " ";
            }
            if (($this->clearOnRender && !$cleared) || $this->refreshCalled)
            {
                Console::ClearWindow();
                $cleared = true;
                $this->refreshCalled = false;
            }
            Console::Write($this->resultOutput);
            Console::WriteLine($wrongItemSelected ? ColoredString::Get($this->wrongItemTitle, $this->wrongItemTitleForegroundColor, $this->wrongItemTitleBackgroundColor) : "");
            $cleared = false;
            $wrongItemSelected = false;
            Console::Write($output);
            if ($this->menuBoxType == MenuBoxTypes::KeyPressType)
            {
                do
                {
                    if ($this->closeMenu)
                        return;
                    $pressedKey = Console::ReadKey(false);
                    $this->lastPressedKey = $pressedKey;
                    $keyCheck = $pressedKey != "enter" && $pressedKey != "uparrow" && $pressedKey != "downarrow";
                    $this->__checkitems();
                    if ($this->KeyPressEvent != null)
                    {
                        $event = new KeyPressEvent();
                        $event->MenuBox = $this;
                        $event->Key = $pressedKey;
                        $this->KeyPressEvent->call($this->mythis, $event);
                        $this->__checkitems();
                        if ($keyCheck) continue 2;
                    }
                }
                while ($keyCheck);
                if ($pressedKey == "enter")
                    $selectedItem = $this->getselitem($this->SelectedItemNumber);
                else
                {
                    $selectedItemId = $this->getnextalloweditemnumber($pressedKey == "downarrow");
                    if ($selectedItemId !== null)
                        $this->SetSelectedItemNumber($selectedItemId);
                    continue;
                }
            }
            if ($this->menuBoxType == MenuBoxTypes::InputItemNumberType)
            {
                $selectedItemIdStr = Console::ReadLine(false, false);
                $selectedItemId = intval($selectedItemIdStr);
                if (($selectedItem = $this->getselitem($selectedItemId)) == null)
                {
                    $wrongItemSelected = true;
                    continue;
                }
            }

            // Console::ReadLine() returns an empty string if you'll input "0". So it's temporarily broken
            /*if ($selectedItemId == 0 && $selectedItemIdStr != "0")
            {
                $wrongItemSelected = true;
                continue;
            }*/

            if ($selectedItem === null || $selectedItem->Disabled() || !$selectedItem->Selectable())
            {
                continue;
            }
            $this->resultOutput = "";
            if ($this->clearOnRender && !$cleared)
            {
                Console::ClearWindow();
                $cleared = true;
            }
            $callbackCalled = true;
            if ($selectedItem instanceof Checkbox)
            {
                if ($selectedItem instanceof Radiobutton)
                {
                    if (!$selectedItem->Checked())
                        $selectedItem->Checked(true);
                }
                else
                {
                    $selectedItem->Checked(!$selectedItem->Checked());
                }
            }
            else
            {
                $this->callbackExecuting = true;
                $selectedItem->CallOnSelect($this);
                $this->callbackExecuting = false;
            }
        }
    }
}