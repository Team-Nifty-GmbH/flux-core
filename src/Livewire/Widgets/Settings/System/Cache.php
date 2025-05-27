<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Widgetable;
use Illuminate\Cache\Console\ClearCommand;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Symfony\Component\Console\Output\BufferedOutput;

class Cache extends Component
{
    use Actions, Widgetable;

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
    public function clearCache(): void
    {
        $result = Artisan::call(ClearCommand::class, [], $output = new BufferedOutput());

        if ($result === 0) {
            $this->toast()->success(trim($output->fetch()))->send();
            $this->getData();
        } else {
            $this->toast()->error(trim($output->fetch()))->send();
        }
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
