<?php

namespace CliForms\Exceptions;

use CliForms\MenuBox\MenuBoxControl;

class ItemIsUsingException extends \Exception
{
    public MenuBoxControl $Control;
}