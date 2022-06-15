<?php
declare(ticks = 1);

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxItem;
use \Closure;

/**
 * MenuBoxItem
 */

class MenuBoxItem extends ListBoxItem
{
    /**
     * @ignore
     */
    private ?Closure $callback = null;

    /**
     * MenuBoxItem constructor.
     *
     * @param string $name Displayed item title
     * @param callable|null $callback Runs when item selected
     */
    public function __construct(string $name, ?callable $callback = null)
    {
        parent::__construct($name);
        $this->SetOnSelect($callback);
    }

    /**
     * Sets callback when item selected
     *
     * @param callable $callback
     * @return MenuBoxItem
     */
    public function SetOnSelect(callable $callback) : MenuBoxItem
    {
        $this->callback = Closure::fromCallable($callback);
        return $this;
    }

    /**
     * Calls item callback
     *
     * @param MenuBox $menu
     */
    public function CallOnSelect(MenuBox $menu) : void
    {
        if ($this->callback == null)
        {
            return;
        }
        $this->callback->call($menu->GetThis(), $menu);
    }

    /**
     * @ignore
     */
    public function GetCallbackForRender2() : Closure
    {
        return function(MenuBox $menu)
        {
            return $this->callback;
        };
    }
}