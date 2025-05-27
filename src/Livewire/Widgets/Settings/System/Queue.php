<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Queue as QueueFacade;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Queue extends Component
{
    use Widgetable;

    public ?string $connection = null;

    public ?string $driver = null;

    public ?string $queue = null;

    public ?int $size = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 3;
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
        return view('flux::livewire.widgets.settings.system.queue');
    }

    #[Renderless]
    public function getData(): void
    {
        $this->fill([
            'connection' => config('queue.default'),
            'driver' => config('queue.connections.' . config('queue.default') . '.driver'),
            'queue' => config('queue.connections.' . config('queue.default') . '.queue'),
            'size' => QueueFacade::size(),
        ]);
    }
}
