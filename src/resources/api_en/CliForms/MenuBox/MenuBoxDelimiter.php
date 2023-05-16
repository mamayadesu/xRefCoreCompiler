<?php

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxDelimiter;

/**
 * Using to delimit MenuBox items
 */
class MenuBoxDelimiter extends ListBoxDelimiter
{
    /**
     * @var bool (always FALSE) Element cannot be selected because it's not clickable
     * @property-read
     */
    public bool $Selectable = false;

    public function __construct(string $name = "", string $hint = "")
    {}
}