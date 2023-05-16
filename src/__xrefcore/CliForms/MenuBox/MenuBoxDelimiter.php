<?php

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxDelimiter;

/**
 * Using to delimit MenuBox items
 *
 * @property bool $Selectable (always FALSE) Element cannot be selected because it's not clickable
 */
class MenuBoxDelimiter extends ListBoxDelimiter
{
    /**
     * @ignore
     */
    protected function _gs_Selectable() : array
    {return [
        Get => function() : bool
        {
            return false;
        },
        Set => function(bool $newValue) : void
        {

        }
    ];}

    /**
     * @ignore
     */
    protected string $delimiterCharacter = "_";

    public function __construct(string $name = "", string $hint = "")
    {
        parent::__construct($name, $hint);
    }
}