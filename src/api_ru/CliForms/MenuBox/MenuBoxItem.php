<?php

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxItem;
use CliForms\MenuBox\Events\ItemClickedEvent;
use \Closure;
use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Кликабельный элемент MenuBox (кнопка)
 */

class MenuBoxItem extends ListBoxItem
{
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
     * Конструктор MenuBoxItem.
     *
     * @param string $name Название элемента, которое будет видно на экране
     * @param callable|null $callback Callback-функция, выполняемая при нажатии на элемент
     */
    public function __construct(string $name, string $hint, ?callable $callback = null)
    {}

    /**
     * Устанавливает новый callback
     *
     * @param callable $callback
     * @return MenuBoxItem
     */
    public function SetOnSelect(?callable $callback) : MenuBoxItem
    {}
}