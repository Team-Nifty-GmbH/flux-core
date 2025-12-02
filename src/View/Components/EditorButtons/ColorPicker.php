<?php

namespace FluxErp\View\Components\EditorButtons;

use FluxErp\Contracts\EditorButton;
use FluxErp\Traits\EditorButtonTrait;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

class ColorPicker extends Component implements EditorButton
{
    use EditorButtonTrait;

    public function __construct(
        protected array $colors,
        protected string $commandTemplate,
    ) {}

    public function render(): string
    {
        $html = '<div class="flex space-x-1 justify-center w-full">';

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
}
