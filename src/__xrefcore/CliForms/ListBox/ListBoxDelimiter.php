<?php

namespace CliForms\ListBox;

use CliForms\MenuBox\MenuBoxControl;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Using to delimit items of ListBox
 */
class ListBoxDelimiter extends MenuBoxControl
{
    public string $ItemForegroundColor = ForegroundColors::DARK_GRAY;

    /**
     * @ignore
     */
    protected string $delimiterCharacter = "=";

    /**
     * @ignore
     */
    protected int $delimiterLength = 40;

    public function __construct(string $name = "", string $hint = "")
    {
        parent::__construct($name, $hint);
    }

    /**
     * This item is cannot be selected and always returns FALSE
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = false) : bool
    {
        return false;
    }

    /**
     * Returns a character delimiter. If you pass new value, it will be changed
     *
     * @param string|null $newValue
     * @return string
     */
    public function Character(?string $newValue = null) : string
    {
        if ($newValue === null)
        {
            return $this->delimiterCharacter;
        }
        return $this->delimiterCharacter = $newValue;
    }

    /**
     * Returns a length of delimiter. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     */
    public function Length(?int $newValue = null) : int
    {
        if ($newValue === null)
        {
            return $this->delimiterLength;
        }
        return $this->delimiterLength = $newValue;
    }

    /**
     * @return string Rendered delimiter
     */
    public function Render() : string
    {
        return ColoredString::Get(str_repeat($this->delimiterCharacter, $this->Length()), $this->ItemForegroundColor, $this->ItemBackgroundColor);
    }
}