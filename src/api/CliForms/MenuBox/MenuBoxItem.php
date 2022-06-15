<?php

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxItem;
use \Closure;

/**
 * MenuBoxItem
 */

class MenuBoxItem extends ListBoxItem
{

    /**
     * MenuBoxItem constructor.
     *
     * @param string $name Displayed item title
     * @param callable|null $callback Runs when item selected
     */
    public function __construct(string $name, ?callable $callback = null)
    {}

    /**
     * Sets callback when item selected
     *
     * @param callable $callback
     * @return MenuBoxItem
     */
    public function SetOnSelect(callable $callback) : MenuBoxItem
    {}

    /**
     * Calls item callback
     *
     * @param MenuBox $menu
     */
    public function CallOnSelect(MenuBox $menu) : void
    {}
}