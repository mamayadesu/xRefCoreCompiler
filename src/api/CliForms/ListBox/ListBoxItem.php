<?php

namespace CliForms\ListBox;

use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;

/**
 * ListBoxItem
 */

class ListBoxItem
{
    /**
     * @var string Displayed name
     */
    public string $Name = "";
    public string $ItemForegroundColor = ForegroundColors::AUTO, $HeaderForegroundColor = ForegroundColors::AUTO, $DelimiterForegroundColor = ForegroundColors::AUTO;
    public string $ItemBackgroundColor = BackgroundColors::AUTO, $HeaderBackgroundColor = BackgroundColors::AUTO, $DelimiterBackgroundColor = BackgroundColors::AUTO;

    public function __construct(string $name = "")
    {}

    /**
     * @param string $name Set new displayed name
     * @return ListBoxItem
     */
    public function SetName(string $name) : ListBoxItem
    {}

    /**
     * Set style for item
     *
     * @param BackgroundColors $foregroundColor
     * @param ForegroundColors $backgroundColor
     * @return ListBoxItem
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {}

    /**
     * Set header style
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ListBoxItem
     */
    public function SetHeaderStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {}

    /**
     * Set style for delimiter between header and item
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ListBoxItem
     */
    public function SetDelimiterStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {}
}