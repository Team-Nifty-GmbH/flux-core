<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Enums\EditorColorPaletteEnum;
use FluxErp\Traits\Editor\EditorDropdownButtonTrait;
use Illuminate\View\Component;

class TextColor extends Component implements EditorDropdownButton
{
    use EditorDropdownButtonTrait;

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
                'additionalAttributes' => ['class' => 'w-full mb-2'],
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
