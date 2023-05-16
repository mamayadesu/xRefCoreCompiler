<?php
declare(ticks = 1);

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxItem;
use CliForms\MenuBox\Events\ItemClickedEvent;
use \Closure;
use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Clickable menu box item
 */

class MenuBoxItem extends ListBoxItem
{
    /**
     * @ignore
     */
    private ?Closure $callback = null;

    /**
     * @var ForegroundColors
     */
    public string
        $HeaderForegroundColor = ForegroundColors::GRAY,
        $DelimiterForegroundColor = ForegroundColors::DARK_GRAY,
        $ItemForegroundColor = ForegroundColors::GREEN,

        $HeaderSelectedForegroundColor = ForegroundColors::DARK_BLUE,
        $DelimiterSelectedForegroundColor = ForegroundColors::GRAY,
        $ItemSelectedForegroundColor = ForegroundColors::DARK_PURPLE,

        $HeaderDisabledForegroundColor = ForegroundColors::GRAY,
        $DelimiterDisabledForegroundColor = ForegroundColors::DARK_GRAY,
        $ItemDisabledForegroundColor = ForegroundColors::GRAY,

        $HeaderSelectedDisabledForegroundColor = ForegroundColors::GRAY,
        $DelimiterSelectedDisabledForegroundColor = ForegroundColors::DARK_GRAY,
        $ItemSelectedDisabledForegroundColor = ForegroundColors::GRAY;

    /**
     * @var BackgroundColors
     */
    public string $HeaderSelectedBackgroundColor = BackgroundColors::YELLOW,
        $DelimiterSelectedBackgroundColor = BackgroundColors::YELLOW,
        $ItemSelectedBackgroundColor = BackgroundColors::YELLOW,

        $HeaderDisabledBackgroundColor = BackgroundColors::AUTO,
        $DelimiterDisabledBackgroundColor = BackgroundColors::AUTO,
        $ItemDisabledBackgroundColor = BackgroundColors::AUTO,

        $HeaderSelectedDisabledBackgroundColor = BackgroundColors::RED,
        $DelimiterSelectedDisabledBackgroundColor = BackgroundColors::RED,
        $ItemSelectedDisabledBackgroundColor = BackgroundColors::RED;


    /**
     * MenuBoxItem constructor.
     *
     * @param string $name Displayed item title
     * @param callable|null $callback Runs when item selected
     */
    public function __construct(string $name, string $hint, ?callable $callback = null)
    {
        parent::__construct($name, $hint);
        $this->SetOnSelect($callback);
    }

    /**
     * Sets callback when item selected
     *
     * @param callable $callback
     * @return MenuBoxItem
     */
    public function SetOnSelect(?callable $callback) : MenuBoxItem
    {
        if ($callback === null)
            return $this;
        $this->callback = Closure::fromCallable($callback);
        return $this;
    }

    /**
     * @ignore
     */
    public function CallOnSelect(MenuBox $menu) : void
    {
        if ($this->callback === null)
        {
            return;
        }
        $event = new ItemClickedEvent();
        $event->MenuBox = $menu;
        $event->ItemNumber = $menu->GetItemNumberByItem($this);
        $event->Item = $this;
        $this->callback->call($menu->GetThis(), $event);
    }

    /**
     * @ignore
     */
    public function Render(bool $selected = false) : string
    {
        $foregroundColor = $this->ItemForegroundColor;
        $backgroundColor = $this->ItemBackgroundColor;
        if ($selected)
        {
            $foregroundColor = $this->ItemSelectedForegroundColor;
            $backgroundColor = $this->ItemSelectedBackgroundColor;
        }

        if ($this->Disabled)
        {
            $foregroundColor = $this->ItemDisabledForegroundColor;
            $backgroundColor = $this->ItemDisabledBackgroundColor;
        }

        if ($selected && $this->Disabled)
        {
            $foregroundColor = $this->ItemSelectedDisabledForegroundColor;
            $backgroundColor = $this->ItemSelectedDisabledBackgroundColor;
        }

        return ColoredString::Get($this->Name, $foregroundColor, $backgroundColor);
    }
}