<?php

namespace FluxErp\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Editor extends Component
{
    public function __construct(
        public bool $bold = true,
        public bool $italic = true,
        public bool $underline = true,
        public bool $strike = true,
        public bool $code = true,
        public bool $h1 = true,
        public bool $h2 = true,
        public bool $h3 = true,
        public bool $horizontalRule = true,
        public bool $bulletList = true,
        public bool $orderedList = true,
        public bool $quote = true,
        public bool $codeBlock = true,

        public bool $tooltipDropdown = false,
        public bool $transparent = false,
        public ?int $defaultFontSize = null,
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
        ]
    ) {}

    public function render(): View|Closure|string
    {
        return view('flux::components.editor');
    }
}
