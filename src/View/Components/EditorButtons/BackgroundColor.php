<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Enums\EditorColorPaletteEnum;
use FluxErp\Traits\EditorDropdownButtonTrait;
use Illuminate\View\Component;

class BackgroundColor extends Component implements EditorDropdownButton
{
    use EditorDropdownButtonTrait;

    public array $textBackgroundColors;

    public function __construct(?array $textBackgroundColors = null)
    {
        $this->textBackgroundColors = $textBackgroundColors
            ?? resolve_static(EditorColorPaletteEnum::class, 'getColorFamilies');
    }

    public function icon(): ?string
    {
        return 'swatch';
    }

    public function tooltip(): ?string
    {
        return 'Background Color';
    }

    public function dropdownContent(): array
    {
        return [
            app(DropdownItem::class, [
                'additionalAttributes' => ['class' => 'w-full mb-2'],
                'text' => __('Remove Color'),
                'command' => <<<'JS'
                    editor().chain().focus().unsetBackgroundColor().run()
                    JS,
            ]),
            app(ColorPicker::class, [
                'colors' => $this->textBackgroundColors,
                'commandTemplate' => <<<'JS'
                    editor().chain().focus().setBackgroundColor('{color}').run()
                    JS,
            ]),
        ];
    }
}
