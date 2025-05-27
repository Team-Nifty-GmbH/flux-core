<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Extensions extends Component
{
    use Widgetable;

    public array $loadedExtensions = [];

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultHeight(): int
    {
        return 3;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 2;
    }

    public static function getDefaultOrderRow(): int
    {
        return 1;
    }

    public static function getDefaultWidth(): int
    {
        return 3;
    }

    public function mount(): void
    {
        $this->getData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.settings.system.extensions');
    }

    #[Renderless]
    public function getData(): void
    {
        $this->loadedExtensions = collect(get_loaded_extensions())
            ->sort()
            ->toArray();
    }
}
