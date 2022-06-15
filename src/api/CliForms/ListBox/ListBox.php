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
    {}

    /**
     * Clears items
     * @return ListBox
     */
    public function ClearItems() : ListBox
    {}

    /**
     * Set color of title. Use ForegroundColors and BackgroundColors enums
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ListBox
     */
    public function SetTitleColor(string $foregroundColor, string $backgroundColor = "") : ListBox
    {}

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
    {}

    /**
     * Sets header type for items
     *
     * @param RowHeaderType $headerType
     * @return ListBox
     * @throws InvalidHeaderTypeException
     */
    public function SetRowsHeaderType(string $headerType) : ListBox
    {}

    /**
     * Sets delimiter between header and item (for example: ". ")
     *
     * @param string $delimiter
     * @return ListBox
     */
    public function SetRowHeaderItemDelimiter(string $delimiter) : ListBox
    {}

    /**
     * Sets style for delimiter between header and item
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return ListBox
     */
    public function SetRowHeaderItemDelimiterStyle(string $foregroundColor, string $backgroundColor) : ListBox
    {}

    /**
     * Addes item to ListBox collection
     *
     * @param ListBoxItem $item
     * @return ListBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem($item) : ListBox
    {}

    protected function _renderTitle(string &$output) : void
    {}

    protected function _renderBody(string &$output) : void
    {}

    /**
     * Builds and renders list
     * @throws NoItemsAddedException
     */
    public function Render() : void
    {}
}