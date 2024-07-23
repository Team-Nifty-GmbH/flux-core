<?php

namespace FluxErp\View\Layouts;

use Illuminate\View\Component;

class Clear extends Component
{
    public static ?string $includeBefore = null;

    public static ?string $includeAfter = null;

    public function render(): string
    {
        return <<<'blade'
            <div>
                @includeWhen($includeBefore(), $includeBefore())
                {{ $slot }}
                @includeWhen($includeAfter(), $includeAfter())
            </div>
        blade;
    }

    public function includeBefore(): ?string
    {
        return static::$includeBefore;
    }

    public function includeAfter(): ?string
    {
        return static::$includeAfter;
    }
}
