<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\ListBox\ListBoxControl;
use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class MenuBoxControl extends ListBoxControl
{

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
     * Возвращает TRUE, если элемент видимый и доступен для выбора. Если задать новое значение, оно будет изменено
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null) : bool
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

    /**
     * Возвращает подсказку элемента. Подсказки появляются справа от элемента при наведении на них. Если задать новое значение, оно будет изменено
     *
     * @param string|null $newValue
     * @return string
     */
    public function Hint(?string $newValue = null) : string
    {}

    /**
     * Возвращает название элемента. Если задать новое значение, оно будет изменено
     *
     * @param ?string $newValue
     * @return string
     */
    public function Name(?string $newValue = null) : string
    {}

    /**
     * Возвращает TRUE, если элемент видимый. Если задать новое значение, оно будет изменено
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Visible(?bool $newValue = null) : bool
    {}

    /**
     * Возвращает порядок сортировки элемента внутри контейнера. Если задать новое значение, оно будет изменено
     *
     * @param int|null $newValue
     * @return int
     */
    public function Ordering(?int $newValue = null) : int
    {}
}