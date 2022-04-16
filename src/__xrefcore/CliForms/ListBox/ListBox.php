<?php

namespace CliForms\ListBox;

use CliForms\Exceptions\InvalidArgumentsPassed;
use \CliForms\Exceptions\InvalidHeaderTypeException;
use CliForms\Exceptions\NoItemsAddedException;
use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;
use \Data\String\ColoredString;
use \CliForms\RowHeaderType;
use \IO\Console;

/**
 * Creation of customized lists
 * @package CliForms\ListBox
 */

class ListBox
{
    /**
     * @var string Title of box
     */
    public string $Title = "";
    protected string $titleForegroundColor = ForegroundColors::PURPLE,
        $defaultItemForegroundColor = ForegroundColors::WHITE,
        $defaultItemHeaderForegroundColor = ForegroundColors::GRAY,
        $defaultRowHeaderItemDelimiterForegroundColor = ForegroundColors::DARK_GRAY;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $defaultItemBackgroundColor = BackgroundColors::AUTO,
        $defaultItemHeaderBackgroundColor = BackgroundColors::AUTO,
        $defaultRowHeaderItemDelimiterBackgroundColor = BackgroundColors::AUTO;

    protected string $rowsHeaderType = RowHeaderType::NUMERIC;
    protected string $rowHeaderItemDelimiter = ". ";

    /**
     * @var array<ListBoxItem> Control items collection
     */
    protected array/*<ListBoxItem>*/ $items = array();

    /**
     * Creates new ListBox control
     * @param string $title
     */
    public function __construct(string $title)
    {
        $this->Title = $title;
    }

    /**
     * Clears items
     * @return ListBox
     */
    public function ClearItems() : ListBox
    {
        $this->items = [];
        return $this;
    }

    /**
     * Set color of title. Use ForegroundColors and BackgroundColors enums
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ListBox
     */
    public function SetTitleColor(string $foregroundColor, string $backgroundColor = "") : ListBox
    {
        $this->titleForegroundColor = $foregroundColor;
        if ($backgroundColor != "")
        {
            $this->titleBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    /**
     * Sets default style for all items. You can set custom style for any title
     *
     * @param ForegroundColors $itemForegroundColor
     * @param BackgroundColors $itemBackgroundColor
     * @param ForegroundColors $itemHeaderForegroundColor
     * @param BackgroundColors $itemHeaderBackgroundColor
     * @return ListBox
     */
    public function SetDefaultItemStyle(string $itemForegroundColor, string $itemBackgroundColor, string $itemHeaderForegroundColor = "", string $itemHeaderBackgroundColor = "") : ListBox
    {
        $this->defaultItemForegroundColor = $itemForegroundColor;
        $this->defaultItemBackgroundColor = $itemBackgroundColor;

        if ($itemHeaderForegroundColor != "")
        {
            $this->defaultItemHeaderForegroundColor = $itemHeaderForegroundColor;
        }
        if ($itemHeaderBackgroundColor != "")
        {
            $this->defaultItemHeaderBackgroundColor = $itemHeaderBackgroundColor;
        }
        return $this;
    }

    /**
     * Sets header type for items
     *
     * @param RowHeaderType $headerType
     * @return ListBox
     * @throws InvalidHeaderTypeException
     */
    public function SetRowsHeaderType(string $headerType) : ListBox
    {
        if (!RowHeaderType::HasItem($headerType))
        {
            $e = new InvalidHeaderTypeException("Invalid header type '" . $headerType . "'");
            $e->__xrefcoreexception = true;
            throw $e;
        }

        $this->rowsHeaderType = $headerType;
        return $this;
    }

    /**
     * Sets delimiter between header and item (for example: ". ")
     *
     * @param string $delimiter
     * @return ListBox
     */
    public function SetRowHeaderItemDelimiter(string $delimiter) : ListBox
    {
        $this->rowHeaderItemDelimiter = $delimiter;
        return $this;
    }

    /**
     * Sets style for delimiter between header and item
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return ListBox
     */
    public function SetRowHeaderItemDelimiterStyle(string $foregroundColor, string $backgroundColor) : ListBox
    {
        $this->defaultRowHeaderItemDelimiterForegroundColor = $foregroundColor;
        $this->defaultRowHeaderItemDelimiterBackgroundColor = $backgroundColor;
        return $this;
    }

    /**
     * Addes item to ListBox collection
     *
     * @param ListBoxItem $item
     * @return ListBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem($item) : ListBox
    {
        if (!$item instanceof ListBoxItem)
        {
            $e = new InvalidArgumentsPassed("Item must be instance of ListBoxItem, " . get_class($item) . " given.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->items[] = $item;
        return $this;
    }

    protected function _renderTitle(string &$output) : void
    {
        $coloredTitle = ColoredString::Get($this->Title, $this->titleForegroundColor, $this->titleBackgroundColor);
        $output .= $coloredTitle . "\n";
    }

    protected function _renderBody(string &$output) : void
    {
        $k = 1;
        $itemName = "";
        $header = "";

        foreach ($this->items as $item)
        {if (!$item instanceof ListBoxItem) continue;
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
            $header = ColoredString::Get($header, ($item->HeaderForegroundColor == ForegroundColors::AUTO ? $this->defaultItemHeaderForegroundColor : $item->HeaderForegroundColor), ($item->HeaderBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemHeaderBackgroundColor : $item->ItemBackgroundColor));
            $header .= ColoredString::Get($this->rowHeaderItemDelimiter, ($item->DelimiterForegroundColor == ForegroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterForegroundColor : $item->DelimiterForegroundColor), ($item->DelimiterBackgroundColor == BackgroundColors::AUTO ? $this->defaultRowHeaderItemDelimiterBackgroundColor : $item->DelimiterBackgroundColor));
            $itemName = ColoredString::Get($itemName, ($item->ItemForegroundColor == ForegroundColors::AUTO ? $this->defaultItemForegroundColor : $item->ItemForegroundColor), ($item->ItemBackgroundColor == BackgroundColors::AUTO ? $this->defaultItemBackgroundColor : $item->ItemBackgroundColor));
            $output .= $header . $itemName . "\n";
            $k++;
        }
    }

    /**
     * Builds and renders list
     * @throws NoItemsAddedException
     */
    public function Render() : void
    {
        if (count($this->items) == 0)
        {
            $e = new NoItemsAddedException("No items added to items collection. Nothing to render.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $output = "";
        $this->_renderTitle($output);
        $this->_renderBody($output);
        Console::Write($output);
    }
}