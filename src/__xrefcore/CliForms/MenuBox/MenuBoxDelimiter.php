<?php

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxDelimiter;

/**
 * Using to delimit MenuBox items
 */
class MenuBoxDelimiter extends ListBoxDelimiter
{
    /**
     * @ignore
     */
    protected string $delimiterCharacter = "_";

    public function __construct(string $name = "", string $hint = "")
    {
        parent::__construct($name, $hint);
    }
}