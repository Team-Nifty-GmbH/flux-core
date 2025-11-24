<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\DropdownButton;
use FluxErp\Traits\DropdownButtonTrait;
use Illuminate\View\Component;

class LineHeight extends Component implements DropdownButton
{
    use DropdownButtonTrait;

    public function __construct(
        public array $availableLineHeights = [
            1,
            1.2,
            1.5,
            2,
            2.5,
            3,
        ],
    ) {}

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
        $buttons = [
            app(DropdownItem::class, [
                'text' => __('Standard'),
                'command' => <<<'JS'
                    editor().chain().focus().unsetLineHeight().run()
                    JS,
                'isActive' => <<<'JS'
                    !editor().isActive({ lineHeight: 1 }) && !editor().isActive({ lineHeight: 1.2 }) && !editor().isActive({ lineHeight: 1.5 }) && !editor().isActive({ lineHeight: 2 }) && !editor().isActive({ lineHeight: 2.5 }) && !editor().isActive({ lineHeight: 3 })
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
