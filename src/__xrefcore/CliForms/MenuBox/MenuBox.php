<?php
declare(ticks = 1);

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
use CliForms\MenuBox\Events\OffsetChangedEvent;
use CliForms\MenuBox\Events\SelectedItemChangedEvent;
use \Closure;
use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console;

/**
 * Creation of pseudo-GUI with elements such as buttons, radiobuttons, checkboxes and some others.
 */

class MenuBox extends ListBox
{
    /**
     * @var string This ID is using to find your MenuBox. Not for anything else.
     */
    public string $Id = "";

    /**
     * @ignore
     */
    public bool $__updatesortedcache = true, $__updateallowedcache = true, $__updatemaxoffsetvaluecache = true;

    /**
     * @ignore
     */
    private array $sortedCache = [], $allowedCache = [];

    /**
     * @ignore
     */
    private ?int $maxOffsetValueCache = 0;

    /**
     * @ignore
     * @var array<MenuBox>
     */
    private static array $MenuBoxes = array();

    protected string $titleForegroundColor = ForegroundColors::CYAN,
        $inputTitleForegroundColor = ForegroundColors::GRAY,
        $inputTitleDelimiterForegroundColor = ForegroundColors::DARK_GRAY;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleBackgroundColor = BackgroundColors::AUTO,
        $inputTitleDelimiterBackgroundColor = BackgroundColors::AUTO;

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
    private string $descriptionBackgroundColor = BackgroundColors::AUTO;

    /**
     * @ignore
     */
    private string $resultOutput = "";

    /**
     * @ignore
     */
    private string $description = "",
        $scrollUpCharacter = "↑",
        $scrollDownCharacter = "↓";

    /**
     * @ignore
     */
    private ?object $mythis = null;

    /**
     * @var array<MenuBoxControl>
     */
    protected array $items = [];

    /**
     * @ignore
     */
    private bool $DISPOSED = false, $closeMenu = true, $preventOffsetChangedEvent = false, $preventSelectedChangedEvent = false, $superPreventRefresh = false, $preventRefresh = false, $preventCheckingItems = false, $refreshCalled = false, $callbackExecuting = false;

    /**
     * @ignore
     */
    private ?MenuBoxItem $zeroItem = null;

    /**
     * @ignore
     */
    private ?int $SelectedItemNumber = 1, $itemsContainerHeight = 0, $scrollOffset = 0;

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
    {
        parent::__construct($title);
        $this->mythis = $mythis;
        self::$MenuBoxes[] = $this;
    }

    /**
     * Returns a count of items which can be rendered. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     * @throws MenuBoxDisposedException
     */
    public function ItemsContainerHeight(?int $newValue = null) : int
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($newValue === null)
        {
            return $this->itemsContainerHeight;
        }

