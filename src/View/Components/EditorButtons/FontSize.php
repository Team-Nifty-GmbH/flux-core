<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\DropdownButton;
use FluxErp\Traits\DropdownButtonTrait;
use Illuminate\View\Component;

class FontSize extends Component implements DropdownButton
{
    use DropdownButtonTrait;

    public function __construct(
        public array $availableFontSizes = [
            12,
            14,
            16,
            18,
            20,
            24,
            28,
            32,
            36,
        ],
    ) {}

    public function text(): ?string
    {
        return '<i class="ph ph-text-aa text-lg"></i>';
    }

    public function tooltip(): ?string
    {
        return 'Font Size';
    }

    public function dropdownContent(): array
    {
        return array_map(
            fn ($size) => app(DropdownItem::class, [
                'text' => $size . 'px',
                'command' => <<<JS
                    editor().chain().focus().setFontSize({$size}).run()
                    JS,
                'isActive' => <<<JS
                    editor().isActive({ fontSize: '{$size}px' })
                    JS,
            ]),
            $this->availableFontSizes
        );
    }
}
