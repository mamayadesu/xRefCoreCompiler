<?php
declare(ticks = 1);

namespace CliForms\ListBox;

use CliForms\Common\ControlItem;
use CliForms\Exceptions\InvalidArgumentsPassed;
use \CliForms\Exceptions\InvalidHeaderTypeException;
use CliForms\Exceptions\NoItemsAddedException;
use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;
use \Data\String\ColoredString;
use \CliForms\Common\RowHeaderType;
use \IO\Console;

/**
 * Creation of customized lists
 */

class ListBox
{
    /**
     * @var string Title of box
     */
    public string $Title = "";
    protected string $titleForegroundColor = ForegroundColors::PURPLE,
        $defaultItemForegroundColor = ForegroundColors::WHITE,
        $defaultDisabledForegroundColor = ForegroundColors::GRAY,
        $defaultItemHeaderForegroundColor = ForegroundColors::GRAY,
        $defaultRowHeaderItemDelimiterForegroundColor = ForegroundColors::DARK_GRAY;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $defaultItemBackgroundColor = BackgroundColors::AUTO,
        $defaultDisabledBackgroundColor = BackgroundColors::AUTO,
        $defaultItemHeaderBackgroundColor = BackgroundColors::AUTO,
        $defaultRowHeaderItemDelimiterBackgroundColor = BackgroundColors::AUTO;

    protected string $rowsHeaderType = RowHeaderType::NUMERIC;
    protected string $rowHeaderItemDelimiter = ". ";

    /**
     * @var array<ListBoxControl> Control items collection
     */
    protected array/*<ListBoxControl>*/ $items = array();

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
     * Adds item to ListBox collection
     *
     * @param ListBoxControl $item
     * @return ListBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem(ControlItem $item) : ListBox
    {
        if (!$item instanceof ListBoxControl)
        {
            $e = new InvalidArgumentsPassed("Passed item is not a ListBoxControl.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        $this->items[] = $item;
        return $this;
    }

    /**
     * @ignore
     */
    protected function _renderTitle(string &$output) : void
    {
        $coloredTitle = ColoredString::Get($this->Title, $this->titleForegroundColor, $this->titleBackgroundColor);
        $output .= $coloredTitle . "\n";
    }

    /**
     * @ignore
     */
    protected function _renderBody(string &$output) : void
    {
        $k = 1;
        $itemName = "";
        $header = "";

        foreach ($this->items as $item)
        {if (!$item instanceof ListBoxControl) continue;
            $itemName = $item->Render();

            if ($item instanceof ListBoxDelimiter)
            {
                $output .= $itemName . "\n";
                continue;
            }

            switch ($this->rowsHeaderType)
            {
                case RowHeaderType::NONE:
                    $header = "";
                    break;

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