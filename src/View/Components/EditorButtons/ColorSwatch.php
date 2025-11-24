<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

class ColorSwatch extends Component implements EditorButton
{
    public function __construct(
        private string $color,
        private string $command,
    ) {}

    public static function identifier(): string
    {
        return 'color-swatch';
    }

    public static function scopes(): array
    {
        return [];
    }

    public function render(): string
    {
        return Blade::render(
            '<div x-on:click="{{ $command }}" class="min-h-6 min-w-6 cursor-pointer" style="background-color: {{ $color }}"></div>',
            ['command' => $this->command, 'color' => $this->color]
        );
    }

    public function command(): ?string
    {
        return $this->command;
    }

    public function isActive(): ?string
    {
        return null;
    }

    public function icon(): ?string
    {
        return null;
    }

    public function text(): ?string
    {
        return null;
    }

    public function title(): ?string
    {
        return null;
    }

    public function tooltip(): ?string
    {
        return null;
    }
}
