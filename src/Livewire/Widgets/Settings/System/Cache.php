<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Cache extends Component
{
    use Widgetable;

    public ?string $driver = null;

    public ?string $prefix = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 5;
    }

    public static function getDefaultOrderRow(): int
    {
        return 0;
    }

    public function mount(): void
    {
        $this->getData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.settings.system.cache');
    }

    #[Renderless]
    public function getData(): void
    {
        $this->fill([
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
        ]);
    }
}
