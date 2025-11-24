<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\DropdownButton;
use FluxErp\Enums\EditorColorPaletteEnum;
use FluxErp\Traits\DropdownButtonTrait;
use Illuminate\View\Component;

class TextColor extends Component implements DropdownButton
{
    use DropdownButtonTrait;

    public array $textColors;

    public function __construct(?array $textColors = null)
    {
        $this->textColors = $textColors ?? resolve_static(EditorColorPaletteEnum::class, 'getColorFamilies');
    }

    public function icon(): ?string
    {
        return 'paint-brush';
    }

    public function tooltip(): ?string
    {
        return 'Text Color';
    }

    public function dropdownContent(): array
    {
        return [
            app(DropdownItem::class, [
                'text' => __('Remove Color'),
                'command' => <<<'JS'
                    editor().chain().focus().unsetColor().run()
                    JS,
            ]),
            app(ColorPicker::class, [
                'colors' => $this->textColors,
                'commandTemplate' => <<<'JS'
                    editor().chain().focus().setColor('{color}').run()
                    JS,
            ]),
        ];
    }
}
