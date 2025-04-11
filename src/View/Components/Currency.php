<?php

namespace FluxErp\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\View\ComponentSlot;
use TallStackUi\Foundation\Attributes\SkipDebug;

class Currency extends Component
{
    public int $modifier = 1;

    public function __construct(
        public string $thousands = ',',
        public string $decimal = '.',
        public int $precision = 2,
        public bool $round = true,
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
    ) {
        if ($this->round) {
            $this->modifier = (int) str_pad('1', $this->precision + 1, '0');
        }
    }

    public function render(): View
    {
        return view('flux::components.currency');
    }
}
