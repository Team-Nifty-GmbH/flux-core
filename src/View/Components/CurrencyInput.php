<?php

namespace FluxErp\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\View\ComponentSlot;
use TallStackUi\Foundation\Attributes\SkipDebug;

class CurrencyInput extends Component
{
    public function __construct(
        public string $thousands = ',',
        public string $decimal = '.',
        public int $precision = 2,
        public ?string $label = null,
        public ?string $hint = null,
        public ?string $icon = null,
        public ?bool $clearable = null,
        public ?bool $invalidate = null,
        #[SkipDebug]
        public ?string $position = 'left',
        #[SkipDebug]
        public ComponentSlot|string|null $prefix = null,
        #[SkipDebug]
        public ComponentSlot|string|null $suffix = null,
    ) {}

    public function render(): View
    {
        return view('flux::components.input.currency');
    }
}
