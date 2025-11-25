<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorDropdownButton;
use FluxErp\Traits\EditorDropdownButtonTrait;
use Illuminate\View\Component;

class Headings extends Component implements EditorDropdownButton
{
    use EditorDropdownButtonTrait;

    public array $headings = [
        'h1' => true,
        'h2' => true,
        'h3' => true,
    ];

    public function text(): ?string
    {
        return '<i class="ph ph-text-h text-lg"></i>';
    }

    public function tooltip(): ?string
    {
        return 'Headings';
    }

    public function dropdownContent(): array
    {
        $buttons = [];

        foreach ($this->headings as $heading => $enabled) {
            if (! $enabled) {
                continue;
            }

            $level = (int) substr($heading, 1);

            $buttons[] = app(DropdownItem::class, [
                'text' => strtoupper($heading),
                'command' => <<<JS
                    editor().chain().focus().toggleHeading({ level: {$level} }).run()
                    JS,
                'isActive' => <<<JS
                    editor().isActive('heading', { level: {$level} })
                    JS,
            ]);
        }

        return $buttons;
    }

    public function expandableContent(): bool
    {
        return true;
    }
}
