<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Traits\Editor\EditorDropdownButtonTrait;
use Illuminate\View\Component;

class LineHeight extends Component implements EditorDropdownButton
{
    use EditorDropdownButtonTrait;

    public array $availableLineHeights = [
        1,
        1.2,
        1.5,
        2,
        2.5,
        3,
    ];

    public function icon(): ?string
    {
        return 'bars-3';
    }

    public function tooltip(): ?string
    {
        return 'Line Height';
    }

    public function dropdownContent(): array
    {
        $isActiveChecks = collect($this->availableLineHeights)
            ->map(fn (int|float $height): string => "!editor().isActive({ lineHeight: {$height} })")
            ->join("\n&& ");

        $buttons = [
            app(DropdownItem::class, [
                'text' => __('Default'),
                'command' => <<<'JS'
                    editor().chain().focus().unsetLineHeight().run()
                    JS,
                'isActive' => <<<JS
                    {$isActiveChecks}
                    JS,
            ]),
        ];

        foreach ($this->availableLineHeights as $lineHeightSize) {
            $buttons[] = app(DropdownItem::class, [
                'text' => $lineHeightSize . '×',
                'command' => <<<JS
                    editor().chain().focus().setLineHeight({$lineHeightSize}).run()
                    JS,
                'isActive' => <<<JS
                    editor().isActive({ lineHeight: {$lineHeightSize} })
                    JS,
            ]);
        }

        return $buttons;
    }
}
