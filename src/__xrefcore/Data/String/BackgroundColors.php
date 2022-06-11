<?php
declare(ticks = 1);

namespace Data\String;

use \Data\Enum;

/**
 * Contains background color codes for CLI
 * @package BackgroundColors
 */

class BackgroundColors extends Enum
{
    const AUTO = "auto";
    const BLACK = "40";
    const RED = "41";
    const GREEN = "42";
    const YELLOW = "43";
    const BLUE = "44";
    const MAGENTA = "45";
    const CYAN = "46";
    const LIGHT_GRAY = "47";
}