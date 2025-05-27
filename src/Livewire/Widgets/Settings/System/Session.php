<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Session extends Component
{
    use Widgetable;

    public ?string $driver = null;

    public ?string $lifetime = null;

    public ?string $same_site = null;

    public ?string $secure = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 4;
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
        return view('flux::livewire.widgets.settings.system.session');
    }

    #[Renderless]
    public function getData(): void
    {
        $this->fill([
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime'),
            'secure' => config('session.secure'),
            'same_site' => config('session.same_site'),
        ]);
    }
}
