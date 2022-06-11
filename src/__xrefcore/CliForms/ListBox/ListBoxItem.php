<?php
declare(ticks = 1);

namespace CliForms\ListBox;

use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;

/**
 * ListBoxItem
 * @package CliForms\ListBox
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
    {
        $this->SetName($name);
    }

    /**
     * @param string $name Set new displayed name
     * @return ListBoxItem
     */
    public function SetName(string $name) : ListBoxItem
    {
        $this->Name = $name;
        return $this;
    }

    /**
     * Set style for item
     *
     * @param BackgroundColors $foregroundColor
     * @param ForegroundColors $backgroundColor
     * @return ListBoxItem
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {
        $this->ItemForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->ItemBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    /**
     * Set header style
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ListBoxItem
     */
    public function SetHeaderStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ListBoxItem
    {
        $this->HeaderForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->HeaderBackgroundColor = $backgroundColor;
        }
        return $this;
    }

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