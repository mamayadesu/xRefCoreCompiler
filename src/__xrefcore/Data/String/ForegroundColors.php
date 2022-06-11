<?php
declare(ticks = 1);

namespace Data\String;

use \Data\Enum;

/**
 * Contains foreground color codes for CLI
 * @package ForegroundColors
 */

class ForegroundColors extends Enum
{
    const AUTO = "auto";
    const BLACK = "0;30";
    const DARK_GRAY = "1;30";
    const DARK_BLUE = "0;34";
    const BLUE = "1;34";
    const DARK_GREEN = "0;32";
    const GREEN = "1;32";
    const DARK_CYAN = "0;36";
    const CYAN = "1;36";
    const DARK_RED = "0;31";
    const RED = "1;31";
    const DARK_PURPLE = "0;35";
    const PURPLE = "1;35";
    const BROWN = "0;33";
    const YELLOW = "1;33";
    const GRAY = "0;37";
    const WHITE = "1;37";
}