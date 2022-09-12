<?php

namespace CliForms\ListBox;

use CliForms\MenuBox\MenuBoxControl;
use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;

/**
 * ListBoxItem
 */

class ListBoxItem extends MenuBoxControl
{
    public string $HeaderForegroundColor = ForegroundColors::AUTO, $DelimiterForegroundColor = ForegroundColors::AUTO;
    public string $HeaderBackgroundColor = BackgroundColors::AUTO, $DelimiterBackgroundColor = BackgroundColors::AUTO;

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
    {
        $this->DelimiterForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->DelimiterBackgroundColor = $backgroundColor;
        }
        return $this;
    }
}