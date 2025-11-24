<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

class ColorPicker extends Component implements EditorButton
{
    public function __construct(
        private readonly array $colors,
        private readonly string $commandTemplate,
    ) {}

    public static function identifier(): string
    {
        return 'color-picker';
    }

    public static function scopes(): array
    {
        return [];
    }

    public function render(): string
    {
        $html = '<div class="flex space-x-1">';

        foreach ($this->colors as $colorFamily) {
            $html .= '<div class="flex flex-col gap-1">';
            foreach ($colorFamily as $shade) {
                $command = str_replace('{color}', $shade, $this->commandTemplate);
                $html .= Blade::render(
                    '<div x-on:click="{{ $command }}" class="min-h-6 min-w-6 cursor-pointer" style="background-color: {{ $shade }}"></div>',
                    ['command' => $command, 'shade' => $shade]
                );
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function command(): ?string
    {
        return null;
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
