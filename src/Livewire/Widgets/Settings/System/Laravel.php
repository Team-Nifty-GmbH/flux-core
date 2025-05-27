<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Laravel extends Component
{
    use Widgetable;

    public ?string $debug_mode = null;

    public ?string $environment = null;

    public ?string $locale = null;

    public ?string $timezone = null;

    public ?string $version = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 0;
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
        return view('flux::livewire.widgets.settings.system.laravel');
    }

    #[Renderless]
    public function getData(): void
    {
        $this->fill([
            'version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ]);
    }
}
