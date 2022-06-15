<?php
declare(ticks = 1);

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
        $selectedItemRowBackgroundColor = BackgroundColors::YELLOW;

    /**
     * @ignore
     */
    private string $descriptionForegroundColor = ForegroundColors::BROWN;

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
    private bool $clearOnRender = false, $closeMenu = true, $render2wrongItemSelected = false, $cleared = false, $callbackCalled = false;

    /**
     * @ignore
     */
    private ?MenuBoxItem $zeroItem = null;

    /**
     * @ignore
     */
    private int $SelectedItemNumber = 1;

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
     * Add item to collection
     *
     * @param MenuBoxItem $item
     * @return MenuBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem($item) : MenuBox
    {
        if (!$item instanceof MenuBoxItem)
        {
            $e = new InvalidArgumentsPassed("Item must be instance of MenuBoxItem, " . get_class($item) . " given.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->items[] = $item;
        return $this;
    }

    /**
     * Sets zero item to your menu
     *
     * @param MenuBoxItem $item
     * @return MenuBox
     */
    public function SetZeroItem(?MenuBoxItem $item) : MenuBox
    {
        $this->zeroItem = $item;
        return $this;
    }

    /**
     * Sets selected item number. If item with specified number doesn't exist, does nothing.
     *
     * @param int $itemNumber
     * @return void
     */
    public function SetSelectedItemNumber(int $itemNumber) : void
    {
        if ($itemNumber > count($this->items) || ($itemNumber == 0 && $this->zeroItem == null))
        {
            return;
        }
        $this->SelectedItemNumber = $itemNumber;
        if ($this->SelectedItemChangedEvent != null)
        {
            $this->SelectedItemChangedEvent->call($this->mythis, $this);
        }
    }

    /**
     * @return int Selected item number
     */
    public function GetSelectedItemNumber() : int
    {
        return $this->SelectedItemNumber;
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
        $this->closeMenu = true;
        if ($this->CloseEvent != null)
        {
            $this->CloseEvent->call($this->mythis, $this);
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
     * @return MenuBoxItem[] Numbered items. Element with 0 index is zero item (or null)
     */
    public function GetNumberedItems() : array
    {
        /** @var array<int, ?MenuBoxItem> $result */$result = array($this->zeroItem);
        foreach ($this->items as $item)
        {if(!$item instanceof MenuBoxItem)continue;
            $result[] = $item;
        }
        return $result;
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
        Console::Write($text, $foregroundColor, $backgroundColor);
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
    {
        $this->selectedItemHeaderForegroundColor = $headerForegroundColor;
        $this->selectedItemHeaderBackgroundColor = $headerBackgroundColor;
        $this->selectedItemDelimiterForegroundColor = $delimiterForegroundColor;
        $this->selectedItemDelimiterBackgroundColor = $delimiterBackgroundColor;
        $this->selectedItemRowForegroundColor = $itemForegroundColor;
        $this->selectedItemRowBackgroundColor = $itemBackgroundColor;
        return $this;
    }

    /**
     * @ignore
     */
    protected function _renderBody(string &$output): void
    {
        $k = 1;
        $itemName = "";
        $header = "";
        foreach ($this->items as $item)
        {if (!$item instanceof MenuBoxItem) continue;
            $itemName = $item->Name;
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

            $headerFg = ($item->HeaderForegroundColor == ForegroundColors::AUTO ? $this->defaultItemHeaderForegroundColor : $item->HeaderForegroundColor);
            $headerBg = ($item->HeaderBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemHeaderBackgroundColor : $item->ItemBackgroundColor);

            $delimiterFg = ($item->DelimiterForegroundColor == ForegroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterForegroundColor : $item->DelimiterForegroundColor);
            $delimiterBg = ($item->DelimiterBackgroundColor == BackgroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterBackgroundColor : $item->DelimiterBackgroundColor);

            $itemFg = ($item->ItemForegroundColor == ForegroundColors::AUTO ? $this->defaultItemForegroundColor : $item->ItemForegroundColor);
            $itemBg = ($item->ItemBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemBackgroundColor : $item->ItemBackgroundColor);

            if ($this->menuBoxType == MenuBoxTypes::KeyPressType && $this->SelectedItemNumber == $k)
            {
                $headerFg = $this->selectedItemHeaderForegroundColor;
                $headerBg = $this->selectedItemHeaderBackgroundColor;

                $delimiterFg = $this->selectedItemDelimiterForegroundColor;
                $delimiterBg = $this->selectedItemDelimiterBackgroundColor;

                $itemFg = $this->selectedItemRowForegroundColor;
                $itemBg = $this->selectedItemRowBackgroundColor;
            }

            $header = ColoredString::Get($header, $headerFg, $headerBg);
            $header .= ColoredString::Get($this->rowHeaderItemDelimiter, $delimiterFg, $delimiterBg);
            $itemName = ColoredString::Get($itemName, $itemFg, $itemBg);
            $output .= $header . $itemName . "\n";
            $k++;
        }

        if ($this->zeroItem == null)
        {
            return;
        }
        $output .= "\n";
        $header = "";
        $item = $this->zeroItem;
        $itemName = $item->Name;
        switch ($this->rowsHeaderType)
        {
            case RowHeaderType::NUMERIC:
                $header = "0";
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
        $headerFg = ($item->HeaderForegroundColor == ForegroundColors::AUTO ? $this->defaultItemHeaderForegroundColor : $item->HeaderForegroundColor);
        $headerBg = ($item->HeaderBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemHeaderBackgroundColor : $item->ItemBackgroundColor);

        $delimiterFg = ($item->DelimiterForegroundColor == ForegroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterForegroundColor : $item->DelimiterForegroundColor);
        $delimiterBg = ($item->DelimiterBackgroundColor == BackgroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterBackgroundColor : $item->DelimiterBackgroundColor);

        $itemFg = ($item->ItemForegroundColor == ForegroundColors::AUTO ? $this->defaultItemForegroundColor : $item->ItemForegroundColor);
        $itemBg = ($item->ItemBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemBackgroundColor : $item->ItemBackgroundColor);

        if ($this->menuBoxType == MenuBoxTypes::KeyPressType && $this->SelectedItemNumber == 0)
        {
            $headerFg = $this->selectedItemHeaderForegroundColor;
            $headerBg = $this->selectedItemHeaderBackgroundColor;

            $delimiterFg = $this->selectedItemDelimiterForegroundColor;
            $delimiterBg = $this->selectedItemDelimiterBackgroundColor;

            $itemFg = $this->selectedItemRowForegroundColor;
            $itemBg = $this->selectedItemRowBackgroundColor;
        }

        $header = ColoredString::Get($header, $headerFg, $headerBg);
        $header .= ColoredString::Get($this->rowHeaderItemDelimiter, $delimiterFg, $delimiterBg);

        if ($this->menuBoxType == MenuBoxTypes::KeyPressType && $this->rowsHeaderType == RowHeaderType::NUMERIC)
        {
            $header = "";
        }
        $itemName = ColoredString::Get($itemName, $itemFg, $itemBg);
        $output .= $header . $itemName . "\n";
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
     * Builds and renders your menu and runs read-line to select menu item
     * @throws NoItemsAddedException
     * @throws MenuAlreadyOpenedException
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
        $this->closeMenu = false; // Open menu again automatically
        if ($this->OpenEvent != null)
        {
            $this->OpenEvent->call($this->mythis, $this);
            if ($this->closeMenu)
            {
                return;
            }
        }
        $output = $selectedItemStr = "";
        $cleared = false;
        $selectedItemId = 0;
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
            if ($this->clearOnRender && !$cleared)
            {
                Console::ClearWindow();
                $cleared = true;
            }
            if (!$callbackCalled)
            {
                Console::Write($this->resultOutput);
            }
            Console::WriteLine($wrongItemSelected ? ColoredString::Get($this->wrongItemTitle, $this->wrongItemTitleForegroundColor, $this->wrongItemTitleBackgroundColor) : "");
            $cleared = false;
            $wrongItemSelected = false;
            Console::Write($output);
            if ($this->menuBoxType == MenuBoxTypes::KeyPressType)
            {
                do
                {
                    $pressedKey = Console::ReadKey();
                }
                while ($pressedKey != "enter" && $pressedKey != "uparrow" && $pressedKey != "downarrow");
                $itemsCount = count($this->items);
                if ($pressedKey == "uparrow")
                {
                    if ($this->SelectedItemNumber == 0)
                    {
                        $this->SetSelectedItemNumber($itemsCount);
                    }
                    else if($this->SelectedItemNumber > 1)
                    {
                        $this->SetSelectedItemNumber($this->SelectedItemNumber - 1);
                    }
                    $callbackCalled = false;
                    continue;
                }
                if ($pressedKey == "downarrow")
                {
                    if ($this->SelectedItemNumber == $itemsCount)
                    {
                        if ($this->zeroItem != null)
                        {
                            $this->SetSelectedItemNumber(0);
                        }
                    }
                    else if($this->SelectedItemNumber < $itemsCount && $this->SelectedItemNumber != 0)
                    {
                        $this->SetSelectedItemNumber($this->SelectedItemNumber + 1);
                    }
                    $callbackCalled = false;
                    continue;
                }
                $selectedItemId = $this->SelectedItemNumber;
                $selectedItemIdStr = $selectedItemId . "";
            }
            if ($this->menuBoxType == MenuBoxTypes::InputItemNumberType)
            {
                $selectedItemIdStr = Console::ReadLine();
                $selectedItemId = intval($selectedItemIdStr);
            }
            $this->resultOutput = "";
            if ($selectedItemId == 0 && $selectedItemIdStr != "0")
            {
                $wrongItemSelected = true;
                continue;
            }
            if ($selectedItemId == 0)
            {
                // if zero item selected but it's null
                if ($this->zeroItem == null)
                {
                    $wrongItemSelected = true;
                    continue;
                }
                $selectedItem = $this->zeroItem;
            }
            if ($selectedItem == null)
            {
                // if item is not still selected
                if (count($this->items) >= $selectedItemId)
                {
                    $selectedItem = $this->items[$selectedItemId - 1];
                }
                else
                {
                    $wrongItemSelected = true;
                    continue;
                }
            }
            if (!$selectedItem instanceof MenuBoxItem)
            {
                echo "\nSelected item is not MenuBoxItem\n";
                var_dump($selectedItem);
                exit(255);
            }
            if ($this->clearOnRender && !$cleared)
            {
                Console::ClearWindow();
                $cleared = true;
            }
            if ($this->ClickedEvent != null)
            {
                $this->ClickedEvent->call($this->mythis, $this);
            }
            $callbackCalled = true;
            $selectedItem->CallOnSelect($this);
        }
    }

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
    {
        if (count($this->items) == 0)
        {
            $e = new NoItemsAddedException("No items added to items collection. Nothing to render.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($this->IsClosed())
        {
            $this->SelectedItemNumber = 1;
            $this->cleared = false;
            $this->callbackCalled = false;
            $this->closeMenu = false;
            if ($this->OpenEvent != null)
            {
                $this->OpenEvent = $this->OpenEvent->call($this->mythis, $this);
                if ($this->closeMenu)
                {
                    return $this->GetEmptyFunction()->call($this, $this);
                }
            }
        }
        $output = $selectedItemStr = "";
        $selectedItemId = 0;
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
        if ($this->clearOnRender && !$this->cleared)
        {
            Console::ClearWindow();
            $cleared = true;
        }
        if (!$this->callbackCalled)
        {
            Console::Write($this->resultOutput);
        }
        Console::WriteLine($this->render2wrongItemSelected ? ColoredString::Get($this->wrongItemTitle, $this->wrongItemTitleForegroundColor, $this->wrongItemTitleBackgroundColor) : "");
        $this->cleared = false;
        $this->render2wrongItemSelected = false;
        Console::Write($output);
        if ($this->menuBoxType == MenuBoxTypes::KeyPressType)
        {
            do
            {
                $pressedKey = Console::ReadKey();
            }
            while ($pressedKey != "enter" && $pressedKey != "uparrow" && $pressedKey != "downarrow");
            $itemsCount = count($this->items);
            if ($pressedKey == "uparrow")
            {
                if ($this->SelectedItemNumber == 0)
                {
                    $this->SetSelectedItemNumber($itemsCount);
                }
                else if($this->SelectedItemNumber > 1)
                {
                    $this->SetSelectedItemNumber($this->SelectedItemNumber - 1);
                }
                $this->callbackCalled = false;
                return $this->GetEmptyFunction()->call($this, $this);
            }
            if ($pressedKey == "downarrow")
            {
                if ($this->SelectedItemNumber == $itemsCount)
                {
                    if ($this->zeroItem != null)
                    {
                        $this->SetSelectedItemNumber(0);
                    }
                }
                else if($this->SelectedItemNumber < $itemsCount && $this->SelectedItemNumber != 0)
                {
                    $this->SetSelectedItemNumber($this->SelectedItemNumber + 1);
                }
                $this->callbackCalled = false;
                return $this->GetEmptyFunction()->call($this, $this);
            }
            $selectedItemId = $this->SelectedItemNumber;
            $selectedItemIdStr = $selectedItemId . "";
        }
        if ($this->menuBoxType == MenuBoxTypes::InputItemNumberType)
        {
            $selectedItemIdStr = Console::ReadLine();
            $selectedItemId = intval($selectedItemIdStr);
        }
        $this->resultOutput = "";
        if ($selectedItemId == 0 && $selectedItemIdStr != "0")
        {
            $this->render2wrongItemSelected = true;
            return $this->GetEmptyFunction()->call($this, $this);
        }
        if ($selectedItemId == 0)
        {
            // if zero item selected but it's null
            if ($this->zeroItem == null)
            {
                $this->render2wrongItemSelected = true;
                return $this->GetEmptyFunction()->call($this, $this);
            }
            $selectedItem = $this->zeroItem;
        }
        if ($selectedItem == null)
        {
            // if item is not still selected
            if (count($this->items) >= $selectedItemId)
            {
                $selectedItem = $this->items[$selectedItemId - 1];
            }
            else
            {
                $this->render2wrongItemSelected = true;
                return $this->GetEmptyFunction()->call($this, $this);
            }
        }
        if (!$selectedItem instanceof MenuBoxItem)
        {
            echo "\nSelected item is not MenuBoxItem\n";
            var_dump($selectedItem);
            exit(255);
        }
        if ($this->clearOnRender && !$this->cleared)
        {
            Console::ClearWindow();
            $this->cleared = true;
        }
        if ($this->ClickedEvent != null)
        {
            $this->ClickedEvent->call($this->mythis, $this);
        }
        $this->callbackCalled = true;
        return $selectedItem->GetCallbackForRender2()->call($selectedItem, $this);
    }
}