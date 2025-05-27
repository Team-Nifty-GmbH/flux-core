<?php

namespace FluxErp\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Settings\System;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Console\ViewClearCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Number;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Symfony\Component\Console\Output\BufferedOutput;

class Storage extends Component
{
    use Actions, Widgetable;

    public ?string $disk_free_space = null;

    public ?string $disk_total_space = null;

    public ?string $view_cache_space = null;

    public static function dashboardComponent(): array|string
    {
        return System::class;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 2;
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
        return view('flux::livewire.widgets.settings.system.storage');
    }

    #[Renderless]
    public function clearViewCache(): void
    {
        $result = Artisan::call(ViewClearCommand::class, [], $output = new BufferedOutput());

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
            'disk_free_space' => Number::fileSize(disk_free_space('/'), 2),
            'disk_total_space' => Number::fileSize(disk_total_space('/'), 2),
            'view_cache_space' => Number::fileSize(
                array_reduce(
                    glob(storage_path('framework/views/*')),
                    fn ($carry, $item) => $carry + (is_dir($item) ? 0 : filesize($item)),
                    0
                ),
                2
            ),
        ]);
    }
}
