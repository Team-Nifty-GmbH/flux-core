<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Queue\Console\ClearCommand;
use Illuminate\Queue\Console\RestartCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue as QueueFacade;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Symfony\Component\Console\Output\BufferedOutput;

class Queue extends Component
{
    use Actions, Widgetable;

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
    public function clearQueue(): void
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
            'connection' => config('queue.default'),
            'driver' => config('queue.connections.' . config('queue.default') . '.driver'),
            'queue' => config('queue.connections.' . config('queue.default') . '.queue'),
            'size' => QueueFacade::size(),
        ]);
    }

    #[Renderless]
    public function restartQueue(): void
    {
        $result = Artisan::call(RestartCommand::class, [], $output = new BufferedOutput());

        if ($result === 0) {
            $this->toast()->success(trim($output->fetch()))->send();
            $this->getData();
        } else {
            $this->toast()->error(trim($output->fetch()))->send();
        }
    }
}
