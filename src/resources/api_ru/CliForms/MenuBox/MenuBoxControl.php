<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\ListBox\ListBoxControl;
use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class MenuBoxControl extends ListBoxControl
{
    /**
     * @var string Название элемента
     */
    public string $Name = "";

    /**
     * @var string Подсказка элемента. Отображается тогда, когда элемент выбран
     */
    public string $Hint = "";

    /**
     * @var bool Элемент отображается в контейнере и доступен для выбора
     */
    public bool $Selectable = true;

    /**
     * @var bool Элемент отключён
     */
    public bool $Disabled = false;

    /**
     * @var bool Элемент отображается в контейнере
     */
    public bool $Visible = true;

    /**
     * @var int Сортировка элемента
     */
    public int $Ordering = 1;

    /**
     * @var ForegroundColors Цвет текста подскази элемента
     */
    public string $HintForegroundColor = ForegroundColors::DARK_RED;

    /**
     * @var BackgroundColors Цвет фона текста подскази элемента
     */
    public string $HintBackgroundColor = BackgroundColors::AUTO;

    public function __construct(string $name, string $hint)
    {}

    /**
     * @return MenuBox|null Объект MenuBox, к которому данный элемент принадлежит
     */
    public function GetMenuBox() : ?MenuBox
    {}
    
    /**
     * Удаляет элемент из MenuBox
     * 
     * @return void
     */
    public function Remove() : void
    {}

    /**
     * Возвращает TRUE, если элемент заблокирован. Если задать новое значение, оно будет изменено
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Disabled(?bool $newValue = null) : bool
    {}

    /**
     * Устанавливает стиль элемента
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBoxControl
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : MenuBoxControl
    {}
}