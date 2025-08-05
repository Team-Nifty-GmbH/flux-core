<?php

namespace FluxErp\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use TallStackUi\View\Components\Layout\Layout as TallStackUiLayout;

class Layout extends TallStackUiLayout
{
    public function blade(): View
    {
        return view('flux::components.layouts.layout');
    }

    public function personalization(): array
    {
        return Arr::dot([
            'wrapper' => [
                'first' => 'h-full flex flex-col',
                'second' => 'flex flex-col w-full grow' . (auth()->check() && auth()->id() ? ' md:pl-20' : ''),
            ],
            'main' => 'h-full mx-auto w-full max-w-full p-4 md:p-10',
        ]);
    }
}
