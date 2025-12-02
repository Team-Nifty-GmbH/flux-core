<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

class ColorSwitch extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function __construct(
        protected string $color,
        protected string $command,
    ) {}

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
}