        if ($newValue < 0)
        {
            $newValue = 1;
        }
        $this->__updatemaxoffsetvaluecache = true;
        $this->itemsContainerHeight = $newValue;
        $this->Refresh();
        return $newValue;
    }

    /**
     * Returns a scroll offset from top. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     * @throws MenuBoxDisposedException
     */
    public function ScrollOffset(?int $newValue = null) : int
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($newValue === null)
        {
            return $this->scrollOffset;
        }

        if ($newValue < 0)
        {
            $newValue = 0;
        }

        $maxValue = $this->getOffsetMaxValue();
        $oldValue = $this->scrollOffset;
        if ($newValue > $maxValue)
        {
            $newValue = $maxValue;
        }

        $callEvent = ($this->scrollOffset != $newValue);

        $this->scrollOffset = $newValue;

        if ($callEvent && $this->OffsetChangedEvent !== null && !$this->preventOffsetChangedEvent)
        {
            $this->preventOffsetChangedEvent = true;
            $event = new OffsetChangedEvent();
            $event->MenuBox = $this;
            $event->PreviousOffset = $oldValue;
            $event->Offset = $newValue;
            $this->OffsetChangedEvent->call($this->mythis, $event);
            $this->preventOffsetChangedEvent = false;
        }

        if (!$this->callbackExecuting)
            $this->Refresh();
        return $newValue;
    }

    /**
     * Returns TRUE if this MenuBox contains this item
     *
     * @param MenuBoxControl $control
     * @return bool
     * @throws MenuBoxDisposedException
     */
    public function HasItem(MenuBoxControl $control) : bool
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        return in_array($control, $this->items, true) || $this->zeroItem === $control;
    }

    /**
     * Returns a character which displays in top of items container if there are items above. If you pass new value, it will be changed
     *
     * @param string|null $newValue
     * @return string
     * @throws MenuBoxDisposedException
     */
    public function ScrollUpCharacter(?string $newValue = null) : string
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($newValue === null)
        {
            return $this->scrollUpCharacter;
        }

        $this->scrollUpCharacter = $newValue;
        $this->Refresh();
        return $newValue;
    }

    /**
     * Returns a character which displays in bottom of items container if there are items below. If you pass new value, it will be changed
     *
     * @param string|null $newValue
     * @return string
     * @throws MenuBoxDisposedException
     */
    public function ScrollDownCharacter(?string $newValue = null) : string
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($newValue === null)
        {
            return $this->scrollDownCharacter;
        }

        $this->scrollDownCharacter = $newValue;
        $this->Refresh();
        return $newValue;
    }

    /**
     * @return bool TRUE if current MenuBox is disposed
     */
    public function IsDisposed() : bool
    {
        return $this->DISPOSED;
    }

    /**
     * Disposes current MenuBox and makes unavailable for any actions
     *
     * @return void
     * @throws MenuBoxDisposedException MenuBox is already disposed
     * @throws MenuBoxCannotBeDisposedException MenuBox is still opened
     */
    public function Dispose() : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!$this->closeMenu)
        {
            $e = new MenuBoxCannotBeDisposedException("MenuBox cannot be disposed right now because it is still opened.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->SetZeroItem(null);

        $this->items = [];
        $this->sortedCache = [];
        $this->allowedCache = [];
        $this->setcachestoupdate();
        $this->SelectedItemChangedEvent = null;
        $this->OpenEvent = null;
        $this->CloseEvent = null;
        $this->KeyPressEvent = null;
        $this->resultOutput = "";
        $this->mythis = null;
        $this->Id = "";

        foreach (self::$MenuBoxes as $key => $value)
        {
            if ($value === $this)
            {
                unset(self::$MenuBoxes[$key]);
                break;
            }
        }
        self::$MenuBoxes = array_values(self::$MenuBoxes);
        $this->DISPOSED = true;
    }

    /**
     * Finds and returns MenuBox with the same ID. Returns NULL if MenuBox not found.
     *
     * @param string $id
     * @return MenuBox|null
     */
    public static function GetMenuBoxById(string $id) : ?MenuBox
    {
        $result = null;
        foreach (self::$MenuBoxes as $key => $menu)
        {if(!$menu instanceof MenuBox)continue;
            if ($menu->IsDisposed())
            {
                continue;
            }
            if ($menu->Id === $id)
            {
                $result = $menu;
            }
        }
        return $result;
    }

    /**
     * @return string Last pressed key on keyboard
     * @throws MenuBoxDisposedException
     */
    public function GetLastPressedKey() : string
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $this->lastPressedKey;
    }

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
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!$item instanceof MenuBoxControl)
        {
            $e = new InvalidArgumentsPassed("Passed item is not a MenuBoxControl item.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!$item->__canbeattached())
        {
            $e = new ItemIsUsingException("Passed item is already using by MenuBox");
            $e->Control = $item;
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $item->__setattached($this, $this->closeMenu);
        $this->items[] = $item;
        $this->setcachestoupdate();
        if (!$this->closeMenu)
            $this->Refresh();
        return $this;
    }

    /**
     * Clears items
     *
     * @param bool $removeZeroItem
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function ClearItems(bool $removeZeroItem = true) : MenuBox
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->Refresh();
        $this->items = array();
        $this->sortedCache = array();
        $this->allowedCache = array();
        if ($removeZeroItem)
        {
            $this->zeroItem = null;
        }
        $this->setcachestoupdate();
        return $this;
    }

    /**
     * Removes item from this MenuBox
     *
     * @param MenuBoxControl $control
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function RemoveItem(MenuBoxControl $control) : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        if ($control === $this->zeroItem)
        {
            $this->zeroItem = null;
            $this->Refresh();
        }
        if (in_array($control, $this->items, true))
        {
            $key = array_keys($this->items, $control, true)[0];
            unset($this->items[$key]);
            $this->items = array_values($this->items);
            $this->setcachestoupdate();
            $this->Refresh();
        }
    }

    /**
     * Sets zero item to your menu
     *
     * @param MenuBoxItem|null $item
     * @return MenuBox
     * @throws ItemIsUsingException
     * @throws MenuBoxDisposedException
     */
    public function SetZeroItem(?MenuBoxItem $item) : MenuBox
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($item === null)
        {
            if ($this->zeroItem !== null)
            {
                $this->zeroItem->__setattached(null, true);
            }
            $this->zeroItem = null;
        }
        else
        {
            if ($this->zeroItem !== null)
            {
                $this->zeroItem->__setattached(null, true);
            }
            if ($item->GetMenuBox() !== $this && !$item->__canbeattached())
            {
                $e = new ItemIsUsingException("Passed zero item is already using by another running MenuBox");
                $e->Control = $item;
                $e->__xrefcoreexception = true;
                throw $e;
            }
            $this->zeroItem = $item;
            $this->zeroItem->__setattached($this, $this->closeMenu);
        }
        $this->sortedCache[0] = $this->zeroItem;
        return $this;
    }

    /**
     * Sets selected item number. If item with specified number doesn't exist, does nothing.
     *
     * @param int|null $itemNumber
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function SetSelectedItemNumber(?int $itemNumber) : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        /*if ($this->closeMenu)
        {
            $this->SelectedItemNumber = $itemNumber;
            return;
        }*/
        if ($itemNumber === null || ($this->getnextalloweditemnumber(true) === null && $this->getnextalloweditemnumber(false) === null))
        {
            $this->SelectedItemNumber = null;
            if ($this->SelectedItemChangedEvent != null && !$this->preventSelectedChangedEvent)
            {
                $this->preventSelectedChangedEvent = true;
                $event = new SelectedItemChangedEvent();
                $event->MenuBox = $this;
                $event->Item = null;
                $event->ItemNumber = $itemNumber;
                $this->SelectedItemChangedEvent->call($this->mythis, $event);
                $this->preventSelectedChangedEvent = false;
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

        $this->preventRefresh = true;
        if ($this->ItemsContainerHeight() == 0)
        {
            // Do nothing?
        }

        // if item is above
        else if ($this->SelectedItemNumber <= $this->ScrollOffset() && $this->SelectedItemNumber != 0)
        {
            $this->ScrollOffset($this->SelectedItemNumber - 1);
        }

        // if item is below
        else if ($this->SelectedItemNumber == 0 || $this->SelectedItemNumber > $this->ScrollOffset() + $this->ItemsContainerHeight())
        {
            if ($this->SelectedItemNumber == 0)
            {
                $this->ScrollOffset($this->getOffsetMaxValue());
            }
            else
            {
                $hiddenItems = 0;
                $j = $this->SelectedItemNumber + 1;
                for ($i = $this->SelectedItemNumber; $i >= $this->SelectedItemNumber - $this->ItemsContainerHeight(); $i--)
                {
                    $j--;
                    if ($i <= 0 || $j <= 0)
                    {
                        break;
                    }
                    if (!$items[$j]->Visible())
                    {
                        $hiddenItems++;
                        $i++;
                    }
                }
                if ($this->SelectedItemNumber > $this->ScrollOffset() + $this->ItemsContainerHeight() + $hiddenItems)
                {
                    $difference = $this->SelectedItemNumber - $this->ItemsContainerHeight() - $hiddenItems;
                    $this->ScrollOffset($difference);
                }
            }
        }
        $this->preventRefresh = false;
        $this->Refresh();
    }

    /**
     * @return int|null Selected item number. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     * @throws MenuBoxDisposedException
     */
    public function GetSelectedItemNumber() : ?int
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $this->SelectedItemNumber;
    }

    /**
     * Returns current selected item. If for some reason the current element is not selected, the method will automatically select the closest available one. If there are no such elements, it will return NULL
     *
     * @return MenuBoxItem|null
     * @throws MenuBoxDisposedException
     */
    public function GetSelectedItem() : ?MenuBoxItem
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
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
     * @throws MenuBoxDisposedException
     */
    public function GetItemNumberByItem(MenuBoxControl $item) : int
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
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
    private function getOffsetMaxValue() : int
    {
        if (!$this->__updatemaxoffsetvaluecache)
        {
            return $this->maxOffsetValueCache;
        }
        $items = $this->GetSortedItems(false);
        $count = count($items);
        $hiddenItems = 0;
        $j = $count + 1;
        for ($i = $count; $i >= $count - $this->ItemsContainerHeight(); $i--)
        {
            $j--;
            if ($i <= 0 || $j <= 0)
            {
                break;
            }
            if (!$items[$j]->Visible())
            {
                $hiddenItems++;
                $i++;
            }
        }
        $this->__updatemaxoffsetvaluecache = false;
        $this->maxOffsetValueCache = max($count - $hiddenItems - $this->itemsContainerHeight, 0);
        return $this->maxOffsetValueCache;
    }

    /**
     * @ignore
     */
    public function __checkcurrentitem(bool $changeIndex) : void
    {
        $this->preventCheckingItems = true;
        $items = $this->GetSortedItems();
        if (!isset($items[$this->SelectedItemNumber]) || !$items[$this->SelectedItemNumber] instanceof MenuBoxItem || $items[$this->SelectedItemNumber]->GetMenuBox() !== $this || !$items[$this->SelectedItemNumber]->Selectable())
        {
            $itemNumber = $this->getnextalloweditemnumber(true);
            if ($itemNumber === null || !$items[$itemNumber]->Selectable())
            {
                $itemNumber = $this->getnextalloweditemnumber(false);
                if ($itemNumber === null || !$items[$itemNumber] || !$items[$itemNumber]->Selectable())
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
        $item = $items[$this->SelectedItemNumber] ?? null;
        if (!$item instanceof MenuBoxItem)
        {
            $this->SetSelectedItemNumber(null);
        }
    }

    /**
     * Closes menu
     * @throws MenuIsNotOpenedException
     * @throws MenuBoxDisposedException
     */
    public function Close() : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($this->closeMenu)
        {
            $e = new MenuIsNotOpenedException("Attempt to close not opened menu.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($this->zeroItem != null)
        {
            $this->zeroItem->__setattached($this, true);
        }
        foreach ($this->items as $item)
        {if(!$item instanceof MenuBoxControl)continue;
            $item->__setattached($this, true);
        }
        $this->closeMenu = true;
        Console::ClearWindow();
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
     * @throws MenuBoxDisposedException
     */
    public function IsClosed() : bool
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $this->closeMenu;
    }

    /**
     * @param bool $includeZeroItem Includes zero item. Attention! If you exclude zero item, the first index of array will be "1", not "0"
     * @return MenuBoxControl[] Numbered items. Element with 0 index is zero item (or null)
     * @throws MenuBoxDisposedException
     */
    public function GetNumberedItems(bool $includeZeroItem = true) : array
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
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
     * @throws MenuBoxDisposedException
     */
    public function GetSortedItems(bool $includeZeroItem = true) : array
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (!$this->__updatesortedcache)
        {
            $result = $this->sortedCache;
            if ($includeZeroItem)
            {
                $result[0] = $this->zeroItem;
            }
            else
            {
                unset($result[0]);
            }
            return $result;
        }
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
        $this->sortedCache = $result;
        $this->sortedCache[0] = $this->zeroItem;
        $this->__updatesortedcache = false;
        return $result;
    }

    /**
     * @ignore
     */
    private function setcachestoupdate() : void
    {
        $this->__updatesortedcache = true;
        $this->__updateallowedcache = true;
        $this->__updatemaxoffsetvaluecache = true;
    }

    /**
     * Finds item by its ID and returns it. Return NULL if item with specified ID was not found.
     *
     * @param string $id
     * @return MenuBoxControl|null
     * @throws MenuBoxDisposedException
     */
    public function GetElementById(string $id) : ?MenuBoxControl
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
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
     * Returns your object which you passed in constructor
     *
     * @return object|null
     * @throws MenuBoxDisposedException
     */
    public function GetThis() : ?object
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $this->mythis;
    }

    /**
     * Prints callback output of selected and called MenuBoxItem. It's recommended to use instead of Console::Write, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @throws MenuBoxDisposedException
     */
    public function ResultOutput(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->resultOutput .= ColoredString::Get($text, $foregroundColor, $backgroundColor);
    }

    /**
     * Prints callback output of selected and called MenuBoxItem and moves caret to new line. It's recommended to use instead of Console::WriteLine, because this method saves output after selecting another item.
     *
     * @param string $text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @throws MenuBoxDisposedException
     */
    public function ResultOutputLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->ResultOutput($text . "\n", $foregroundColor, $backgroundColor);
    }

    /**
     * @return void Clears result output
     * @throws MenuBoxDisposedException
     */
    public function ClearResultOutput() : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->resultOutput = "";
    }

    /**
     * @return string Result output
     * @throws MenuBoxDisposedException
     */
    public function GetResultOutput() : string
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        return $this->resultOutput;
    }

    /**
     * Sets description for your menu, which will be displayed between title and items
     *
     * @param string $description
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetDescription(string $description = "") : MenuBox
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->description = $description;
        return $this;
    }

    /**
     * Sets style for description
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return MenuBox
     * @throws MenuBoxDisposedException
     */
    public function SetDescriptionStyle(string $foregroundColor, string $backgroundColor = BackgroundColors::AUTO) : MenuBox
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->descriptionForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->descriptionBackgroundColor = $backgroundColor;
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

        $selected = $this->GetSelectedItem() !== null && $this->SelectedItemNumber == $a;

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
        if (($this->rowsHeaderType == RowHeaderType::NUMERIC && $a == 0) || $item instanceof Checkbox)
            $header = "";
        $itemName = $item->Render($selected);
        if ($selected)
            $itemName .= "  " . ColoredString::Get($item->Hint(), $item->HintForegroundColor, $item->HintBackgroundColor);
        return $header . $itemName . "\n";
    }

    /**
     * @ignore
     * @param array<MenuBoxControl> $items
     * @return array<MenuBoxControl>
     */
    private function excludeInvisibleItems(array $items) : array
    {
        $newArray = [];
        foreach ($items as $item)
        {
            if ($item->Visible())
            {
                $newArray[] = $item;
            }
        }
        return $newArray;
    }

    /**
     * @ignore
     */
    protected function _renderBody(string &$output): void
    {
        $output .= $this->ScrollOffset() > 0 ? $this->ScrollUpCharacter() . "\n" : "\n";
        $k = 1;
        $a = 0;
        $itemName = "";
        $header = "";
        $renderedItems = 0;
        $scrollDownCharacterRendered = false;
        $allItems = $this->GetSortedItems();
        foreach ($allItems as $key => $item)
        {if (!$item instanceof MenuBoxControl) continue;
            if ($key == 0)
                continue;
            $a++;
            if ($a <= $this->ScrollOffset())
            {
                if ($item->Visible() && !$item instanceof MenuBoxDelimiter && !$item instanceof Label && $item instanceof MenuBoxItem)
                {
                    $k++;
                }

                continue;
            }
            if ($renderedItems >= $this->ItemsContainerHeight() && $this->ItemsContainerHeight() != 0 && $this->SelectedItemNumber != 0)
            {
                // Checking are there not hidden elements below
                $startFrom = $this->ScrollOffset() + 1;
                $has = false;
                $j = $startFrom - 1;
                $b = 0;
                for ($i = $startFrom; $i < count($allItems); $i++)
                {
                    if ($b == $this->ItemsContainerHeight())
                    {
                        break;
                    }
                    $j++;
                    if (!isset($allItems[$j]))
                    {
                        break;
                    }
                    $startFrom++;
                    if (!$allItems[$j]->Visible())
                    {
                        $i--;
                        continue;
                    }
                    $b++;
                }
                for ($i = $startFrom; $i < count($allItems); $i++)
                {
                    if (!isset($allItems[$i]))
                    {
                        break;
                    }

                    if ($allItems[$i]->Visible())
                    {
                        $has = true;
                        break;
                    }
                }
                if (!$has)
                {
                    break;
                }
                $output .= $this->ScrollDownCharacter() . "\n";
                $scrollDownCharacterRendered = true;
                break;
            }
            if (!$item->Visible())
            {
                continue;
            }

            $itemName = $item->Render();

            if ($item instanceof MenuBoxDelimiter || $item instanceof Label)
            {
                $output .= $itemName . "\n";
                $renderedItems++;
                continue;
            }

            if ($item instanceof MenuBoxItem)
            {
                $output .= $this->formatItem($item, $k, $a);
                $renderedItems++;
            }
            $k++;
        }

        if (!$scrollDownCharacterRendered)
        {
            $output .= "\n";
        }

        if ($this->zeroItem == null)
        {
            return;
        }

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
     * Prevents next container's refresh
     * @return void
     */
    public function PreventNextRefresh() : void
    {
        $this->preventRefresh = true;
    }

    /**
     * Renders menu again
     *
     * @return void
     * @throws MenuBoxDisposedException
     */
    public function Refresh() : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($this->superPreventRefresh)
        {
            return;
        }
        if ($this->preventRefresh)
        {
            $this->preventRefresh = false;
            return;
        }
        if ($this->closeMenu || $this->callbackExecuting)
            return;

        $output = $this->resultOutput;
        $this->_renderTitle($output);
        if ($this->description != "")
        {
            $output .= ColoredString::Get($this->description, $this->descriptionForegroundColor, $this->descriptionBackgroundColor) . "\n";
        }
        $this->_renderBody($output);
        Console::ClearWindow($output);
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
        if (!$this->__updateallowedcache)
        {
            return $this->allowedCache;
        }
        $result = array();
        foreach ($this->GetSortedItems() as $key => $item)
        {if(!$item instanceof MenuBoxControl)continue;
            if ($item->Selectable())
                $result[$key] = $item;
        }
        $this->__updateallowedcache = false;
        $this->allowedCache = $result;
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
     * @throws MenuBoxDisposedException
     */
    public function Render() : void
    {
        if ($this->DISPOSED)
        {
            $e = new MenuBoxDisposedException("This MenuBox is disposed. You can't do any actions with this MenuBox.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if (count($this->items) == 0 && $this->zeroItem === null)
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
            if ($this->zeroItem->GetMenuBox() !== $this && !$this->zeroItem->__canbeattached())
            {
                $e = new ItemIsUsingException("Passed zero item is already using by another running MenuBox");
                $e->Control = $this->zeroItem;
                $e->__xrefcoreexception = true;
                throw $e;
            }

            $this->zeroItem->__setattached($this, false);
        }
        foreach ($this->items as $item)
        {if(!$item instanceof MenuBoxControl)continue;
            if ($item->GetMenuBox() !== $this && !$item->__canbeattached())
            {
                $e = new ItemIsUsingException("Item is already using by another running MenuBox");
                $e->Control = $item;
                $e->__xrefcoreexception = true;
                throw $e;
            }
            $item->__setattached($this, false);
        }

        // Checking if selected item correct
        $this->__checkcurrentitem(false);
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
        $__output = "";
        $cleared = false;
        $selectedItemId = 0;
        $selectedItemIdStr = "0";
        /** @var MenuBoxItem $selectedItem */$selectedItem = null;
        $this->wrongItemSelected = false;
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
            $__output = $this->resultOutput;
            $__output .= $output;
            if ((!$cleared) || $this->refreshCalled)
            {
                Console::ClearWindow($__output);
                $cleared = true;
                $this->refreshCalled = false;
            }
            else
            {
                Console::Write($__output);
            }
            $cleared = false;
            $this->wrongItemSelected = false;
            do
            {
                if ($this->closeMenu)
                    return;
                $pressedKey = Console::ReadKey(false);
                $this->lastPressedKey = $pressedKey;
                $keyCheck = $pressedKey != "enter" && $pressedKey != "uparrow" && $pressedKey != "downarrow";
                if ($this->KeyPressEvent !== null)
                {
                    $event = new KeyPressEvent();
                    $event->MenuBox = $this;
                    $event->Key = $pressedKey;
                    $this->KeyPressEvent->call($this->mythis, $event);
                    $this->callbackExecuting = false;
                    if ($keyCheck) continue 2;
                }
            }
            while ($keyCheck);

            if ($pressedKey == "enter" && $this->SelectedItemNumber !== null)
                $selectedItem = $this->getselitem($this->SelectedItemNumber);
            else
            {
                $selectedItemId = $this->getnextalloweditemnumber($pressedKey == "downarrow");
                $this->superPreventRefresh = true;
                if ($selectedItemId !== null)
                    $this->SetSelectedItemNumber($selectedItemId);
                else if ($this->ItemsContainerHeight() > 0)
                {
                    if ($pressedKey == "downarrow")
                    {
                        $offset = count($this->GetSortedItems(false)) - $this->ItemsContainerHeight();
                    }
                    else
                    {
                        $offset = 0;
                    }

                    $this->ScrollOffset($offset);
                }
                $this->superPreventRefresh = false;
                continue;
            }

            // Console::ReadLine() returns an empty string if you'll input "0". So it's temporarily broken
            /*if ($selectedItemId == 0 && $selectedItemIdStr != "0")
            {
                $this->wrongItemSelected = true;
                continue;
            }*/

            if ($selectedItem === null || $selectedItem->Disabled() || !$selectedItem->Selectable())
            {
                continue;
            }
            $this->resultOutput = "";
            if (!$cleared)
            {
                Console::ClearWindow();
                $cleared = true;
            }
            $callbackCalled = true;
            if ($selectedItem instanceof Checkbox)
            {
                $this->callbackExecuting = true;
                if ($selectedItem instanceof Radiobutton)
                {
                    if (!$selectedItem->Checked())
                        $selectedItem->Checked(true);
                }
                else
                {
                    $selectedItem->Checked(!$selectedItem->Checked());
                }
                $this->callbackExecuting = false;
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