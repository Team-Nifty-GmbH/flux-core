<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\DropdownButton;
use FluxErp\Traits\DropdownButtonTrait;
use Illuminate\View\Component;

class Headings extends Component implements DropdownButton
{
    use DropdownButtonTrait;

    public function __construct(
        public bool $h1 = true,
        public bool $h2 = true,
        public bool $h3 = true,
    ) {}

    public function icon(): ?string
    {
        return null;
    }

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

        if ($this->h1) {
            $buttons[] = app(DropdownItem::class, [
                'text' => 'H1',
                'command' => <<<'JS'
                    editor().chain().focus().toggleHeading({ level: 1 }).run()
                    JS,
                'isActive' => <<<'JS'
                    editor().isActive('heading', { level: 1 })
                    JS,
            ]);
        }

        if ($this->h2) {
            $buttons[] = app(DropdownItem::class, [
                'text' => 'H2',
                'command' => <<<'JS'
                    editor().chain().focus().toggleHeading({ level: 2 }).run()
                    JS,
                'isActive' => <<<'JS'
                    editor().isActive('heading', { level: 2 })
                    JS,
            ]);
        }

        if ($this->h3) {
            $buttons[] = app(DropdownItem::class, [
                'text' => 'H3',
                'command' => <<<'JS'
                    editor().chain().focus().toggleHeading({ level: 3 }).run()
                    JS,
                'isActive' => <<<'JS'
                    editor().isActive('heading', { level: 3 })
                    JS,
            ]);
        }

        return $buttons;
    }
}
